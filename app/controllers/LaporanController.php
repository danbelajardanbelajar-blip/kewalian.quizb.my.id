<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/LaporanModel.php';
require_once APP_PATH . '/models/KonfigurasiModel.php';

/**
 * LaporanController.php
 * CRUD laporan presensi harian
 */
class LaporanController extends Controller
{
    private LaporanModel     $laporanModel;
    private KonfigurasiModel $konfig;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
        $this->konfig       = new KonfigurasiModel();
    }

    /**
     * GET /laporan — Daftar semua laporan
     */
    public function index(): void
    {
        $this->requireAuth();

        $semua = $this->laporanModel->getAll();

        $this->view('laporan/index', [
            'title'   => 'Riwayat Laporan Presensi',
            'laporan' => $semua,
        ]);
    }

    /**
     * POST /laporan/simpan — Simpan laporan baru / update
     */
    public function simpan(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('dashboard');
        }

        $tanggal = trim($_POST['tanggal'] ?? '');

        // Validasi tanggal
        if (empty($tanggal) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            Flash::set('error', 'Tanggal tidak valid.');
            $this->redirect('dashboard');
        }

        $kategori = $this->konfig->getKategori();
        $kelas    = $this->konfig->getKelas();
        $rawData  = $_POST['data'] ?? [];
        $siswa    = [];

        foreach ($rawData as $idStr => $row) {
            $namaRaw = $row['nama'] ?? '';
            $id      = (int) $idStr;
            if (empty($namaRaw) || $id <= 0) continue;

            $entry = [
                'id'   => $id,
                'nama' => strip_tags(trim($namaRaw))
            ];

            // Parse detailed answers same as Absen format
            foreach (['sekolah', 'almiftah', 'diniyah', 'subuh'] as $k) {
                $status = $row[$k]['status'] ?? 'absen';
                $ket    = ($status === 'izin') ? strip_tags($row[$k]['ket'] ?? '') : '';
                $entry[$k] = ['status' => $status, 'ket' => $ket];
            }

            $quranType = $row['quran']['type'] ?? 'tidak';
            $entry['quran'] = [
                'type'   => $quranType,
                'jumlah' => in_array($quranType, ['halaman', 'juz']) ? max(1, (int)($row['quran']['jumlah'] ?? 1)) : 0
            ];

            $entry['dluha']   = ['status' => $row['dluha']['status'] ?? 'tidak_ikut'];
            $entry['belajar'] = ['status' => $row['belajar']['status'] ?? 'tidak'];

            $bukuSudah = $row['baca_buku']['status'] ?? 'belum';
            $entry['baca_buku'] = [
                'status' => $bukuSudah,
                'jumlah' => $bukuSudah === 'iya' ? max(1, (int)($row['baca_buku']['jumlah'] ?? 1)) : 0
            ];

            $siswa[$id] = $entry; // Use ID as key
        }

        if (empty($siswa)) {
            Flash::set('error', 'Data siswa tidak boleh kosong.');
            $this->redirect('dashboard?tanggal=' . $tanggal);
        }

        $isUpdate = $this->laporanModel->exists($tanggal);
        $existing = $isUpdate ? $this->laporanModel->getByTanggal($tanggal) : [];

        $saved = $this->laporanModel->save($tanggal, [
            'kelas'      => $kelas,
            'kategori'   => $kategori,
            'siswa'      => $siswa,
            'created_at' => $existing['created_at'] ?? date('Y-m-d H:i:s'),
        ]);

        if ($saved) {
            $msg = $isUpdate
                ? "Laporan tanggal {$tanggal} berhasil diperbarui."
                : "Laporan tanggal {$tanggal} berhasil disimpan.";
            Flash::set('success', $msg);
            $this->redirect('laporan/lihat/' . $tanggal);
        } else {
            Flash::set('error', 'Gagal menyimpan laporan. Pastikan folder storage/laporan dapat ditulis.');
            $this->redirect('dashboard?tanggal=' . $tanggal);
        }
    }

    /**
     * GET /laporan/lihat/{tanggal} — Detail laporan
     */
    public function lihat(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan untuk tanggal ' . $tanggal . ' tidak ditemukan.');
            $this->redirect('laporan');
        }

        $this->view('laporan/show', [
            'title'    => 'Detail Laporan — ' . date('d F Y', strtotime($tanggal)),
            'laporan'  => $data,
            'tanggal'  => $tanggal,
        ]);
    }

    /**
     * GET /laporan/edit/{tanggal} — Form edit laporan
     */
    public function edit(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan tidak ditemukan.');
            $this->redirect('laporan');
        }

        // Dashboard sekarang sudah menangani mode edit
        $this->redirect('dashboard?tanggal=' . $tanggal);
    }

    /**
     * POST /laporan/hapus/{tanggal} — Hapus laporan
     */
    public function hapus(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        if ($this->laporanModel->delete($tanggal)) {
            Flash::set('success', 'Laporan tanggal ' . $tanggal . ' berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus laporan.');
        }

        $this->redirect('laporan');
    }

    /**
     * GET /laporan/rekap — Rekap kehadiran per siswa
     */
    public function rekap(): void
    {
        $this->requireAuth();

        $rekap    = $this->laporanModel->getRekapPerSiswa();
        $kategori = [
            'sekolah'  => 'Sekolah',
            'almiftah' => 'Al-Miftah',
            'diniyah'  => 'Diniyah',
            'subuh'    => 'Ngaji Pagi',
            'quran'    => 'Al-Qur\'an',
            'dluha'    => 'Dluha',
            'belajar'  => 'Belajar',
            'baca_buku'=> 'Baca Buku',
            'memaafkan'=> 'Memaafkan',
            'mendoakan_muslimin'=> 'Doa Muslim',
            'mendoakan_ortu' => 'Doa Ortu',
            'shadaqah' => 'Sedekah'
        ];
        $kelas    = $this->konfig->getKelas();

        $this->view('laporan/rekap', [
            'title'    => 'Rekap Kehadiran — Kelas ' . $kelas,
            'rekap'    => $rekap,
            'kategori' => $kategori,
            'kelas'    => $kelas,
        ]);
    }

    /**
     * GET /laporan/export/{tanggal} — Export laporan CSV
     */
    public function export(string $tanggal = ''): void
    {
        $this->requireAuth();

        if (empty($tanggal)) {
            $this->redirect('laporan');
        }

        $data = $this->laporanModel->getByTanggal($tanggal);

        if (empty($data)) {
            Flash::set('error', 'Laporan tidak ditemukan untuk diexport.');
            $this->redirect('laporan');
        }

        $filename = 'laporan_' . $tanggal . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM untuk Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $kategori = [
            'sekolah'  => 'Sekolah',
            'almiftah' => 'Al-Miftah',
            'diniyah'  => 'Diniyah',
            'subuh'    => 'Ngaji Pagi'
        ];

        // Header row
        $headers = ['No', 'Nama Siswa'];
        foreach ($kategori as $label) {
            $headers[] = $label;
        }
        $headers[] = 'Al-Qur\'an';
        $headers[] = 'Shalat Dluha';
        $headers[] = 'Belajar Kamar';
        $headers[] = 'Baca Buku';
        $headers[] = 'Memaafkan';
        $headers[] = 'Doa Muslim';
        $headers[] = 'Doa Ortu';
        $headers[] = 'Sedekah';
        
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($data['siswa'] ?? [] as $siswa) {
            $row = [$no++, $siswa['nama']];
            
            // Kehadiran Dasar
            foreach ($kategori as $key => $label) {
                if (isset($siswa[$key])) {
                    if (is_array($siswa[$key])) {
                        $st = ucfirst($siswa[$key]['status'] ?? 'absen');
                        if ($st === 'Izin' && !empty($siswa[$key]['ket'])) {
                            $st .= ' (' . $siswa[$key]['ket'] . ')';
                        }
                        $row[] = $st;
                    } else {
                        $row[] = !empty($siswa[$key]) ? 'Hadir' : 'Tidak';
                    }
                } else {
                    $row[] = '-';
                }
            }

            // Al Quran
            $q = $siswa['quran'] ?? [];
            if (!empty($q)) {
                if (($q['type'] ?? '') === 'setengah_juz') $row[] = 'Setengah Juz';
                elseif (($q['type'] ?? '') === 'juz') $row[] = $q['jumlah'] . ' Juz';
                elseif (($q['type'] ?? '') === 'halaman') $row[] = $q['jumlah'] . ' Halaman';
                else $row[] = 'Belum';
            } else {
                $row[] = '-';
            }

            // Dluha & Belajar
            $dl = $siswa['dluha']['status'] ?? '';
            $row[] = $dl === 'ikut' ? 'Ikut' : ($dl === 'udzur_haid' ? 'Udzur' : 'Tidak');
            
            $bl = $siswa['belajar']['status'] ?? '';
            $row[] = $bl === 'iya' ? 'Iya' : 'Tidak';

            $bb = $siswa['baca_buku'] ?? [];
            if (!empty($bb) && ($bb['status'] ?? '') === 'iya') {
                $row[] = $bb['jumlah'] . ' Halaman';
            } else {
                $row[] = 'Belum';
            }

            // 4 Pertanyaan Tambahan
            $row[] = ($siswa['memaafkan']['status'] ?? '') === 'iya' ? 'Iya' : 'Belum';
            $row[] = ($siswa['mendoakan_muslimin']['status'] ?? '') === 'iya' ? 'Iya' : 'Belum';
            $row[] = ($siswa['mendoakan_ortu']['status'] ?? '') === 'iya' ? 'Iya' : 'Belum';
            $row[] = ($siswa['shadaqah']['status'] ?? '') === 'iya' ? 'Iya' : 'Belum';

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
