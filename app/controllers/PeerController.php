<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/PeerReviewModel.php';

class PeerController extends Controller
{
    private PeerReviewModel $peerModel;

    public function __construct()
    {
        $this->peerModel = new PeerReviewModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');
        $pertanyaan = $this->peerModel->getPertanyaan($userId);

        $this->view('peer/index', [
            'title' => 'Manajemen Pertanyaan Peer Review',
            'pertanyaan' => $pertanyaan
        ]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect('peer');

        $userId = Session::get('user_id');
        $id = (int)($_POST['id'] ?? 0);
        $pertanyaan = trim($_POST['pertanyaan'] ?? '');
        $sifat = in_array($_POST['sifat'] ?? '', ['positif', 'negatif']) ? $_POST['sifat'] : 'positif';
        $status = (int)($_POST['status'] ?? 1);

        if (empty($pertanyaan)) {
            Flash::set('error', 'Pertanyaan tidak boleh kosong.');
            $this->redirect('peer');
        }

        $db = new Database();
        if ($id > 0) {
            $db->query("UPDATE peer_pertanyaan SET pertanyaan = :p, sifat = :s, status = :st WHERE id = :id AND user_id = :uid");
            $db->bind(':id', $id);
            $db->bind(':uid', $userId);
            $msg = 'diperbarui';
        } else {
            $db->query("INSERT INTO peer_pertanyaan (user_id, pertanyaan, sifat, status) VALUES (:uid, :p, :s, :st)");
            $db->bind(':uid', $userId);
            $msg = 'ditambahkan';
        }
        $db->bind(':p', $pertanyaan);
        $db->bind(':s', $sifat);
        $db->bind(':st', $status);
        
        if ($db->execute()) {
            Flash::set('success', "Pertanyaan berhasil $msg.");
        } else {
            Flash::set('error', 'Gagal menyimpan pertanyaan.');
        }

        $this->redirect('peer');
    }

    public function hapus(): void
    {
        $this->requireAuth();
        if (!$this->isPost()) $this->redirect('peer');

        $userId = Session::get('user_id');
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $db = new Database();
            $db->query("DELETE FROM peer_pertanyaan WHERE id = :id AND user_id = :uid");
            $db->bind(':id', $id);
            $db->bind(':uid', $userId);
            
            if ($db->execute()) {
                // Delete related votes
                $db->query("DELETE FROM peer_vote WHERE id_pertanyaan = :id");
                $db->bind(':id', $id);
                $db->execute();
                
                Flash::set('success', 'Pertanyaan berhasil dihapus.');
            } else {
                Flash::set('error', 'Gagal menghapus pertanyaan.');
            }
        }
        $this->redirect('peer');
    }
    
    public function dashboard(): void
    {
        $this->requireAuth();
        $userId = Session::get('user_id');
        
        $pertanyaan = $this->peerModel->getActivePertanyaan($userId);
        $rentang = $_GET['rentang'] ?? 'semua';
        
        $leaderboard = [];
        foreach ($pertanyaan as $p) {
            $leaderboard[$p['id']] = $this->peerModel->getLeaderboard($p['id'], $rentang);
        }
        
        $this->view('peer/dashboard', [
            'title' => 'Dashboard Peta Karakter (Peer Review)',
            'pertanyaan' => $pertanyaan,
            'leaderboard' => $leaderboard,
            'rentang' => $rentang
        ]);
    }
}
