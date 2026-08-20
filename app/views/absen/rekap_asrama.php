<?php
/**
 * absen/rekap_asrama.php
 */
$siswaData = $dataTanggal['siswa'] ?? [];

// Kelompokkan siswa berdasarkan asrama
$grupAsrama = [];
foreach ($siswa as $s) {
    $asrama = trim($s['asrama'] ?? '');
    if (empty($asrama)) {
        $asrama = 'Tanpa Asrama';
    }
    if (!isset($grupAsrama[$asrama])) {
        $grupAsrama[$asrama] = [];
    }
    $grupAsrama[$asrama][] = $s;
}

ksort($grupAsrama);
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-building text-primary me-2"></i>
                Rekap Per Asrama
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> &mdash; 
                Laporan poin siswa dikelompokkan berdasarkan asrama
            </p>
        </div>
    </div>
</div>

<?= Flash::render() ?>

<!-- Pilih Tanggal -->
<div class="card card-main shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-auto">
                <label class="fw-semibold me-2"><i class="bi bi-calendar3 me-1"></i> Pilih Tanggal:</label>
                <input type="date" id="pilihTanggal" class="form-control form-control-sm d-inline-block"
                       style="max-width:200px" value="<?= $tanggal ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-12 col-md">
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($allDates as $d): ?>
                        <a href="?tanggal=<?= $d['tanggal'] ?>"
                           class="badge <?= $d['tanggal'] === $tanggal ? 'bg-primary text-white' : 'bg-secondary-subtle text-secondary' ?> text-decoration-none p-2">
                            <?= date('d/m', strtotime($d['tanggal'])) ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($allDates)): ?>
                        <span class="text-muted small">Belum ada data absen mandiri</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($grupAsrama as $namaAsrama => $listSiswa): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card card-main shadow-sm h-100">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-house me-2"></i> <?= htmlspecialchars($namaAsrama) ?>
                </div>
                <span class="badge bg-primary rounded-pill"><?= count($listSiswa) ?> Siswa</span>
            </div>
            
            <?php 
                $pengurus = $pengurusMap[$namaAsrama] ?? null;
            ?>
            
            <div class="card-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <?php if ($pengurus): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-bold">Pengurus: <?= htmlspecialchars($pengurus['nama_pengurus']) ?></small>
                            <a href="https://wa.me/<?= $pengurus['no_hp'] ?>" target="_blank" class="text-decoration-none text-success small">
                                <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($pengurus['no_hp']) ?>
                            </a>
                        </div>
                        <button class="btn btn-sm btn-success w-100 btn-kirim-asrama" data-asrama="<?= htmlspecialchars($namaAsrama) ?>" data-tanggal="<?= $tanggal ?>">
                            <i class="bi bi-send me-1"></i> Kirim Laporan ke Pengurus
                        </button>
                    <?php else: ?>
                        <?php if ($namaAsrama !== 'Tanpa Asrama'): ?>
                            <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Pengurus belum diatur</small>
                            <a href="<?= BASE_URL ?>/asrama" class="btn btn-sm btn-outline-primary w-100 mt-2">Atur Pengurus</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <ul class="list-group list-group-flush">
                    <?php foreach ($listSiswa as $s): ?>
                    <?php
                        $sudahIsi = isset($siswaData[$s['id']]);
                        $poin = $sudahIsi ? $siswaData[$s['id']]['total_poin'] : '-';
                        $badgeClass = $sudahIsi ? 'bg-success' : 'bg-secondary';
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-medium"><?= htmlspecialchars($s['nama']) ?></span>
                            <?php if (!$sudahIsi): ?>
                                <br><small class="text-danger"><i class="bi bi-x-circle me-1"></i>Belum mengisi</small>
                            <?php endif; ?>
                        </div>
                        <span class="badge <?= $badgeClass ?> rounded-pill" title="Total Poin">
                            <?= $poin ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.getElementById('pilihTanggal').addEventListener('change', function () {
    window.location.href = '<?= BASE_URL ?>/absen/rekap_asrama?tanggal=' + this.value;
});

document.querySelectorAll('.btn-kirim-asrama').forEach(btn => {
    btn.addEventListener('click', async function() {
        const asrama = this.dataset.asrama;
        const tanggal = this.dataset.tanggal;
        
        if (!confirm(`Yakin ingin mengirim laporan ${asrama} untuk tanggal ini ke pengurus?`)) return;
        
        const originalHtml = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
        this.disabled = true;
        
        try {
            const response = await fetch('<?= BASE_URL ?>/absen/kirim_wa_asrama', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nama_asrama: asrama,
                    tanggal: tanggal
                })
            });
            const res = await response.json();
            
            if (res.success) {
                this.innerHTML = '<i class="bi bi-check-lg"></i> Berhasil Terkirim';
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-success');
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.add('btn-success');
                    this.classList.remove('btn-outline-success');
                    this.disabled = false;
                }, 3000);
            } else {
                alert('Gagal: ' + (res.message || 'Kesalahan tidak diketahui'));
                this.innerHTML = originalHtml;
                this.disabled = false;
            }
        } catch (error) {
            alert('Terjadi kesalahan jaringan.');
            this.innerHTML = originalHtml;
            this.disabled = false;
        }
    });
});
</script>
