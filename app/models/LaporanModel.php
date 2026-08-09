<?php
require_once APP_PATH . '/core/Model.php';

/**
 * LaporanModel.php
 * Mengelola data laporan harian presensi
 * Setiap laporan disimpan sebagai: storage/laporan/YYYY-MM-DD.json
 */
class LaporanModel extends Model
{
    private string $folder = 'laporan';

    /**
     * Ambil laporan berdasarkan tanggal (YYYY-MM-DD)
     */
    public function getByTanggal(string $tanggal): array
    {
        return $this->db->read($this->folder . '/' . $tanggal . '.json');
    }

    /**
     * Simpan laporan baru atau update laporan yang sudah ada
     * 
     * @param string $tanggal Format YYYY-MM-DD
     * @param array  $data    Data presensi siswa
     */
    public function save(string $tanggal, array $data): bool
    {
        $laporan = [
            'tanggal'       => $tanggal,
            'kelas'         => $data['kelas'] ?? '',
            'kategori'      => $data['kategori'] ?? [],
            'siswa'         => $data['siswa'] ?? [],
            'created_at'    => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
            'created_by'    => Session::get('username', 'admin'),
        ];

        return $this->db->write($this->folder . '/' . $tanggal . '.json', $laporan);
    }

    /**
     * Hapus laporan berdasarkan tanggal
     */
    public function delete(string $tanggal): bool
    {
        return $this->db->delete($this->folder . '/' . $tanggal . '.json');
    }

    /**
     * Cek apakah laporan untuk tanggal tertentu sudah ada
     */
    public function exists(string $tanggal): bool
    {
        return $this->db->exists($this->folder . '/' . $tanggal . '.json');
    }

    /**
     * Ambil semua laporan yang tersedia (urut terbaru)
     */
    public function getAll(): array
    {
        $files   = $this->db->listFiles($this->folder);
        $laporan = [];

        foreach ($files as $file) {
            $tanggal = pathinfo($file, PATHINFO_FILENAME);
            $data    = $this->db->read($this->folder . '/' . $file);
            if (!empty($data)) {
                $laporan[] = [
                    'tanggal'    => $tanggal,
                    'kelas'      => $data['kelas'] ?? '',
                    'jumlah_siswa' => count($data['siswa'] ?? []),
                    'updated_at' => $data['updated_at'] ?? '',
                    'created_by' => $data['created_by'] ?? '',
                ];
            }
        }

        // Urutkan dari terbaru
        usort($laporan, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        return $laporan;
    }

    /**
     * Hitung rekap kehadiran per siswa dari semua laporan
     * 
     * @return array [nama_siswa => [kategori => jumlah_hadir]]
     */
    public function getRekapPerSiswa(): array
    {
        $files = $this->db->listFiles($this->folder);
        $rekap = [];

        foreach ($files as $file) {
            $data = $this->db->read($this->folder . '/' . $file);
            foreach ($data['siswa'] ?? [] as $siswa) {
                $id = $siswa['id'] ?? null;
                $nama = $siswa['nama'] ?? '';
                
                // Fallback for older data that doesn't have ID yet
                if (empty($id)) {
                    continue; // Skip if no ID, or we can use nama but that defeats the purpose. Let's assume migration script handles it.
                }

                if (!isset($rekap[$id])) {
                    $rekap[$id] = ['id' => $id, 'nama' => $nama, 'total_hadir' => 0, 'total_hari' => 0, 'kategori' => []];
                } else if ($nama) {
                    $rekap[$id]['nama'] = $nama;
                }

                $rekap[$id]['total_hari']++;
                foreach ($data['kategori'] ?? [] as $key => $label) {
                    if (!isset($rekap[$id]['kategori'][$key])) {
                        $rekap[$id]['kategori'][$key] = ['label' => $label, 'hadir' => 0];
                    }
                    if (!empty($siswa[$key])) {
                        $rekap[$id]['kategori'][$key]['hadir']++;
                        $rekap[$id]['total_hadir']++;
                    }
                }
            }
        }

        // Ksort by ID, or we can let the controller sort by Name later if needed
        ksort($rekap);
        return $rekap;
    }

    /**
     * Ambil laporan dalam rentang tanggal tertentu
     */
    public function getByRange(string $dari, string $sampai): array
    {
        $files   = $this->db->listFiles($this->folder);
        $laporan = [];

        foreach ($files as $file) {
            $tanggal = pathinfo($file, PATHINFO_FILENAME);
            if ($tanggal >= $dari && $tanggal <= $sampai) {
                $data = $this->db->read($this->folder . '/' . $file);
                if (!empty($data)) {
                    $laporan[$tanggal] = $data;
                }
            }
        }

        ksort($laporan);
        return $laporan;
    }
}
