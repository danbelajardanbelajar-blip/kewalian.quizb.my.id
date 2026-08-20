    /**
     * GET /absen/rekap_asrama
     */
    public function rekap_asrama(): void
    {
        $this->requireAuth();

        $userId = Session::get('user_id');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $siswa   = $this->konfig->getSiswa($userId);
        $kelas   = $this->konfig->getKelas($userId);

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $allDates    = $this->absenModel->getAllDates($userId);
        
        require_once APP_PATH . '/models/AsramaModel.php';
        $asramaModel = new AsramaModel();
        $listPengurus = $asramaModel->getAll($userId);
        
        $pengurusMap = [];
        foreach ($listPengurus as $p) {
            $pengurusMap[$p['nama_asrama']] = $p;
        }

        $this->view('absen/rekap_asrama', [
            'title'       => 'Rekap Per Asrama - ' . date('d F Y', strtotime($tanggal)),
            'tanggal'     => $tanggal,
            'kelas'       => $kelas,
            'dataTanggal' => $dataTanggal,
            'siswa'       => $siswa,
            'allDates'    => $allDates,
            'pengurusMap' => $pengurusMap
        ]);
    }

    /**
     * POST /absen/kirim_wa_asrama
     */
    public function kirim_wa_asrama(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $json = json_decode(file_get_contents('php://input'), true);
        $tanggal = trim($json['tanggal'] ?? '');
        $namaAsrama = trim($json['nama_asrama'] ?? '');
        
        if (empty($tanggal) || empty($namaAsrama)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap.']);
            return;
        }

        $userId = Session::get('user_id');
        
        require_once APP_PATH . '/models/AsramaModel.php';
        $asramaModel = new AsramaModel();
        $pengurus = $asramaModel->getByName($userId, $namaAsrama);
        
        if (!$pengurus || empty($pengurus['no_hp'])) {
            $this->json(['success' => false, 'message' => 'Pengurus atau nomor HP belum diatur.']);
            return;
        }

        $dataTanggal = $this->absenModel->getByTanggal($tanggal, $userId);
        $siswa = $this->konfig->getSiswa($userId);
        
        $siswaAsrama = [];
        foreach ($siswa as $s) {
            if (trim($s['asrama']) === $namaAsrama) {
                $siswaAsrama[] = $s;
            }
        }
        
        if (empty($siswaAsrama)) {
            $this->json(['success' => false, 'message' => 'Tidak ada siswa di asrama ini.']);
            return;
        }

        $tglIndo = date('d F Y', strtotime($tanggal));
        $msg = "*LAPORAN KEDISIPLINAN ASRAMA*\n";
        $msg .= "Asrama: *{$namaAsrama}*\n";
        $msg .= "Pengurus: *{$pengurus['nama_pengurus']}*\n";
        $msg .= "Tanggal: *{$tglIndo}*\n\n";
        
        $no = 1;
        foreach ($siswaAsrama as $s) {
            $sId = $s['id'];
            $nama = $s['nama'];
            
            if (isset($dataTanggal['siswa'][$sId])) {
                $poin = $dataTanggal['siswa'][$sId]['total_poin'] ?? 0;
                $msg .= "{$no}. {$nama} - Poin: {$poin}\n";
            } else {
                $msg .= "{$no}. {$nama} - Belum Mengisi Laporan\n";
            }
            $no++;
        }
        
        $msg .= "\n_Pesan otomatis dari Sistem Kedisiplinan Santri._";

        $apiKey = 'wa-key-1e0a672693117e4d09db166e49979691';
        $url    = 'https://wa.quizb.my.id/api/send.php';
        $dataWa = [
            'phone_number'   => $pengurus['no_hp'],
            'message'        => $msg,
            'scheduled_time' => ''
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataWa));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->json(['success' => true, 'message' => 'Pesan berhasil dikirim via API']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal mengirim pesan via API']);
        }
    }
