<?php
require_once APP_PATH . '/core/Model.php';

/**
 * AbsenModel.php
 * Mengelola data absen mandiri siswa (MySQL)
 */
class AbsenModel extends Model
{
    /**
     * Ambil data absen berdasarkan tanggal
     * Mengembalikan struktur array yang kompatibel dengan format lama:
     * ['tanggal' => ..., 'siswa' => [ id => [ data absen... ] ]]
     */
    public function getByTanggal(string $tanggal): array
    {
        $this->db->query("
            SELECT a.*, s.nama 
            FROM absen a 
            JOIN siswa s ON a.id_siswa = s.id 
            WHERE a.tanggal = :tanggal
        ");
        $this->db->bind(':tanggal', $tanggal);
        $results = $this->db->resultSet();

        $data = [
            'tanggal' => $tanggal,
            'siswa'   => []
        ];

        foreach ($results as $row) {
            $id = $row['id_siswa'];
            $data['siswa'][$id] = [
                'id' => $id,
                'nama' => $row['nama'],
                'waktu_isi' => $row['waktu_isi'],
                'sekolah' => ['status' => $row['sekolah_status'], 'ket' => $row['sekolah_ket']],
                'almiftah' => ['status' => $row['almiftah_status'], 'ket' => $row['almiftah_ket']],
                'diniyah' => ['status' => $row['diniyah_status'], 'ket' => $row['diniyah_ket']],
                'subuh' => ['status' => $row['subuh_status'], 'ket' => $row['subuh_ket']],
                'quran' => ['type' => $row['quran_type'], 'jumlah' => $row['quran_jumlah']],
                'baca_buku' => ['status' => $row['baca_buku_status'], 'jumlah' => $row['baca_buku_jumlah']],
                'dluha' => ['status' => $row['dluha_status']],
                'belajar' => ['status' => $row['belajar_status']],
                'memaafkan' => ['status' => $row['memaafkan_status']],
                'mendoakan_muslimin' => ['status' => $row['mendoakan_muslimin_status']],
                'mendoakan_ortu' => ['status' => $row['mendoakan_ortu_status']],
                'shadaqah' => ['status' => $row['shadaqah_status']]
            ];
        }

        return $data;
    }

    /**
     * Ambil data absen satu siswa berdasarkan tanggal
     */
    public function getSiswaByTanggal(string $tanggal, int $id): array
    {
        $data = $this->getByTanggal($tanggal);
        return $data['siswa'][$id] ?? [];
    }

    /**
     * Cek apakah siswa sudah mengisi absen hari ini
     */
    public function sudahIsi(string $tanggal, int $id): bool
    {
        $this->db->query("SELECT id FROM absen WHERE id_siswa = :id_siswa AND tanggal = :tanggal");
        $this->db->bind(':id_siswa', $id);
        $this->db->bind(':tanggal', $tanggal);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Simpan/update data absen satu siswa
     */
    public function simpanSiswa(string $tanggal, int $id, string $nama, array $dataSiswa): bool
    {
        $waktu_isi = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO absen (
            id_siswa, tanggal, waktu_isi, 
            sekolah_status, sekolah_ket, 
            almiftah_status, almiftah_ket, 
            diniyah_status, diniyah_ket, 
            subuh_status, subuh_ket, 
            quran_type, quran_jumlah, 
            baca_buku_status, baca_buku_jumlah, 
            dluha_status, belajar_status, 
            memaafkan_status, mendoakan_muslimin_status, mendoakan_ortu_status, shadaqah_status
        ) VALUES (
            :id_siswa, :tanggal, :waktu_isi,
            :sekolah_status, :sekolah_ket,
            :almiftah_status, :almiftah_ket,
            :diniyah_status, :diniyah_ket,
            :subuh_status, :subuh_ket,
            :quran_type, :quran_jumlah,
            :baca_buku_status, :baca_buku_jumlah,
            :dluha_status, :belajar_status,
            :memaafkan_status, :mendoakan_muslimin_status, :mendoakan_ortu_status, :shadaqah_status
        ) ON DUPLICATE KEY UPDATE
            waktu_isi = VALUES(waktu_isi),
            sekolah_status = VALUES(sekolah_status), sekolah_ket = VALUES(sekolah_ket),
            almiftah_status = VALUES(almiftah_status), almiftah_ket = VALUES(almiftah_ket),
            diniyah_status = VALUES(diniyah_status), diniyah_ket = VALUES(diniyah_ket),
            subuh_status = VALUES(subuh_status), subuh_ket = VALUES(subuh_ket),
            quran_type = VALUES(quran_type), quran_jumlah = VALUES(quran_jumlah),
            baca_buku_status = VALUES(baca_buku_status), baca_buku_jumlah = VALUES(baca_buku_jumlah),
            dluha_status = VALUES(dluha_status), belajar_status = VALUES(belajar_status),
            memaafkan_status = VALUES(memaafkan_status), mendoakan_muslimin_status = VALUES(mendoakan_muslimin_status), 
            mendoakan_ortu_status = VALUES(mendoakan_ortu_status), shadaqah_status = VALUES(shadaqah_status)";
            
        $this->db->query($sql);
        $this->db->bind(':id_siswa', $id);
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':waktu_isi', $waktu_isi);
        $this->db->bind(':sekolah_status', $dataSiswa['sekolah']['status'] ?? null);
        $this->db->bind(':sekolah_ket', $dataSiswa['sekolah']['ket'] ?? null);
        $this->db->bind(':almiftah_status', $dataSiswa['almiftah']['status'] ?? null);
        $this->db->bind(':almiftah_ket', $dataSiswa['almiftah']['ket'] ?? null);
        $this->db->bind(':diniyah_status', $dataSiswa['diniyah']['status'] ?? null);
        $this->db->bind(':diniyah_ket', $dataSiswa['diniyah']['ket'] ?? null);
        $this->db->bind(':subuh_status', $dataSiswa['subuh']['status'] ?? null);
        $this->db->bind(':subuh_ket', $dataSiswa['subuh']['ket'] ?? null);
        $this->db->bind(':quran_type', $dataSiswa['quran']['type'] ?? null);
        $this->db->bind(':quran_jumlah', $dataSiswa['quran']['jumlah'] ?? 0);
        $this->db->bind(':baca_buku_status', $dataSiswa['baca_buku']['status'] ?? null);
        $this->db->bind(':baca_buku_jumlah', $dataSiswa['baca_buku']['jumlah'] ?? 0);
        $this->db->bind(':dluha_status', $dataSiswa['dluha']['status'] ?? null);
        $this->db->bind(':belajar_status', $dataSiswa['belajar']['status'] ?? null);
        $this->db->bind(':memaafkan_status', $dataSiswa['memaafkan']['status'] ?? null);
        $this->db->bind(':mendoakan_muslimin_status', $dataSiswa['mendoakan_muslimin']['status'] ?? null);
        $this->db->bind(':mendoakan_ortu_status', $dataSiswa['mendoakan_ortu']['status'] ?? null);
        $this->db->bind(':shadaqah_status', $dataSiswa['shadaqah']['status'] ?? null);
        
        return $this->db->execute();
    }

    /**
     * Ambil semua rekap absen (semua tanggal)
     */
    public function getAllDates(): array
    {
        $this->db->query("
            SELECT tanggal, COUNT(id_siswa) as jumlah_isi, MAX(waktu_isi) as updated_at 
            FROM absen 
            GROUP BY tanggal 
            ORDER BY tanggal DESC
        ");
        return $this->db->resultSet();
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
        foreach ($daftarSiswa as $s) {
            if (!isset($siswaData[$s['id']])) {
                $stats['belum_isi'][] = $s['nama'];
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

    /**
     * Hapus data absen berdasarkan tanggal
     */
    public function deleteTanggal(string $tanggal): bool
    {
        $this->db->query("DELETE FROM absen WHERE tanggal = :tanggal");
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->execute();
    }

    /**
     * Hapus data absen satu siswa berdasarkan tanggal
     */
    public function deleteSiswa(string $tanggal, int $id): bool
    {
        $this->db->query("DELETE FROM absen WHERE tanggal = :tanggal AND id_siswa = :id_siswa");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':id_siswa', $id);
        return $this->db->execute();
    }
}
