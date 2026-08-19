<?php
require_once APP_PATH . '/core/Model.php';

/**
 * AbsenModel.php
 * Mengelola data absen mandiri siswa secara dinamis.
 */
class AbsenModel extends Model
{
    /**
     * Ambil data absen berdasarkan tanggal
     * Mengembalikan struktur array yang memuat list siswa beserta jawaban dinamisnya.
     */
    public function getByTanggal(string $tanggal, int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        
        // 1. Ambil header absen
        $this->db->query("
            SELECT h.*, s.nama 
            FROM absen_header h 
            JOIN siswa s ON h.id_siswa = s.id 
            WHERE h.tanggal = :tanggal AND s.user_id = :user_id
        ");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':user_id', $userId);
        $headers = $this->db->resultSet();

        if (empty($headers)) {
            return ['tanggal' => $tanggal, 'siswa' => []];
        }

        $headerIds = array_column($headers, 'id');
        $inQuery = implode(',', array_fill(0, count($headerIds), '?'));

        // 2. Ambil detail jawaban untuk header-header tersebut
        $this->db->query("SELECT * FROM absen_detail WHERE id_absen IN ($inQuery)");
        foreach ($headerIds as $k => $vid) {
            $this->db->bind($k + 1, $vid); // array PDO bindings 1-indexed
        }
        $details = $this->db->resultSet();

        // 3. Kelompokkan detail ke tiap siswa
        $data = [
            'tanggal' => $tanggal,
            'siswa'   => []
        ];

        foreach ($headers as $h) {
            $idSiswa = $h['id_siswa'];
            $data['siswa'][$idSiswa] = [
                'id' => $idSiswa,
                'nama' => $h['nama'],
                'waktu_isi' => $h['waktu_isi'],
                'total_poin' => 0,
                'jawaban' => [] // key: id_pertanyaan
            ];
        }

        foreach ($details as $d) {
            // cari siapa yang punya id_absen ini
            foreach ($headers as $h) {
                if ($h['id'] == $d['id_absen']) {
                    $idSiswa = $h['id_siswa'];
                    $data['siswa'][$idSiswa]['jawaban'][$d['id_pertanyaan']] = [
                        'jawaban' => $d['jawaban'],
                        'keterangan' => $d['keterangan'],
                        'poin' => $d['poin']
                    ];
                    $data['siswa'][$idSiswa]['total_poin'] += $d['poin'];
                    break;
                }
            }
        }

        return $data;
    }

    public function getSiswaByTanggal(string $tanggal, int $id, int $userId = null): array
    {
        $data = $this->getByTanggal($tanggal, $userId);
        return $data['siswa'][$id] ?? [];
    }

    public function sudahIsi(string $tanggal, int $id): bool
    {
        $this->db->query("SELECT id FROM absen_header WHERE id_siswa = :id_siswa AND tanggal = :tanggal");
        $this->db->bind(':id_siswa', $id);
        $this->db->bind(':tanggal', $tanggal);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Menyimpan data dari form dinamis
     * $jawabanArray format: [id_pertanyaan => ['jawaban' => '...', 'keterangan' => '...', 'poin' => 10]]
     */
    public function simpanSiswa(string $tanggal, int $id, array $jawabanArray): bool
    {
        $waktu_isi = date('Y-m-d H:i:s');
        
        try {
            $this->db->beginTransaction();

            // Insert atau Update Header
            $this->db->query("SELECT id FROM absen_header WHERE id_siswa = :id_siswa AND tanggal = :tanggal");
            $this->db->bind(':id_siswa', $id);
            $this->db->bind(':tanggal', $tanggal);
            $existing = $this->db->single();

            $id_absen = null;
            if ($existing) {
                $id_absen = $existing['id'];
                $this->db->query("UPDATE absen_header SET waktu_isi = :waktu_isi WHERE id = :id");
                $this->db->bind(':waktu_isi', $waktu_isi);
                $this->db->bind(':id', $id_absen);
                $this->db->execute();
            } else {
                $this->db->query("INSERT INTO absen_header (id_siswa, tanggal, waktu_isi) VALUES (:id_siswa, :tanggal, :waktu_isi)");
                $this->db->bind(':id_siswa', $id);
                $this->db->bind(':tanggal', $tanggal);
                $this->db->bind(':waktu_isi', $waktu_isi);
                $this->db->execute();
                $id_absen = $this->db->lastInsertId();
            }

            // Hapus detail lama jika ada
            $this->db->query("DELETE FROM absen_detail WHERE id_absen = :id_absen");
            $this->db->bind(':id_absen', $id_absen);
            $this->db->execute();

            // Insert detail baru
            foreach ($jawabanArray as $id_pertanyaan => $ans) {
                $this->db->query("INSERT INTO absen_detail (id_absen, id_pertanyaan, jawaban, keterangan, poin) 
                                  VALUES (:id_absen, :id_pertanyaan, :jawaban, :keterangan, :poin)");
                $this->db->bind(':id_absen', $id_absen);
                $this->db->bind(':id_pertanyaan', $id_pertanyaan);
                $this->db->bind(':jawaban', $ans['jawaban'] ?? '');
                $this->db->bind(':keterangan', $ans['keterangan'] ?? null);
                $this->db->bind(':poin', (int)($ans['poin'] ?? 0));
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving absen dinamis: " . $e->getMessage());
            return false;
        }
    }

    public function getLateSubmissionsByDate(string $startDate, string $endDate, int $userId): array
    {
        $this->db->query("
            SELECT h.id_siswa, DATE(h.waktu_isi) as tgl_isi, TIME(h.waktu_isi) as jam_isi
            FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE s.user_id = :user_id 
              AND DATE(h.waktu_isi) BETWEEN :start_date AND :end_date
              AND TIME(h.waktu_isi) > '07:00:00'
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        
        $result = $this->db->resultSet();
        $lateMap = [];
        foreach ($result as $row) {
            $lateMap[$row['id_siswa']][$row['tgl_isi']] = substr($row['jam_isi'], 0, 5); // HH:MM
        }
        return $lateMap;
    }

    public function getAllDates(int $userId = null): array
    {
        $userId = $userId ?? Session::get('user_id');
        
        $this->db->query("
            SELECT h.tanggal, COUNT(h.id_siswa) as jumlah_isi, MAX(h.waktu_isi) as updated_at 
            FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE s.user_id = :user_id
            GROUP BY h.tanggal 
            ORDER BY h.tanggal DESC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * getStatistik tidak lagi dihitung berdasarkan kolom hardcoded.
     * Akan mengembalikan stats dasar, laporan per soal dilakukan di laporan.
     */
    public function getStatistik(string $tanggal, array $daftarSiswa, int $userId = null): array
    {
        $data = $this->getByTanggal($tanggal, $userId);
        $siswaData = $data['siswa'] ?? [];

        $stats = [
            'total'        => count($daftarSiswa),
            'sudah_isi'    => count($siswaData),
            'belum_isi'    => [],
        ];

        foreach ($daftarSiswa as $s) {
            if (!isset($siswaData[$s['id']])) {
                $stats['belum_isi'][] = $s['nama'];
            }
        }

        return $stats;
    }

    public function deleteSiswa(string $tanggal, int $idSiswa, int $userId = null): bool
    {
        $userId = $userId ?? Session::get('user_id');
        if (!$userId) return false;
        
        $this->db->query("
            DELETE h FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE h.tanggal = :tanggal AND h.id_siswa = :id_siswa AND s.user_id = :user_id
        ");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':id_siswa', $idSiswa);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function deleteTanggal(string $tanggal, int $userId = null): bool
    {
        $userId = $userId ?? Session::get('user_id');
        if (!$userId) return false;
        
        $this->db->query("
            DELETE h FROM absen_header h
            JOIN siswa s ON h.id_siswa = s.id
            WHERE h.tanggal = :tanggal AND s.user_id = :user_id
        ");
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}
