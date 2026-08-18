<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Absen Mandiri Siswa — Kelas <?= htmlspecialchars($kelas) ?>">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
</head>
<body class="absen-page">

<!-- Hero Header -->
<div class="absen-hero">
    <div class="absen-hero-content">
        <div class="absen-logo">
            <i class="bi bi-pencil-square"></i>
        </div>
        <h1 class="absen-hero-title">Absen Mandiri</h1>
        <p class="absen-hero-sub">
            Kelas <?= htmlspecialchars($kelas) ?> &nbsp;·&nbsp;
            <?= date('l, d F Y', strtotime($tanggal)) ?>
        </p>
    </div>
</div>

<div class="absen-container">

    <!-- Flash messages -->
    <?= Flash::render() ?>

    <!-- Info box -->
    <div class="absen-info-box mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        Klik namamu, lalu jawab pertanyaan dengan jujur. Hanya perlu 1 menit!
    </div>

    <!-- Search -->
    <div class="mb-4">
        <div class="absen-search-wrap">
            <i class="bi bi-search absen-search-icon"></i>
            <input type="text" id="searchNama" class="absen-search"
                   placeholder="Ketik namamu di sini..." autocomplete="off" autofocus>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="siswa-grid" id="gridSiswa">
        <?php foreach ($siswa as $i => $s): ?>
            <?php $done = $sudahIsi[$s['id']] ?? false; ?>
            <?php $url  = BASE_URL . '/absen/isi/' . $s['id'] . '?tanggal=' . $tanggal . '&wali=' . $idWali; ?>
            <?php $hasKode = !empty($s['kode_akses']) ? 1 : 0; ?>

            <a href="#"
               class="siswa-card <?= $done ? 'siswa-card--done' : '' ?>"
               title="<?= htmlspecialchars($s['nama']) ?>"
               data-nama="<?= htmlspecialchars(strtolower($s['nama'])) ?>"
               data-id="<?= $s['id'] ?>"
               data-haskode="<?= $hasKode ?>"
               data-url="<?= $url ?>"
               onclick="openKodeModal(this, event)">
                
                <div class="siswa-card-no" title="ID Siswa"><?= $s['id'] ?></div>
                
                <div class="siswa-card-nama">
                    <?= htmlspecialchars($s['nama']) ?></div>
                <?php if ($done): ?>
                    <div class="siswa-card-status">
                        <i class="bi bi-check-circle-fill"></i> Sudah Isi
                    </div>
                <?php else: ?>
                    <div class="siswa-card-status siswa-card-status--pending">
                        <i class="bi bi-circle"></i> Belum Isi
                    </div>
                <?php endif; ?>
                <div class="siswa-card-arrow">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty state saat search tidak ada hasil -->
    <div id="emptySearch" class="absen-empty" style="display:none">
        <i class="bi bi-search display-5 d-block mb-2"></i>
        <p>Nama tidak ditemukan</p>
    </div>

    <!-- Footer -->
    <div class="absen-footer-note mt-4">
        <i class="bi bi-shield-lock me-1"></i>
        Data kamu bersifat rahasia dan hanya bisa dilihat oleh Wali Kelas
    </div>
</div>

