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

        $this->view('pertanyaan/index', [
            'title' => 'Manajemen Pertanyaan Absen',
            'pertanyaan' => $pertanyaan
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
            if ($tipe === 'pilihan_ganda') {
                $labels = $_POST['opsi_label'] ?? [];
                $poins = $_POST['opsi_poin'] ?? [];
                $reqKets = $_POST['opsi_req_ket'] ?? [];

                foreach ($labels as $idx => $label) {
                    $label = trim($label);
                    if ($label !== '') {
                        $opsiArray[] = [
                            'label' => $label,
                            'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label)),
                            'poin' => (int)($poins[$idx] ?? 0),
                            'require_ket' => isset($reqKets[$idx]) ? true : false
                        ];
                    }
                }
            } else if ($tipe === 'angka') {
                $opsiArray = [
                    'poin_per_angka' => (int)($_POST['poin_per_angka'] ?? 1),
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
            if ($tipe === 'pilihan_ganda') {
                $labels = $_POST['opsi_label'] ?? [];
                $poins = $_POST['opsi_poin'] ?? [];
                $reqKets = $_POST['opsi_req_ket'] ?? [];

                foreach ($labels as $idx => $label) {
                    $label = trim($label);
                    if ($label !== '') {
                        $opsiArray[] = [
                            'label' => $label,
                            'value' => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $label)),
                            'poin' => (int)($poins[$idx] ?? 0),
                            'require_ket' => isset($reqKets[$idx]) ? true : false
                        ];
                    }
                }
            } else if ($tipe === 'angka') {
                $opsiArray = [
                    'poin_per_angka' => (int)($_POST['poin_per_angka'] ?? 1),
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
}
