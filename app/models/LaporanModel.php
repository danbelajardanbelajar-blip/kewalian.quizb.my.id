<?php
require_once APP_PATH . '/core/Model.php';

/**
 * LaporanModel.php
 * Mengelola data laporan harian presensi dari MySQL
 */
class LaporanModel extends Model
{
    /**
     * Ambil laporan berdasarkan tanggal (YYYY-MM-DD)
     */
    public function getByTanggal(string $tanggal): array
    {
        // Panggil AbsenModel untuk mengurangi duplikasi
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        return $absenModel->getByTanggal($tanggal);
    }

    /**
     * Simpan laporan baru atau update laporan yang sudah ada (dari dashboard admin)
     */
    public function save(string $tanggal, array $data): bool
    {
        // $data['siswa'] berisi array id_siswa => data_absen
        if (!isset($data['siswa']) || empty($data['siswa'])) {
            return false;
        }

        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();

        $success = true;
        foreach ($data['siswa'] as $id => $s) {
            $nama = $s['nama'] ?? '';
            // Pastikan data dalam format yang diharapkan oleh AbsenModel
            $dataSiswa = [
                'sekolah' => ['status' => $s['sekolah'], 'ket' => $s['sekolah_ket'] ?? ''],
                'almiftah' => ['status' => $s['almiftah'], 'ket' => $s['almiftah_ket'] ?? ''],
                'diniyah' => ['status' => $s['diniyah'], 'ket' => $s['diniyah_ket'] ?? ''],
                'subuh' => ['status' => $s['subuh'], 'ket' => $s['subuh_ket'] ?? ''],
                'quran' => ['type' => $s['quran_type'] ?? 'halaman', 'jumlah' => $s['quran_jumlah'] ?? 0],
                'baca_buku' => ['status' => $s['baca_buku_status'] ?? 'belum', 'jumlah' => $s['baca_buku_jumlah'] ?? 0],
                'dluha' => ['status' => $s['dluha'] ?? 'tidak_ikut'],
                'belajar' => ['status' => $s['belajar'] ?? 'tidak'],
                'memaafkan' => ['status' => $s['memaafkan'] ?? 'tidak'],
                'mendoakan_muslimin' => ['status' => $s['mendoakan_muslimin'] ?? 'tidak'],
                'mendoakan_ortu' => ['status' => $s['mendoakan_ortu'] ?? 'tidak'],
                'shadaqah' => ['status' => $s['shadaqah'] ?? 'tidak']
            ];

            if (!$absenModel->simpanSiswa($tanggal, $id, $nama, $dataSiswa)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Hapus laporan berdasarkan tanggal
     */
    public function delete(string $tanggal): bool
    {
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        return $absenModel->deleteTanggal($tanggal);
    }

    /**
     * Cek apakah laporan untuk tanggal tertentu sudah ada
     */
    public function exists(string $tanggal): bool
    {
        $this->db->query("SELECT id FROM absen WHERE tanggal = :tanggal LIMIT 1");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Ambil semua laporan yang tersedia (urut terbaru)
     */
    public function getAll(): array
    {
        // Gunakan fungsi dari AbsenModel dan tambahkan data kelas/created_by
        $this->db->query("
            SELECT tanggal, COUNT(id_siswa) as jumlah_siswa, MAX(waktu_isi) as updated_at 
            FROM absen 
            GROUP BY tanggal 
            ORDER BY tanggal DESC
        ");
        
        $laporan = $this->db->resultSet();
        
        require_once APP_PATH . '/models/KonfigurasiModel.php';
        $konfig = new KonfigurasiModel();
        $kelas = $konfig->getKelas();

        foreach ($laporan as &$lap) {
            $lap['kelas'] = $kelas;
            $lap['created_by'] = 'admin'; // Static for now, as we only have one admin
        }
        return $laporan;
    }

    /**
     * Hitung rekap kehadiran per siswa dari semua laporan
     */
    public function getRekapPerSiswa(): array
    {
        $this->db->query("
            SELECT a.*, s.nama 
            FROM absen a 
            JOIN siswa s ON a.id_siswa = s.id 
            ORDER BY a.id_siswa ASC
        ");
        $allAbsen = $this->db->resultSet();

        $rekap = [];
        $kategoriList = [
            'sekolah', 'almiftah', 'diniyah', 'subuh',
            'quran', 'dluha', 'belajar', 'baca_buku',
            'memaafkan', 'mendoakan_muslimin', 'mendoakan_ortu', 'shadaqah'
        ];
        $kategoriLabel = [
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
            'shadaqah' => 'Membantu'
        ];

        foreach ($allAbsen as $absen) {
            $id = $absen['id_siswa'];
            if (!isset($rekap[$id])) {
                $rekap[$id] = [
                    'id' => $id, 
                    'nama' => $absen['nama'], 
                    'total_hadir' => 0, 
                    'total_hari' => 0, 
                    'kategori' => []
                ];
                foreach ($kategoriList as $k) {
                    $rekap[$id]['kategori'][$k] = ['label' => $kategoriLabel[$k], 'hadir' => 0];
                }
            }

            $rekap[$id]['total_hari']++;

            foreach ($kategoriList as $k) {
                $isHadir = false;
                if (in_array($k, ['sekolah', 'almiftah', 'diniyah', 'subuh'])) {
                    $statusField = $k . '_status';
                    $isHadir = ($absen[$statusField] === 'hadir');
                } elseif ($k === 'quran') {
                    $isHadir = ($absen['quran_type'] !== 'tidak' && !empty($absen['quran_type']));
                } elseif ($k === 'baca_buku') {
                    $isHadir = ($absen['baca_buku_status'] === 'iya');
                } elseif ($k === 'dluha') {
                    $isHadir = ($absen['dluha_status'] === 'ikut' || $absen['dluha_status'] === 'udzur_haid');
                } else {
                    $statusField = $k . '_status';
                    $isHadir = ($absen[$statusField] === 'iya');
                }

                if ($isHadir) {
                    $rekap[$id]['kategori'][$k]['hadir']++;
                    $rekap[$id]['total_hadir']++;
                }
            }
        }

        return $rekap;
    }

    /**
     * Ambil laporan dalam rentang tanggal tertentu
     */
    public function getByRange(string $dari, string $sampai): array
    {
        $this->db->query("SELECT DISTINCT tanggal FROM absen WHERE tanggal >= :dari AND tanggal <= :sampai ORDER BY tanggal ASC");
        $this->db->bind(':dari', $dari);
        $this->db->bind(':sampai', $sampai);
        $tanggals = $this->db->resultSet();

        $laporan = [];
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        
        foreach ($tanggals as $row) {
            $tgl = $row['tanggal'];
            $data = $absenModel->getByTanggal($tgl);
            if (!empty($data)) {
                $laporan[$tgl] = $data;
            }
        }

        return $laporan;
    }
}
