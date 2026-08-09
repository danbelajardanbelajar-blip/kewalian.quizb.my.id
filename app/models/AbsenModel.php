<?php
require_once APP_PATH . '/core/Model.php';

/**
 * AbsenModel.php
 * Mengelola data absen mandiri siswa (self-report)
 * Storage: storage/absen/YYYY-MM-DD.json
 */
class AbsenModel extends Model
{
    private string $folder = 'absen';

    /**
     * Ambil data absen berdasarkan tanggal
     */
    public function getByTanggal(string $tanggal): array
    {
        return $this->db->read($this->folder . '/' . $tanggal . '.json');
    }

    /**
     * Ambil data absen satu siswa berdasarkan tanggal
     */
    public function getSiswaByTanggal(string $tanggal, string $nama): array
    {
        $data = $this->getByTanggal($tanggal);
        return $data['siswa'][$nama] ?? [];
    }

    /**
     * Cek apakah siswa sudah mengisi absen hari ini
     */
    public function sudahIsi(string $tanggal, string $nama): bool
    {
        $data = $this->getByTanggal($tanggal);
        return !empty($data['siswa'][$nama]);
    }

    /**
     * Simpan/update data absen satu siswa
     */
    public function simpanSiswa(string $tanggal, string $nama, array $dataSiswa): bool
    {
        $existing = $this->getByTanggal($tanggal);

        if (empty($existing)) {
            $existing = [
                'tanggal'    => $tanggal,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'siswa'      => [],
            ];
        }

        $dataSiswa['nama']      = $nama;
        $dataSiswa['waktu_isi'] = date('Y-m-d H:i:s');

        $existing['siswa'][$nama] = $dataSiswa;
        $existing['updated_at']   = date('Y-m-d H:i:s');

        return $this->db->write($this->folder . '/' . $tanggal . '.json', $existing);
    }

    /**
     * Ambil semua rekap absen (semua tanggal)
     */
    public function getAllDates(): array
    {
        $files = $this->db->listFiles($this->folder);
        $hasil = [];

        foreach ($files as $file) {
            $tanggal = pathinfo($file, PATHINFO_FILENAME);
            $data    = $this->db->read($this->folder . '/' . $file);
            if (!empty($data)) {
                $hasil[] = [
                    'tanggal'      => $tanggal,
                    'jumlah_isi'   => count($data['siswa'] ?? []),
                    'updated_at'   => $data['updated_at'] ?? '',
                ];
            }
        }

        usort($hasil, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        return $hasil;
    }

    /**
     * Hitung statistik absen untuk satu tanggal
     */
    public function getStatistik(string $tanggal, array $daftarSiswa): array
    {
        $data = $this->getByTanggal($tanggal);
        $siswaData = $data['siswa'] ?? [];

        $stats = [
            'total'        => count($daftarSiswa),
            'sudah_isi'    => count($siswaData),
            'belum_isi'    => [],
            'per_kategori' => [],
        ];

        // Siswa belum isi
        foreach ($daftarSiswa as $nama) {
            if (!isset($siswaData[$nama])) {
                $stats['belum_isi'][] = $nama;
            }
        }

        // Statistik per kategori kehadiran
        $kategoriAbsen = ['sekolah', 'almiftah', 'diniyah', 'subuh'];
        foreach ($kategoriAbsen as $kat) {
            $stats['per_kategori'][$kat] = ['hadir' => 0, 'absen' => 0, 'sakit' => 0, 'izin' => 0];
            foreach ($siswaData as $s) {
                $status = $s[$kat]['status'] ?? 'absen';
                if (isset($stats['per_kategori'][$kat][$status])) {
                    $stats['per_kategori'][$kat][$status]++;
                }
            }
        }

        return $stats;
    }
}
