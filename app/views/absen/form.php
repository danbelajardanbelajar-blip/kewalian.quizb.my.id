<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
</head>
<body class="absen-page">

<!-- Header Khusus Absen -->
<div class="absen-hero" style="padding: 2rem 1rem 3rem;">
    <div class="absen-hero-content">
        <a href="<?= BASE_URL ?>/absen?tanggal=<?= $tanggal ?>&wali=<?= urlencode($usernameWali) ?>" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div class="absen-logo mt-3">
            <i class="bi bi-person-badge"></i>
        </div>
        <h1 class="absen-hero-title">Absen Harian</h1>
        <p class="absen-hero-sub">
            Halo kulo <strong><?= htmlspecialchars($nama) ?></strong>,<br>
            Mari isi kegiatan harian untuk tanggal <br>
            <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>
        </p>
    </div>
</div>

<div class="absen-container" style="margin-top: -2rem;">
    <?= Flash::render() ?>

    <form action="<?= BASE_URL ?>/absen/simpan" method="POST" id="formAbsen">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="nama" value="<?= htmlspecialchars($nama) ?>">
        <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
        <input type="hidden" name="usernameWali" value="<?= htmlspecialchars($usernameWali) ?>">

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                
                <?php if (empty($pertanyaan)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                        Wali kelas belum mengatur pertanyaan untuk absen ini.
                    </div>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    foreach ($pertanyaan as $p): 
                        $pId = $p['id'];
                        $ansData = $existing['jawaban'][$pId] ?? null;
                        $ansValue = $ansData['jawaban'] ?? '';
                        $ansKet = $ansData['keterangan'] ?? '';
                        $opsi = json_decode($p['opsi'], true);
                    ?>
                        <div class="form-group mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold fs-5 text-dark mb-3">
                                <?= $no++ ?>. <?= htmlspecialchars($p['judul']) ?>
                            </label>

                            <?php if ($p['tipe'] === 'pilihan_ganda'): ?>
                                <div class="row g-2">
                                    <?php foreach ($opsi as $idx => $op): ?>
                                        <div class="col-6 col-sm-4">
                                            <input type="radio" class="btn-check radio-pg" 
                                                   name="jawaban[<?= $pId ?>]" 
                                                   id="p_<?= $pId ?>_<?= $idx ?>" 
                                                   value="<?= htmlspecialchars($op['value']) ?>"
                                                   data-reqket="<?= !empty($op['require_ket']) ? '1' : '0' ?>"
                                                   data-pid="<?= $pId ?>"
                                                   autocomplete="off" required
                                                   <?= $ansValue === $op['value'] ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-primary w-100 py-2 btn-radio-custom" for="p_<?= $pId ?>_<?= $idx ?>">
                                                <?= htmlspecialchars($op['label']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3 ket-container" id="ket_container_<?= $pId ?>" style="display: none;">
                                    <input type="text" name="keterangan[<?= $pId ?>]" id="ket_input_<?= $pId ?>" 
                                           class="form-control bg-light" 
                                           placeholder="Tuliskan keterangan..." 
                                           value="<?= htmlspecialchars($ansKet) ?>">
                                </div>

                            <?php elseif ($p['tipe'] === 'angka'): ?>
                                <div class="input-group mb-2">
                                    <input type="number" name="jawaban[<?= $pId ?>]" 
                                           class="form-control form-control-lg text-center" 
                                           placeholder="0" min="0" required
                                           value="<?= htmlspecialchars($ansValue) ?>">
                                    <?php if(!empty($opsi['satuan'])): ?>
                                        <span class="input-group-text"><?= htmlspecialchars($opsi['satuan']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($opsi['require_ket'])): ?>
                                    <input type="text" name="keterangan[<?= $pId ?>]" 
                                           class="form-control bg-light mt-2" 
                                           placeholder="Keterangan tambahan..." 
                                           value="<?= htmlspecialchars($ansKet) ?>" required>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm" style="font-size: 1.1rem;">
                            <i class="bi bi-send-fill me-2"></i> Simpan Absen Kulo
                        </button>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic to show/hide Keterangan input based on 'require_ket'
    const radios = document.querySelectorAll('.radio-pg');
    
    function updateKetVisibility(radio) {
        const pid = radio.getAttribute('data-pid');
        const reqKet = radio.getAttribute('data-reqket') === '1';
        const container = document.getElementById('ket_container_' + pid);
        const input = document.getElementById('ket_input_' + pid);
        
        if (radio.checked) {
            if (reqKet) {
                container.style.display = 'block';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            // update all radios in same group
            const pid = this.getAttribute('data-pid');
            const group = document.querySelectorAll('.radio-pg[data-pid="'+pid+'"]');
            group.forEach(r => {
                if(r.checked) updateKetVisibility(r);
            });
        });
        
        // Init on load
        if (radio.checked) {
            updateKetVisibility(radio);
        }
    });
});
</script>

</body>
</html>