<!-- Modal Kode Akses -->
<div class="modal fade" id="kodeModal" tabindex="-1" aria-labelledby="kodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="kodeForm" action="<?= BASE_URL ?>/absen/verify_kode" method="POST">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="kodeModalLabel">Masukkan Kode Akses</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="kodeAlert" class="alert alert-warning d-none" style="font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Perhatian:</strong> Buat kode akses rahasia (minimal 3 huruf, jangan gunakan angka). 
                <span class="text-danger fw-bold">JANGAN beritahukan kode ini kepada temanmu!</span>
            </div>
            
            <input type="hidden" name="siswa_id" id="kodeSiswaId">
            <input type="hidden" name="target_url" id="kodeTargetUrl">
            <input type="hidden" name="action_type" id="kodeActionType">
            
            <div class="mb-3">
                <label for="kodeAksesInput" class="form-label" id="kodeInputLabel">Kode Akses Rahasia</label>
                <input type="text" class="form-control text-center fs-4 fw-bold tracking-widest" id="kodeAksesInput" name="kode_akses" required autocomplete="off" style="text-transform: uppercase;">
                <div class="form-text text-center">Minimal 3 huruf (tanpa angka)</div>
            </div>
            
            <div id="kodeErrorMsg" class="text-danger text-center fw-bold d-none mb-3"></div>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" id="btnSubmitKode">
                <span class="spinner-border spinner-border-sm d-none me-1" id="kodeSpinner" role="status" aria-hidden="true"></span>
                Lanjutkan
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchNama');
const cards       = document.querySelectorAll('.siswa-card');
const emptyEl     = document.getElementById('emptySearch');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
        const nama = card.dataset.nama;
        const show = nama.includes(q);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    emptyEl.style.display = visible === 0 ? 'block' : 'none';
});

let kodeModal;
document.addEventListener("DOMContentLoaded", function() {
    kodeModal = new bootstrap.Modal(document.getElementById('kodeModal'));
});

function openKodeModal(element, event) {
    event.preventDefault();
    
    const id = element.dataset.id;
    const hasKode = element.dataset.haskode == '1';
    const targetUrl = element.dataset.url;
    const namaSiswa = element.getAttribute('title');
    
    document.getElementById('kodeSiswaId').value = id;
    document.getElementById('kodeTargetUrl').value = targetUrl;
    
    const label = document.getElementById('kodeModalLabel');
    const inputLabel = document.getElementById('kodeInputLabel');
    const alertBox = document.getElementById('kodeAlert');
    const actionType = document.getElementById('kodeActionType');
    const input = document.getElementById('kodeAksesInput');
    const errorMsg = document.getElementById('kodeErrorMsg');
    
    errorMsg.classList.add('d-none');
    errorMsg.textContent = '';
    input.value = '';
    
    if (!hasKode) {
        label.textContent = "Set Kode Akses Baru";
        inputLabel.innerHTML = `Buat sandi untuk <strong>${namaSiswa}</strong>`;
        alertBox.classList.remove('d-none');
        actionType.value = 'set';
    } else {
        label.textContent = "Masukkan Kode Akses";
        inputLabel.innerHTML = `Masukkan sandi milik <strong>${namaSiswa}</strong>`;
        alertBox.classList.add('d-none');
        actionType.value = 'verify';
    }
    
    kodeModal.show();
    setTimeout(() => input.focus(), 500);
}

document.getElementById('kodeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const input = document.getElementById('kodeAksesInput').value.trim();
    const errorMsg = document.getElementById('kodeErrorMsg');
    
    // Validasi minimal 3 huruf tanpa angka
    if (input.length < 3) {
        errorMsg.textContent = "Kode harus minimal 3 huruf.";
        errorMsg.classList.remove('d-none');
        return;
    }
    if (/\d/.test(input)) {
        errorMsg.textContent = "Kode tidak boleh mengandung angka.";
        errorMsg.classList.remove('d-none');
        return;
    }
    
    const btn = document.getElementById('btnSubmitKode');
    const spinner = document.getElementById('kodeSpinner');
    const actionType = document.getElementById('kodeActionType').value;
    const formData = new FormData(this);
    
    btn.disabled = true;
    spinner.classList.remove('d-none');
    errorMsg.classList.add('d-none');
    
    try {
        const url = "<?= BASE_URL ?>/absen/" + (actionType === 'set' ? 'set_kode' : 'verify_kode');
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            // Berhasil, redirect ke halaman absen/isi
            window.location.href = document.getElementById('kodeTargetUrl').value;
        } else {
            errorMsg.textContent = result.message || "Kode akses salah.";
            errorMsg.classList.remove('d-none');
        }
    } catch (err) {
        errorMsg.textContent = "Terjadi kesalahan jaringan.";
        errorMsg.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
    }
});
</script>
</body>
</html>
