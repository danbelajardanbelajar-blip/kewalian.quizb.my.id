<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
</head>
<body class="absen-page">

<?php
// Nama proper untuk sapaan
$namaProper = ucwords(strtolower($nama));

$totalStep = count($pertanyaan);
if ($totalStep === 0) {
    echo '<div class="container mt-5"><div class="alert alert-warning text-center">Belum ada pertanyaan yang dikonfigurasi.</div></div></body></html>';
    exit;
}
?>

<!-- Progress bar atas -->
<div class="wizard-topbar">
    <a href="<?= BASE_URL ?>/absen?tanggal=<?= $tanggal ?>&wali=<?= $idWali ?>" class="wizard-back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="wizard-progress-wrap flex-grow-1">
        <div class="wizard-progress-bar" id="progressBar" style="width: <?= round(100/$totalStep) ?>%"></div>
    </div>
    <div class="wizard-step-label" id="stepLabel">1 / <?= $totalStep ?></div>
</div>

<!-- Name badge -->
<div class="wizard-name-badge">
    <div class="wizard-avatar">
        <?= mb_substr($namaProper, 0, 1) ?>
    </div>
    <div>
        <div class="wizard-name-text"><?= htmlspecialchars($namaProper) ?></div>
        <div class="wizard-date-text"><?= date('d F Y', strtotime($tanggal)) ?></div>
    </div>
    <?php if ($isEdit ?? false): ?>
        <span class="badge bg-warning text-dark ms-auto">Edit</span>
    <?php endif; ?>
</div>

