<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/AsramaModel.php';

class AsramaController extends Controller
{
    private AsramaModel $asramaModel;

    public function __construct()
    {
        $this->asramaModel = new AsramaModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');
        
        $data = $this->asramaModel->getAll($userId);
        
        // Seed default if empty
        if (empty($data)) {
            $defaults = [
                'Asrama A' => ['Ust Jawad', '6282245427571'],
                'Asrama B' => ['Ust Nadzifah', '6285785368979'],
                'Asrama C' => ['Ust Annisa', '6285773511334'],
                'Asrama D' => ['Ust Fani', '6285785841967'],
                'Asrama E' => ['Ust Fitri', '6287816048669'],
            ];
            foreach ($defaults as $asrama => $info) {
                $this->asramaModel->save($userId, $asrama, $info[0], $info[1]);
            }
            $data = $this->asramaModel->getAll($userId);
        }

        $this->view('asrama/pengurus', [
            'title' => 'Pengurus Asrama',
            'data'  => $data
        ]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->redirect('asrama');
        }

        $userId = Session::get('user_id');
        $namaAsrama = trim($_POST['nama_asrama'] ?? '');
        $namaPengurus = trim($_POST['nama_pengurus'] ?? '');
        $noHp = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');

        if (empty($namaAsrama) || empty($namaPengurus)) {
            Flash::set('error', 'Nama Asrama dan Pengurus wajib diisi.');
            $this->redirect('asrama');
            return;
        }

        if ($this->asramaModel->save($userId, $namaAsrama, $namaPengurus, $noHp)) {
            Flash::set('success', 'Data pengurus berhasil disimpan.');
        } else {
            Flash::set('error', 'Gagal menyimpan data.');
        }

        $this->redirect('asrama');
    }
    
    public function hapus(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) {
            $this->redirect('asrama');
        }

        $userId = Session::get('user_id');
        $id = (int)($_POST['id'] ?? 0);

        if ($this->asramaModel->hapus($id, $userId)) {
            Flash::set('success', 'Data pengurus berhasil dihapus.');
        } else {
            Flash::set('error', 'Gagal menghapus data.');
        }

        $this->redirect('asrama');
    }
}
