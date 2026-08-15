<?php
require_once APP_PATH . '/core/Controller.php';

class PertanyaanController extends Controller
{
    private $pertanyaanModel;

    public function __construct()
    {
        $this->requireAuth(); // Hanya wali kelas yang login bisa akses
        $this->pertanyaanModel = $this->model('PertanyaanModel');
    }

    public function index()
    {
        $userId = Session::get('user_id');
        $pertanyaan = $this->pertanyaanModel->getAll($userId);
        $settings = $this->pertanyaanModel->getUserSettings($userId);

        $this->view('pertanyaan/index', [
            'title' => 'Manajemen Pertanyaan Absen',
            'pertanyaan' => $pertanyaan,
            'settings' => $settings
        ]);
    }

    public function tambah()
    {
        $this->view('pertanyaan/form', [
            'title' => 'Tambah Pertanyaan',
            'data' => null,
            'action' => BASE_URL . '/pertanyaan/simpan'
        ]);
    }

    public function edit($id)
    {
        $userId = Session::get('user_id');
        $data = $this->pertanyaanModel->getById((int)$id, $userId);

        if (!$data) {
            Flash::set('error', 'Data tidak ditemukan.');
            $this->redirect('pertanyaan');
        }

        $this->view('pertanyaan/form', [
            'title' => 'Edit Pertanyaan',
            'data' => $data,
            'action' => BASE_URL . '/pertanyaan/update'
        ]);
    }

    public function simpan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $judul = trim($_POST['judul'] ?? '');
            $tipe = $_POST['tipe'] ?? 'pilihan_ganda';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            // Proses opsi JSON
            $opsiArray = [];
            if ($tipe === 'pilihan_ganda' || $tipe === 'ganda_dan_angka') {
                $labels = $_POST['opsi_label'] ?? [];
                $poins = $_POST['opsi_poin'] ?? [];
                $reqKets = $_POST['opsi_req_ket'] ?? [];
                $reqAngkas = $_POST['opsi_req_angka'] ?? [];

                $pilihanList = [];
                foreach ($labels as $idx => $label) {
                    $label = trim($label);
                    if ($label !== '') {
                        $pilihanList[] = [
                            'label' => $label,
                            'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label)),
                            'poin' => (int)($poins[$idx] ?? 0),
                            'require_ket' => isset($reqKets[$idx]) ? true : false,
                            'require_angka' => isset($reqAngkas[$idx]) ? true : false
                        ];
                    }
                }

                if ($tipe === 'ganda_dan_angka') {
                    $opsiArray['pilihan'] = $pilihanList;
                    $opsiArray['angka'] = [
                        'poin_per_angka' => (float)($_POST['gda_poin_per_angka'] ?? 1),
                        'satuan' => trim($_POST['gda_satuan'] ?? '')
                    ];
                } else {
                    $opsiArray = $pilihanList;
                }
            } else if ($tipe === 'angka') {
                $opsiArray = [
                    'poin_per_angka' => (float)($_POST['poin_per_angka'] ?? 1),
                    'require_ket' => isset($_POST['angka_req_ket']) ? true : false,
                    'satuan' => trim($_POST['satuan'] ?? '')
                ];
            }

            $data = [
                'user_id' => $userId,
                'judul' => $judul,
                'tipe' => $tipe,
                'opsi' => json_encode($opsiArray),
                'urutan' => 0, // bisa diperbaiki nanti untuk auto increment max urutan
                'is_active' => $isActive
            ];

