    /**
     * POST /absen/kirim_wa_asrama ?? Mengirim laporan asrama via WA
     */
    public function kirim_wa_asrama(): void
    {
        \->requireAuth();
        if (!\->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        \ = json_decode(file_get_contents('php://input'), true);
        \ = trim(\['tanggal'] ?? '');
        \ = trim(\['nama_asrama'] ?? '');
        
        if (empty(\) || empty(\)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            return;
        }

        \ = Session::get('user_id');
        
        // Ambil data pengurus asrama
        require_once APP_PATH . '/models/AsramaModel.php';
        \ = new AsramaModel();
        \ = \->getByName(\, \);
        
        if (!\ || empty(\['no_hp'])) {
            echo json_encode(['success' => false, 'message' => 'Pengurus atau nomor HP belum diatur.']);
            return;
        }

        // Ambil data absen pada tanggal tsb
        \ = \->absenModel->getByTanggal(\, \);
        \ = \->konfig->getSiswa(\);
        
        // Filter siswa yang asramanya sesuai
        \ = [];
        foreach (\ as \) {
            if (trim(\['asrama']) === \) {
                \[] = \;
            }
        }
        
        if (empty(\)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada siswa di asrama ini.']);
            return;
        }

        // Susun Laporan
        \ = date('d F Y', strtotime(\));
        \ = "*LAPORAN KEDISIPLINAN ASRAMA*\n";
        \ .= "Asrama: *{\}*\n";
        \ .= "Pengurus: *{\['nama_pengurus']}*\n";
        \ .= "Tanggal: *{\}*\n\n";
        
        \ = 1;
        foreach (\ as \) {
            \ = \['id'];
            \ = \['nama'];
            
            if (isset(\['siswa'][\])) {
                \ = \['siswa'][\]['total_poin'] ?? 0;
                \ .= "{\}. {\} ? Poin: {\}\n";
            } else {
                \ .= "{\}. {\} ? Belum Mengisi Laporan\n";
            }
            \++;
        }
        
        \ .= "\n_Pesan otomatis dari Sistem Kedisiplinan Santri._";

        \ = 'wa-key-1e0a672693117e4d09db166e49979691';
        \    = 'https://wa.quizb.my.id/api/send.php';
        \ = [
            'phone_number'   => \['no_hp'],
            'message'        => \,
            'scheduled_time' => ''
        ];

        \ = curl_init(\);
        curl_setopt(\, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\, CURLOPT_POST, true);
        curl_setopt(\, CURLOPT_POSTFIELDS, json_encode(\));
        curl_setopt(\, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . \
        ]);

        \ = curl_exec(\);
        \ = curl_getinfo(\, CURLINFO_HTTP_CODE);
        curl_close(\);

        if (\ === 200) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengirim WA. Code: ' . \]);
        }
    }
