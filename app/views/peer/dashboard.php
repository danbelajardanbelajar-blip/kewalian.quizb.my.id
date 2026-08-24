<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Peta Karakter Kelas</h1>
            <form action="<?= BASE_URL ?>/peer/dashboard" method="GET" class="d-flex align-items-center gap-2">
                <label class="text-muted fw-semibold small text-nowrap">Filter Waktu:</label>
                <select name="rentang" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 150px;">
                    <option value="semua" <?= $rentang === 'semua' ? 'selected' : '' ?>>Semua Waktu</option>
                    <option value="bulan_ini" <?= $rentang === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                    <option value="minggu_ini" <?= $rentang === 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                </select>
            </form>
        </div>
    </div>
    
    <div class="alert alert-light border shadow-sm rounded-4 mb-4">
        <i class="bi bi-info-circle-fill text-info me-2"></i>
        <strong>Informasi:</strong> Data di bawah ini adalah hasil akumulasi suara (vote) antar teman. Siswa yang mem-vote 100% anonim dan rahasia. Digunakan hanya untuk memetakan indikasi karakter dan deteksi dini.
    </div>

    <?php if (empty($pertanyaan)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted display-1"></i>
            <h4 class="mt-3 text-muted">Belum ada pertanyaan Peer Review aktif.</h4>
            <p class="text-muted">Silakan tambahkan soal di menu Bank Soal Peer.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($pertanyaan as $p): ?>
                <?php 
                    $isPositif = $p['sifat'] === 'positif'; 
                    $theme = $isPositif ? 'success' : 'danger';
                    $icon = $isPositif ? 'bi-star-fill' : 'bi-exclamation-triangle-fill';
                    $data = $leaderboard[$p['id']] ?? [];
                ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 border-top border-<?= $theme ?> border-4">
                        <div class="card-header bg-white border-0 pt-4 pb-2">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi <?= $icon ?> text-<?= $theme ?> fs-5 mt-1"></i>
                                <h6 class="card-title fw-bold lh-base mb-0"><?= htmlspecialchars($p['pertanyaan']) ?></h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (empty($data)): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-person-slash fs-3 d-block mb-2"></i>
                                    Belum ada data vote terkumpul.
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush mt-2">
                                    <?php $rank = 1; foreach ($data as $lb): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                            <div class="d-flex align-items-center gap-2 text-truncate">
                                                <span class="badge bg-light text-dark border rounded-circle" style="width:25px;height:25px;display:flex;align-items:center;justify-content:center;"><?= $rank++ ?></span>
                                                <span class="fw-medium text-truncate" title="<?= htmlspecialchars($lb['nama']) ?>"><?= htmlspecialchars($lb['nama']) ?></span>
                                            </div>
                                            <span class="badge bg-<?= $theme ?> bg-opacity-10 text-<?= $theme ?> border border-<?= $theme ?> rounded-pill">
                                                <?= $lb['total_vote'] ?> suara
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
