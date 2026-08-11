<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-primary text-white p-4 text-center rounded-top-4 border-0">
                <i class="bi bi-chat-heart-fill fs-1"></i>
                <h4 class="mb-0 mt-2">Kirim Feedback</h4>
                <p class="mb-0 text-white-50 small mt-1">Beri masukan, saran, atau laporkan masalah</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="<?= BASE_URL ?>/feedback/submit" method="POST">
                    
                    <div class="mb-4">
                        <label for="nama" class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?= htmlspecialchars(Session::get('nama_lengkap') ?? '') ?>" 
                               required placeholder="Masukkan nama Anda">
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Email <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="email@contoh.com">
                        <div class="form-text">Bila Anda ingin kami membalas masukan Anda.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tingkat Kepuasan <span class="text-muted fw-normal">(Opsional)</span></label>
                        <div class="d-flex gap-3 text-center align-items-center">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <div class="form-check p-0">
                                <input type="radio" class="btn-check" name="rating" id="rating<?= $i ?>" value="<?= $i ?>" autocomplete="off">
                                <label class="btn btn-outline-warning rounded-circle d-flex align-items-center justify-content-center" 
                                       style="width: 45px; height: 45px;" for="rating<?= $i ?>">
                                    <h5 class="mb-0 fw-bold"><?= $i ?></h5>
                                </label>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <div class="d-flex justify-content-between mt-1 px-1">
                            <span class="small text-muted">Sangat Buruk</span>
                            <span class="small text-muted">Sangat Baik</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="pesan" class="form-label fw-bold">Pesan / Masukan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="5" required 
                                  placeholder="Tuliskan pengalaman, kendala, atau saran Anda di sini..."></textarea>
                    </div>
                    
                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">
                            <i class="bi bi-send-fill me-2"></i> Kirim Feedback
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-5">
            <a href="<?= BASE_URL ?>" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<style>
/* Animasi bintang/rating */
.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    color: #000;
    transform: scale(1.1);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.btn-outline-warning {
    transition: all 0.2s ease-in-out;
}
.btn-outline-warning:hover {
    transform: scale(1.1);
}
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