<!-- Form Wizard -->
<form action="<?= BASE_URL ?>/absen/simpan" method="POST" id="formAbsen">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="nama" value="<?= htmlspecialchars($nama) ?>">
    <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
    <input type="hidden" name="idWali" value="<?= $idWali ?>">

    <div class="wizard-container" id="wizardContainer">

        <?php foreach ($pertanyaan as $stepIdx => $p): ?>
            <?php
            $isFirst = $stepIdx === 0;
            $isLast  = $stepIdx === $totalStep - 1;

            $pId = $p['id'];
            $ansData = $existing['jawaban'][$pId] ?? null;
            $rawAnsValue = $ansData['jawaban'] ?? '';
            $ansKet = $ansData['keterangan'] ?? '';
            
            $opsi = json_decode($p['opsi'], true);
            
            $pilihanGandaList = [];
            $angkaSatuan = '';
            
            $ansValue = $rawAnsValue;
            $ansAngka = '1';

            if ($p['tipe'] === 'ganda_dan_angka') {
                $pilihanGandaList = $opsi['pilihan'] ?? [];
                $angkaSatuan = $opsi['angka']['satuan'] ?? '';
                
                // parse value:angka
                $parts = explode(':', $rawAnsValue);
                $ansValue = $parts[0] ?? '';
                $ansAngka = $parts[1] ?? '1';
            } else if ($p['tipe'] === 'pilihan_ganda') {
                $pilihanGandaList = $opsi;
            }

            // Acak urutan jawaban jika diset
            if (!empty($settings['acak_jawaban']) && !empty($pilihanGandaList)) {
                shuffle($pilihanGandaList);
            }

            // Icon dan emoji dinamis sederhana berdasarkan judul
            $emoji = '📝';
            if (stripos($p['judul'], 'sekolah') !== false) $emoji = '🏫';
            if (stripos($p['judul'], 'miftah') !== false) $emoji = '📖';
            if (stripos($p['judul'], 'diniyah') !== false) $emoji = '🌙';
            if (stripos($p['judul'], 'subuh') !== false || stripos($p['judul'], 'pagi') !== false) $emoji = '🌅';
            if (stripos($p['judul'], 'quran') !== false) $emoji = '📿';
            if (stripos($p['judul'], 'dluha') !== false) $emoji = '🕌';
            if (stripos($p['judul'], 'belajar') !== false || stripos($p['judul'], 'buku') !== false) $emoji = '📚';
            if (stripos($p['judul'], 'maaf') !== false) $emoji = '💖';
            if (stripos($p['judul'], 'doa') !== false) $emoji = '🤲';
            if (stripos($p['judul'], 'shadaqah') !== false || stripos($p['judul'], 'bantu') !== false) $emoji = '🤝';
            ?>

            <div class="wizard-step <?= $isFirst ? 'wizard-step--active' : '' ?>"
                 data-step="<?= $stepIdx + 1 ?>" id="step<?= $stepIdx + 1 ?>">

                <!-- Pertanyaan card -->
                <div class="question-card">
                    <div class="question-emoji"><?= $emoji ?></div>
                    <div class="question-number">Pertanyaan <?= $stepIdx + 1 ?> dari <?= $totalStep ?></div>
                    <h2 class="question-text"><?= htmlspecialchars(str_replace('{{nama}}', $namaProper, $p['judul'])) ?></h2>

                    <?php if ($p['tipe'] === 'pilihan_ganda' || $p['tipe'] === 'ganda_dan_angka'): ?>
                        <div class="option-grid" data-group="<?= $pId ?>">
                            <?php foreach ($pilihanGandaList as $idx => $op): ?>
                                <?php
                                $checked = ($ansValue === $op['value']) ? 'checked' : '';
                                // Tentukan warna/icon berdasarkan text
                                $lblLower = strtolower($op['label']);
                                $color = 'primary';
                                $icon = 'bi-circle';
                                if (in_array($lblLower, ['hadir', 'iya', 'ikut', 'sudah'])) {
                                    $color = 'success'; $icon = 'bi-check-circle-fill';
                                } elseif (in_array($lblLower, ['absen', 'tidak', 'belum', 'alpha'])) {
                                    $color = 'danger'; $icon = 'bi-x-circle-fill';
                                } elseif (in_array($lblLower, ['sakit', 'udzur haid'])) {
                                    $color = 'warning'; $icon = 'bi-thermometer';
                                } elseif (in_array($lblLower, ['izin', 'telat'])) {
                                    $color = 'info'; $icon = 'bi-card-text';
                                }
                                ?>
                                <label class="option-btn option-btn--<?= $color ?> <?= $checked ? 'selected' : '' ?>"
                                       for="p_<?= $pId ?>_<?= $idx ?>">
                                    <input type="radio"
                                           id="p_<?= $pId ?>_<?= $idx ?>"
                                           name="jawaban[<?= $pId ?>]"
                                           value="<?= htmlspecialchars($op['value']) ?>"
                                           <?= $checked ?>
                                           class="d-none option-radio"
                                           data-field="<?= $pId ?>"
                                           data-reqket="<?= !empty($op['require_ket']) ? '1' : '0' ?>"
                                           data-reqangka="<?= !empty($op['require_angka']) ? '1' : '0' ?>">
                                    <i class="bi <?= $icon ?> option-icon"></i>
                                    <span><?= htmlspecialchars($op['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Input angka bersyarat -->
                        <div class="quran-jumlah-wrap mt-3" id="angka_wrap_<?= $pId ?>" style="display:none">
                            <label class="izin-ket-label text-center d-block mb-2">
                                <?= $angkaSatuan !== '' ? htmlspecialchars($angkaSatuan) : 'Jumlah' ?>
                            </label>
                            <div class="quran-counter">
                                <button type="button" class="quran-counter-btn btnMinAngka" data-target="angka_input_<?= $pId ?>">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number"
                                       id="angka_input_<?= $pId ?>"
                                       name="jawaban_angka[<?= $pId ?>]"
                                       class="quran-counter-input"
                                       value="<?= htmlspecialchars($ansAngka) ?>"
                                       min="1" max="999">
                                <button type="button" class="quran-counter-btn btnPlusAngka" data-target="angka_input_<?= $pId ?>">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Input keterangan jika diwajibkan -->
                        <div class="izin-ket-wrap mt-3" id="ket_wrap_<?= $pId ?>"
                             style="display:none">
                            <label class="izin-ket-label">
                                <i class="bi bi-chat-text me-1"></i>
                                Tuliskan keterangan:
                            </label>
                            <textarea name="keterangan[<?= $pId ?>]"
                                      id="ket_input_<?= $pId ?>"
                                      class="izin-ket-input"
                                      rows="2"
                                      placeholder="Ketik keterangan di sini..."><?= htmlspecialchars($ansKet) ?></textarea>
                        </div>

                    <?php elseif ($p['tipe'] === 'angka'): ?>
                        <div class="quran-jumlah-wrap mt-3" style="display:block;">
                            <label class="izin-ket-label text-center d-block mb-2">
                                <?= !empty($opsi['satuan']) ? htmlspecialchars($opsi['satuan']) : 'Jumlah' ?>
                            </label>
                            <div class="quran-counter">
                                <button type="button" class="quran-counter-btn btnMinAngka" data-target="angka_<?= $pId ?>">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number"
                                       id="angka_<?= $pId ?>"
                                       name="jawaban[<?= $pId ?>]"
                                       class="quran-counter-input"
                                       value="<?= htmlspecialchars($ansValue !== '' ? $ansValue : '0') ?>"
                                       min="0" max="999">
                                <button type="button" class="quran-counter-btn btnPlusAngka" data-target="angka_<?= $pId ?>">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <?php if (!empty($opsi['require_ket'])): ?>
                            <div class="izin-ket-wrap mt-3">
                                <label class="izin-ket-label">Keterangan:</label>
                                <textarea name="keterangan[<?= $pId ?>]" class="izin-ket-input" rows="2" required><?= htmlspecialchars($ansKet) ?></textarea>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Navigasi step -->
                <div class="wizard-nav">
                    <?php if (!$isFirst): ?>
                        <button type="button" class="btn-wizard-prev" data-step="<?= $stepIdx + 1 ?>">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <?php if (!$isLast): ?>
                        <button type="button" class="btn-wizard-next d-none" data-step="<?= $stepIdx + 1 ?>" id="btnNext_<?= $pId ?>">
                            Lanjut <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn-wizard-submit" id="btnSubmit">
                            <i class="bi bi-check-lg me-1"></i> Selesai &amp; Simpan
                        </button>
                    <?php endif; ?>
                </div>

            </div><!-- /wizard-step -->
        <?php endforeach; ?>

    </div><!-- /wizardContainer -->
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    const TOTAL = <?= $totalStep ?>;
    let current = 1;

    // ── Update progress bar & label ─────────────────────────
    function updateProgress(step) {
        const pct = Math.round((step / TOTAL) * 100);
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('stepLabel').textContent = step + ' / ' + TOTAL;
    }

    // ── Show step ───────────────────────────────────────────
    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => {
            el.classList.remove('wizard-step--active', 'wizard-step--exit-left', 'wizard-step--enter-right');
        });
        const el = document.getElementById('step' + step);
        if (el) {
            el.classList.add('wizard-step--active');
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        updateProgress(step);
        current = step;
    }

    // ── Validasi Step ───────────────────────────────────────
    function validateStep(stepIdx) {
        const stepEl = document.getElementById('step' + stepIdx);
        if (!stepEl) return true;

        // Cek radio button di step ini
        const radios = stepEl.querySelectorAll('.option-radio');
        if (radios.length > 0) {
            let selected = false;
            let isValid = false;
            radios.forEach(r => {
                if (r.checked) {
                    selected = true;
                    isValid = true;
                    // Cek textarea keterangan jika required
                    if (r.getAttribute('data-reqket') === '1') {
                        const field = r.dataset.field;
                        const ket = document.getElementById('ket_input_' + field);
                        if (ket && ket.value.trim() === '') {
                            isValid = false;
                            ket.classList.add('is-invalid');
                        } else if (ket) {
                            ket.classList.remove('is-invalid');
                        }
                    }
                }
            });
            if (!selected) {
                return false; // Belum pilih radio
            }
            return isValid;
        }
        return true;
    }

    // ── Tombol Lanjut ───────────────────────────────────────
    document.querySelectorAll('.btn-wizard-next').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (validateStep(current)) {
                if (step < TOTAL) showStep(step + 1);
            } else {
                alert('Silakan lengkapi jawaban pada pertanyaan ini terlebih dahulu.');
            }
        });
    });

    // ── Tombol Kembali ──────────────────────────────────────
    document.querySelectorAll('.btn-wizard-prev').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (step > 1) showStep(step - 1);
        });
    });

    // ── Pilihan radio → visual selected & Auto-next & Ket ───
    document.querySelectorAll('.option-radio').forEach(radio => {
        
        // Initial setup for existing value
        if (radio.checked) {
            const reqKet = radio.getAttribute('data-reqket') === '1';
            const reqAngka = radio.getAttribute('data-reqangka') === '1';
            const field = radio.dataset.field;
            if (field) {
                const ketWrap = document.getElementById('ket_wrap_' + field);
                const ketInput = document.getElementById('ket_input_' + field);
                if (ketWrap && ketInput) {
                    ketWrap.style.display = reqKet ? '' : 'none';
                    ketInput.required = reqKet;
                }
                
                const angkaWrap = document.getElementById('angka_wrap_' + field);
                if (angkaWrap) {
                    angkaWrap.style.display = reqAngka ? '' : 'none';
                }
                
                const btnNext = document.getElementById('btnNext_' + field);
                if (btnNext) {
                    btnNext.classList.remove('d-none');
                }
            }
        }

        radio.addEventListener('change', function () {
            const group = this.closest('.option-grid');
            if (group) group.querySelectorAll('.option-btn').forEach(lbl => lbl.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            let autoNext = true;
            const reqKet = this.getAttribute('data-reqket') === '1';
            const reqAngka = this.getAttribute('data-reqangka') === '1';
            const field = this.dataset.field;

            if (field) {
                const ketWrap = document.getElementById('ket_wrap_' + field);
                const ketInput = document.getElementById('ket_input_' + field);
                if (ketWrap && ketInput) {
                    ketWrap.style.display = reqKet ? '' : 'none';
                    ketInput.required = reqKet;
                    
                    if (reqKet) {
                        ketInput.focus();
                        autoNext = false; // Jangan auto-next karena harus ketik keterangan
                    } else if (ketWrap.style.display === 'none') {
                        // Jangan clear input keterangan jika tidak required, biarkan saja
                    }
                }
                
                const angkaWrap = document.getElementById('angka_wrap_' + field);
                if (angkaWrap) {
                    angkaWrap.style.display = reqAngka ? '' : 'none';
                    if (reqAngka) autoNext = false;
                }
            }
            
            const btnNext = document.getElementById('btnNext_' + field);

            // Auto-next dengan delay agar animasi klik terlihat
            if (autoNext && current < TOTAL) {
                if (btnNext) btnNext.classList.add('d-none'); // sembunyikan kembali kalau misal tadi ganti opsi
                setTimeout(() => showStep(current + 1), 300);
            } else if (!autoNext) {
                // Jika auto-next mati (karena butuh isi keterangan/angka), munculkan tombol lanjut
                if (btnNext) btnNext.classList.remove('d-none');
            }
        });
    });

    // ── Counter Angka Dinamis ───────────────────────────────
    document.querySelectorAll('.btnMinAngka').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input && parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    document.querySelectorAll('.btnPlusAngka').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                input.value = parseInt(input.value || 0) + 1;
            }
        });
    });

    // ── Submit loading ──────────────────────────────────────
    document.getElementById('formAbsen').addEventListener('submit', function (e) {
        if (!validateStep(TOTAL)) {
            e.preventDefault();
            alert('Silakan lengkapi jawaban pada pertanyaan terakhir ini terlebih dahulu.');
            return false;
        }
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        btn.disabled = true;
    });

    // ── Keyboard navigation ─────────────────────────────────
    document.addEventListener('keydown', function (e) {
        // Hanya jika tidak sedang di dalam textarea/input text
        if (e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT' || e.target.type === 'radio' || e.target.type === 'checkbox') {
            if (e.key === 'ArrowRight' && current < TOTAL) {
                if (validateStep(current)) showStep(current + 1);
            }
            if (e.key === 'ArrowLeft'  && current > 1) {
                showStep(current - 1);
            }
        }
    });

})();
</script>
</body>
</html>
