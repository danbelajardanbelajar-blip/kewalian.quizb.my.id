<?php
require_once APP_PATH . "/core/Model.php";

class WalimuridModel extends Model
{
    public function getSiswaById(int $id)
    {
        $this->db->query("SELECT * FROM siswa WHERE id = :id");
        $this->db->bind(":id", $id);
        return $this->db->single();
    }

    public function verifyNoHp(int $id, string $noHp): bool
    {
        $siswa = $this->getSiswaById($id);
        if (!$siswa) return false;
        
        // Remove formatting from both sides
        $dbHp = preg_replace("/[^0-9]/", "", $siswa["no_hp"] ?? "");
        $inputHp = preg_replace("/[^0-9]/", "", $noHp);

        if (empty($dbHp) || empty($inputHp)) return false;
        return $dbHp === $inputHp;
    }

    public function getProgress(int $id_siswa): array
    {
        // Get total poin per day
        $this->db->query("
            SELECT h.tanggal, SUM(d.poin) as total_poin
            FROM absen_header h
            JOIN absen_detail d ON h.id = d.id_absen
            WHERE h.id_siswa = :id_siswa
            GROUP BY h.id, h.tanggal
            ORDER BY h.tanggal ASC
        ");
        $this->db->bind(":id_siswa", $id_siswa);
        return $this->db->resultSet();
    }

    public function getRanking(int $user_id): array
    {
        // Get total poin for all students in the same class (user_id)
        $this->db->query("
            SELECT s.id, s.nama, COALESCE(SUM(d.poin), 0) as total_poin
            FROM siswa s
            LEFT JOIN absen_header h ON s.id = h.id_siswa
            LEFT JOIN absen_detail d ON h.id = d.id_absen
            WHERE s.user_id = :user_id
            GROUP BY s.id, s.nama
            ORDER BY total_poin DESC
        ");
        $this->db->bind(":user_id", $user_id);
        
        $results = $this->db->resultSet();
        $rankings = [];
        $rank = 1;
        
        // Hitung rata-rata kelas
        $totalSemuaPoin = 0;
        foreach ($results as $row) {
            $totalSemuaPoin += (int)($row["total_poin"] ?? 0);
        }
        $jumlahSiswa = count($results);
        $avgPoin = $jumlahSiswa > 0 ? $totalSemuaPoin / $jumlahSiswa : 0;

        foreach ($results as $row) {
            $poin = (int)($row["total_poin"] ?? 0);
            
            // Rating 1-5 berdasarkan rata-rata kelas
            if ($avgPoin > 0) {
                $ratio = $poin / $avgPoin;
                // ratio 0 = rating 1, ratio 1 (rata-rata) = rating 3, ratio 2 = rating 5
                $rating = (int)round(1 + ($ratio * 2));
                if ($rating < 1) $rating = 1;
                if ($rating > 5) $rating = 5;
            } else {
                $rating = 0; // Jika kelas belum ada nilai sama sekali
            }

            $rankings[$row["id"]] = [
                "rank" => $rank,
                "rating" => $rating,
                "nama" => $row["nama"],
                "total_poin" => $poin,
                "avg_kelas" => round($avgPoin, 1)
            ];
            $rank++;
        }
        return $rankings;
    }
    public function getRiwayatDetail(int $id_siswa): array
    {
        $this->db->query("
            SELECT h.tanggal, h.waktu_isi,
                   d.jawaban, d.keterangan, d.poin,
                   p.judul as pertanyaan, p.urutan, p.tipe, p.opsi
            FROM absen_header h
            JOIN absen_detail d ON h.id = d.id_absen
            LEFT JOIN pertanyaan p ON d.id_pertanyaan = p.id
            WHERE h.id_siswa = :id_siswa
            ORDER BY h.tanggal DESC, p.urutan ASC
        ");
        $this->db->bind(":id_siswa", $id_siswa);
        $results = $this->db->resultSet();
        
        $riwayat = [];
        foreach ($results as $row) {
            $tanggal = $row['tanggal'];
            if (!isset($riwayat[$tanggal])) {
                $riwayat[$tanggal] = [
                    'tanggal' => $tanggal,
                    'waktu_isi' => $row['waktu_isi'],
                    'total_poin' => 0,
                    'detail' => []
                ];
            }
            
            $jawabanLabel = $row['jawaban'];
            if (!empty($row['opsi'])) {
                $opsiArr = json_decode($row['opsi'], true);
                if ($row['tipe'] === 'pilihan_ganda' && is_array($opsiArr)) {
                    foreach ($opsiArr as $op) {
                        if (isset($op['value']) && $op['value'] === $jawabanLabel) {
                            $jawabanLabel = $op['label'] ?? $jawabanLabel;
                            break;
                        }
                    }
                } elseif ($row['tipe'] === 'ganda_dan_angka' && is_array($opsiArr) && isset($opsiArr['pilihan'])) {
                    // ganda_dan_angka saves answer as "value:angka"
                    $parts = explode(':', $jawabanLabel);
                    $val = $parts[0] ?? '';
                    $num = $parts[1] ?? '';
                    foreach ($opsiArr['pilihan'] as $op) {
                        if (isset($op['value']) && $op['value'] === $val) {
                            $jawabanLabel = $op['label'] ?? $val;
                            if (!empty($num) && $num !== '0') {
                                $satuan = $opsiArr['angka']['satuan'] ?? '';
                                $jawabanLabel .= " ($num $satuan)";
                            }
                            break;
                        }
                    }
                }
            }
            
            $riwayat[$tanggal]['total_poin'] += $row['poin'];
            $riwayat[$tanggal]['detail'][] = [
                'pertanyaan' => $row['pertanyaan'] ?? 'Pertanyaan Dihapus',
                'jawaban' => $jawabanLabel,
                'keterangan' => $row['keterangan'],
                'poin' => $row['poin'],
                'tipe' => $row['tipe'] ?? 'unknown'
            ];
        }
        return $riwayat;
    }
}