            if ($this->pertanyaanModel->insert($data)) {
                Flash::set('success', 'Pertanyaan berhasil ditambahkan.');
            } else {
                Flash::set('error', 'Gagal menambahkan pertanyaan.');
            }
        }
        $this->redirect('pertanyaan');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $id = (int)$_POST['id'];
            $judul = trim($_POST['judul'] ?? '');
            $tipe = $_POST['tipe'] ?? 'pilihan_ganda';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            // Proses opsi JSON
            $opsiArray = [];
            if ($tipe === 'pilihan_ganda' || $tipe === 'ganda_dan_angka') {
                $labels = $_POST['opsi_label'] ?? [];
                $poins = $_POST['opsi_poin'] ?? [];
                $reqKets = $_POST['opsi_req_ket'] ?? [];
                $reqAngkas = $_POST['opsi_req_angka'] ?? [];

                $pilihanList = [];
                foreach ($labels as $idx => $label) {
                    $label = trim($label);
                    if ($label !== '') {
                        $pilihanList[] = [
                            'label' => $label,
                            'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label)),
                            'poin' => (int)($poins[$idx] ?? 0),
                            'require_ket' => isset($reqKets[$idx]) ? true : false,
                            'require_angka' => isset($reqAngkas[$idx]) ? true : false
                        ];
                    }
                }

                if ($tipe === 'ganda_dan_angka') {
                    $opsiArray['pilihan'] = $pilihanList;
                    $opsiArray['angka'] = [
                        'poin_per_angka' => (float)($_POST['gda_poin_per_angka'] ?? 1),
                        'satuan' => trim($_POST['gda_satuan'] ?? '')
                    ];
                } else {
                    $opsiArray = $pilihanList;
                }
            } else if ($tipe === 'angka') {
                $opsiArray = [
                    'poin_per_angka' => (float)($_POST['poin_per_angka'] ?? 1),
                    'require_ket' => isset($_POST['angka_req_ket']) ? true : false,
                    'satuan' => trim($_POST['satuan'] ?? '')
                ];
            }

            $data = [
                'id' => $id,
                'user_id' => $userId,
                'judul' => $judul,
                'tipe' => $tipe,
                'opsi' => json_encode($opsiArray),
                'is_active' => $isActive
            ];

            if ($this->pertanyaanModel->update($data)) {
                Flash::set('success', 'Pertanyaan berhasil diupdate.');
            } else {
                Flash::set('error', 'Gagal mengupdate pertanyaan.');
            }
        }
        $this->redirect('pertanyaan');
    }

    public function hapus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            if ($this->pertanyaanModel->delete((int)$id, $userId)) {
                Flash::set('success', 'Pertanyaan berhasil dihapus.');
            } else {
                Flash::set('error', 'Gagal menghapus pertanyaan.');
            }
        }
        $this->redirect('pertanyaan');
    }

    public function duplikat($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $data = $this->pertanyaanModel->getById((int)$id, $userId);
            
            if ($data) {
                // Duplicate data
                $newData = [
                    'user_id' => $userId,
                    'judul' => $data['judul'] . ' (Copy)',
                    'tipe' => $data['tipe'],
                    'opsi' => $data['opsi'],
                    'urutan' => (int)$data['urutan'] + 1,
                    'is_active' => (int)$data['is_active']
                ];
                
                if ($this->pertanyaanModel->insert($newData)) {
                    Flash::set('success', 'Pertanyaan berhasil diduplikat.');
                } else {
                    Flash::set('error', 'Gagal menduplikat pertanyaan.');
                }
            } else {
                Flash::set('error', 'Data tidak ditemukan.');
            }
        }
        $this->redirect('pertanyaan');
    }

    public function urut()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $urutan = $input['urutan'] ?? [];

        if (empty($urutan)) {
            $this->json(['success' => false, 'message' => 'Data urutan kosong'], 400);
        }

        $userId = Session::get('user_id');
        $success = true;

        foreach ($urutan as $index => $id) {
            if (!$this->pertanyaanModel->updateUrutan((int)$id, $index + 1, $userId)) {
                $success = false;
            }
        }

        if ($success) {
            $this->json(['success' => true, 'message' => 'Urutan berhasil disimpan']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan beberapa urutan'], 500);
        }
    }

    public function update_settings()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $acakPertanyaan = isset($input['acak_pertanyaan']) && $input['acak_pertanyaan'] ? 1 : 0;
        $acakJawaban = isset($input['acak_jawaban']) && $input['acak_jawaban'] ? 1 : 0;

        $userId = Session::get('user_id');
        
        if ($this->pertanyaanModel->updateSettings($userId, $acakPertanyaan, $acakJawaban)) {
            $this->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan pengaturan'], 500);
        }
    }
}
