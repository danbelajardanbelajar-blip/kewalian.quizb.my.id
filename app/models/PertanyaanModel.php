<?php
require_once APP_PATH . '/core/Model.php';

/**
 * PertanyaanModel.php
 * Mengelola konfigurasi pertanyaan dinamis per wali kelas (user_id)
 */
class PertanyaanModel extends Model
{
    /**
     * Ambil semua pertanyaan milik user tertentu
     */
    public function getAll(int $userId): array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE user_id = :user_id ORDER BY urutan ASC, id ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil pertanyaan yang aktif saja (untuk form siswa)
     */
    public function getActive(int $userId): array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE user_id = :user_id AND is_active = 1 ORDER BY urutan ASC, id ASC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    /**
     * Ambil detail satu pertanyaan
     */
    public function getById(int $id, int $userId): ?array
    {
        $this->db->query("SELECT * FROM pertanyaan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Tambah pertanyaan baru
     */
    public function insert(array $data): bool
    {
        $this->db->query("INSERT INTO pertanyaan (user_id, judul, tipe, opsi, urutan, is_active) 
                          VALUES (:user_id, :judul, :tipe, :opsi, :urutan, :is_active)");
        
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']); // 'pilihan_ganda' atau 'angka'
        $this->db->bind(':opsi', $data['opsi']); // JSON string
        $this->db->bind(':urutan', $data['urutan'] ?? 0);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        
        return $this->db->execute();
    }

    /**
     * Update pertanyaan
     */
    public function update(array $data): bool
    {
        $this->db->query("UPDATE pertanyaan SET 
                            judul = :judul, 
                            tipe = :tipe, 
                            opsi = :opsi, 
                            is_active = :is_active 
                          WHERE id = :id AND user_id = :user_id");
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':judul', $data['judul']);
        $this->db->bind(':tipe', $data['tipe']);
        $this->db->bind(':opsi', $data['opsi']);
        $this->db->bind(':is_active', $data['is_active']);
        
        return $this->db->execute();
    }

    /**
     * Hapus pertanyaan
     */
    public function delete(int $id, int $userId): bool
    {
        $this->db->query("DELETE FROM pertanyaan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Update urutan
     */
    public function updateUrutan(int $id, int $urutan, int $userId): bool
    {
        $this->db->query("UPDATE pertanyaan SET urutan = :urutan WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':urutan', $urutan);
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Buat pertanyaan default untuk user baru
     */
    public function createDefaultPertanyaan(int $userId): bool
    {
        $default_pertanyaan = [
            [
                'judul' => 'Kehadiran Sekolah',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Kehadiran Al-Miftah',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Kehadiran Diniyah',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Kehadiran Ngaji Pagi',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Hadir', 'value' => 'hadir', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Sakit', 'value' => 'sakit', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Izin', 'value' => 'izin', 'poin' => 5, 'require_ket' => true],
                    ['label' => 'Alpha', 'value' => 'alpha', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Membaca Al-Qur\'an',
                'tipe' => 'ganda_dan_angka',
                'opsi' => [
                    'pilihan' => [
                        ['label' => 'Iya', 'value' => 'iya', 'poin' => 0, 'require_ket' => false, 'require_angka' => true],
                        ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false, 'require_angka' => false]
                    ],
                    'angka' => [
                        'poin_per_angka' => 2,
                        'satuan' => 'Halaman'
                    ]
                ]
            ],
            [
                'judul' => 'Shalat Dluha',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Ikut', 'value' => 'ikut', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Udzur Haid', 'value' => 'udzur_haid', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Tidak Ikut', 'value' => 'tidak_ikut', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Belajar Mandiri di Kamar',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 10, 'require_ket' => false],
                    ['label' => 'Tidak', 'value' => 'tidak', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Membaca Buku',
                'tipe' => 'ganda_dan_angka',
                'opsi' => [
                    'pilihan' => [
                        ['label' => 'Iya', 'value' => 'iya', 'poin' => 0, 'require_ket' => false, 'require_angka' => true],
                        ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false, 'require_angka' => false]
                    ],
                    'angka' => [
                        'poin_per_angka' => 1,
                        'satuan' => 'Halaman'
                    ]
                ]
            ],
            [
                'judul' => 'Memaafkan Semua Teman',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Mendoakan Sesama Muslim',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Mendoakan Orang Tua',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ],
            [
                'judul' => 'Shadaqah / Membantu Teman',
                'tipe' => 'pilihan_ganda',
                'opsi' => [
                    ['label' => 'Iya', 'value' => 'iya', 'poin' => 5, 'require_ket' => false],
                    ['label' => 'Belum', 'value' => 'belum', 'poin' => 0, 'require_ket' => false]
                ]
            ]
        ];

        try {
            $urutan = 1;
            foreach ($default_pertanyaan as $dp) {
                $this->db->query("INSERT INTO pertanyaan (user_id, judul, tipe, opsi, urutan, is_active) 
                                VALUES (:user_id, :judul, :tipe, :opsi, :urutan, 1)");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':judul', $dp['judul']);
                $this->db->bind(':tipe', $dp['tipe']);
                $this->db->bind(':opsi', json_encode($dp['opsi']));
                $this->db->bind(':urutan', $urutan);
                $this->db->execute();
                $urutan++;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
