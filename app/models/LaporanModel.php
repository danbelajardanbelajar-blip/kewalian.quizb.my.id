<?php
require_once APP_PATH . '/core/Model.php';

/**
 * LaporanModel.php
 * Mengelola data laporan presensi dinamis dari MySQL
 */
class LaporanModel extends Model
{
    public function getByTanggal(string $tanggal, int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        return $absenModel->getByTanggal($tanggal, $userId);
    }

    public function delete(string $tanggal, int $userId = null): bool
    {
        $userId = $userId ?? Session::get('user_id');
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        return $absenModel->deleteTanggal($tanggal, $userId);
    }

    public function exists(string $tanggal, int $userId = null): bool
    {
        $userId = $userId ?? Session::get('user_id');
        $this->db->query("
            SELECT h.id FROM absen_header h 
            JOIN siswa s ON h.id_siswa = s.id 
            WHERE h.tanggal = :tanggal AND s.user_id = :user_id LIMIT 1
        ");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getAll(int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        $this->db->query("
            SELECT h.tanggal, COUNT(h.id_siswa) as jumlah_siswa, MAX(h.waktu_isi) as updated_at 
            FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE s.user_id = :user_id
            GROUP BY h.tanggal 
            ORDER BY h.tanggal DESC
        ");
        $this->db->bind(':user_id', $userId);
        $laporan = $this->db->resultSet();
        
        require_once APP_PATH . '/models/KonfigurasiModel.php';
        $konfig = new KonfigurasiModel();
        $kelas = $konfig->getKelas($userId);

        foreach ($laporan as &$lap) {
            $lap['kelas'] = $kelas;
            $lap['created_by'] = 'admin'; 
        }
        return $laporan;
    }

    /**
     * Hitung rekap kehadiran/poin per siswa dari semua laporan
     */
    public function getRekapPerSiswa(int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        
        // Ambil semua header absen
        $this->db->query("
            SELECT h.*, s.nama 
            FROM absen_header h 
            JOIN siswa s ON h.id_siswa = s.id 
            WHERE s.user_id = :user_id
            ORDER BY h.id_siswa ASC
        ");
        $this->db->bind(':user_id', $userId);
        $headers = $this->db->resultSet();

        $rekap = [];
        if (empty($headers)) return $rekap;
        
        $headerIds = array_column($headers, 'id');
        $inQuery = implode(',', array_fill(0, count($headerIds), '?'));
        
        // Ambil semua detail
        $this->db->query("SELECT id_absen, id_pertanyaan, poin FROM absen_detail WHERE id_absen IN ($inQuery)");
        foreach ($headerIds as $k => $vid) {
            $this->db->bind($k + 1, $vid);
        }
        $details = $this->db->resultSet();
        
        // Group details by id_absen
        $detByAbsen = [];
        foreach ($details as $d) {
            $detByAbsen[$d['id_absen']][] = $d;
        }

        // Susun rekap
        foreach ($headers as $h) {
            $id = $h['id_siswa'];
            if (!isset($rekap[$id])) {
                $rekap[$id] = [
                    'id' => $id, 
                    'nama' => $h['nama'], 
                    'total_poin' => 0, 
                    'total_hari' => 0
                ];
            }

            $rekap[$id]['total_hari']++;
            
            // Hitung poin dari absen_detail
            $absenDetails = $detByAbsen[$h['id']] ?? [];
            foreach ($absenDetails as $d) {
                $rekap[$id]['total_poin'] += $d['poin'];
            }
        }

        return $rekap;
    }

    public function getByRange(string $dari, string $sampai, int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        $this->db->query("
            SELECT DISTINCT h.tanggal 
            FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE h.tanggal >= :dari AND h.tanggal <= :sampai AND s.user_id = :user_id
            ORDER BY h.tanggal ASC
        ");
        $this->db->bind(':dari', $dari);
        $this->db->bind(':sampai', $sampai);
        $this->db->bind(':user_id', $userId);
        $tanggals = $this->db->resultSet();

        $laporan = [];
        require_once APP_PATH . '/models/AbsenModel.php';
        $absenModel = new AbsenModel();
        
        foreach ($tanggals as $row) {
            $tgl = $row['tanggal'];
            $data = $absenModel->getByTanggal($tgl, $userId);
            if (!empty($data)) {
                $laporan[$tgl] = $data;
            }
        }

        return $laporan;
    }
}
