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

// Definisi 7 pertanyaan
$pertanyaan = [
    [
        'id'     => 'sekolah',
        'label'  => 'Apakah kemarin ananda ' . htmlspecialchars($namaProper) . ' hadir sekolah?',
        'icon'   => 'bi-building',
        'emoji'  => '🏫',
        'type'   => 'kehadiran',
        'options'=> [
            ['value'=>'hadir',  'label'=>'Hadir',  'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'absen',  'label'=>'Absen',  'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
            ['value'=>'sakit',  'label'=>'Sakit',  'icon'=>'bi-thermometer',       'color'=>'warning'],
            ['value'=>'izin',   'label'=>'Izin',   'icon'=>'bi-card-text',         'color'=>'info'],
        ],
    ],
    [
        'id'     => 'almiftah',
        'label'  => 'Apakah kemarin siang ananda ' . htmlspecialchars($namaProper) . ' hadir Al-Miftah?',
        'icon'   => 'bi-book-half',
        'emoji'  => '📖',
        'type'   => 'kehadiran',
        'options'=> [
            ['value'=>'hadir',  'label'=>'Hadir',  'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'absen',  'label'=>'Absen',  'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
            ['value'=>'sakit',  'label'=>'Sakit',  'icon'=>'bi-thermometer',       'color'=>'warning'],
            ['value'=>'izin',   'label'=>'Izin',   'icon'=>'bi-card-text',         'color'=>'info'],
        ],
    ],
    [
        'id'     => 'diniyah',
        'label'  => 'Apakah tadi malam ananda ' . htmlspecialchars($namaProper) . ' hadir Diniyah?',
        'icon'   => 'bi-moon-stars',
        'emoji'  => '🌙',
        'type'   => 'kehadiran',
        'options'=> [
            ['value'=>'hadir',  'label'=>'Hadir',  'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'absen',  'label'=>'Absen',  'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
            ['value'=>'sakit',  'label'=>'Sakit',  'icon'=>'bi-thermometer',       'color'=>'warning'],
            ['value'=>'izin',   'label'=>'Izin',   'icon'=>'bi-card-text',         'color'=>'info'],
        ],
    ],
    [
        'id'     => 'subuh',
        'label'  => 'Apakah kemarin pagi (bakda shubuh) ananda ' . htmlspecialchars($namaProper) . ' hadir Ngaji Pagi?',
        'icon'   => 'bi-sunrise',
        'emoji'  => '🌅',
        'type'   => 'kehadiran',
        'options'=> [
            ['value'=>'hadir',  'label'=>'Hadir',  'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'absen',  'label'=>'Absen',  'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
            ['value'=>'sakit',  'label'=>'Sakit',  'icon'=>'bi-thermometer',       'color'=>'warning'],
            ['value'=>'izin',   'label'=>'Izin',   'icon'=>'bi-card-text',         'color'=>'info'],
        ],
    ],
    [
        'id'     => 'quran',
        'label'  => 'Apakah sejak kemarin hingga shubuh ini ananda ' . htmlspecialchars($namaProper) . ' membaca Al-Qur\'an secara mandiri?',
        'icon'   => 'bi-journal-bookmark',
        'emoji'  => '📿',
        'type'   => 'quran',
    ],
    [
        'id'     => 'baca_buku',
        'label'  => 'Apakah kemarin ananda ' . htmlspecialchars($namaProper) . ' sudah membaca buku secara mandiri?',
        'icon'   => 'bi-book',
        'emoji'  => '📖',
        'type'   => 'buku',
    ],
    [
        'id'     => 'dluha',
        'label'  => 'Apakah kemarin pagi ananda ' . htmlspecialchars($namaProper) . ' ikut Shalat Dluha di madrasah?',
        'icon'   => 'bi-brightness-high',
        'emoji'  => '🕌',
        'type'   => 'dluha',
        'options'=> [
            ['value'=>'ikut',        'label'=>'Ikut',       'icon'=>'bi-check-circle-fill',   'color'=>'success'],
            ['value'=>'udzur_haid',  'label'=>'Udzur Haid', 'icon'=>'bi-shield-check',         'color'=>'warning'],
            ['value'=>'tidak_ikut',  'label'=>'Tidak Ikut', 'icon'=>'bi-x-circle-fill',        'color'=>'danger'],
        ],
    ],
    [
        'id'     => 'belajar',
        'label'  => 'Apakah tadi malam ananda ' . htmlspecialchars($namaProper) . ' belajar di kamar?',
        'icon'   => 'bi-lamp',
        'emoji'  => '📚',
        'type'   => 'belajar',
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya',   'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Belum', 'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
    [
        'id'     => 'memaafkan',
        'label'  => 'Apakah tadi malam ananda ' . htmlspecialchars($namaProper) . ' sudah memaafkan semua orang?',
        'icon'   => 'bi-heart-fill',
        'emoji'  => '💖',
        'type'   => 'belajar', // use 'belajar' type for simple yes/no UI
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya',   'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Belum', 'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
    [
        'id'     => 'mendoakan_muslimin',
        'label'  => 'Apakah tadi malam ananda ' . htmlspecialchars($namaProper) . ' sudah mendoakan semua kaum muslimin?',
        'icon'   => 'bi-people-fill',
        'emoji'  => '🤲',
        'type'   => 'belajar',
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya',   'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Belum', 'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
    [
        'id'     => 'mendoakan_ortu',
        'label'  => 'Apakah kemarin ananda ' . htmlspecialchars($namaProper) . ' sudah mendoakan kedua orang tua?',
        'icon'   => 'bi-person-hearts',
        'emoji'  => '👨‍👩‍👦',
        'type'   => 'belajar',
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya',   'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Belum', 'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
    [
        'id'     => 'shadaqah',
        'label'  => 'Apakah kemarin ananda ' . htmlspecialchars($namaProper) . ' sudah membantu teman atau bersedekah?',
        'icon'   => 'bi-gift-fill',
        'emoji'  => '🤝',
        'type'   => 'belajar',
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya',   'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Belum', 'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
];

$totalStep = count($pertanyaan);
?>

<!-- Progress bar atas -->
<div class="wizard-topbar">
    <a href="<?= BASE_URL ?>/absen?tanggal=<?= $tanggal ?>" class="wizard-back-btn">
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
    <?php if ($isEdit): ?>
        <span class="badge bg-warning text-dark ms-auto">Edit</span>
    <?php endif; ?>
</div>

<!-- Form Wizard -->
<form action="<?= BASE_URL ?>/absen/simpan" method="POST" id="formAbsen">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="nama" value="<?= htmlspecialchars($nama) ?>">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">

    <div class="wizard-container" id="wizardContainer">

        <?php foreach ($pertanyaan as $stepIdx => $q): ?>
            <?php
            $isFirst = $stepIdx === 0;
            $isLast  = $stepIdx === $totalStep - 1;

            // Ambil nilai existing
            $existVal = $existing[$q['id']] ?? [];
            ?>

            <div class="wizard-step <?= $isFirst ? 'wizard-step--active' : '' ?>"
                 data-step="<?= $stepIdx + 1 ?>" id="step<?= $stepIdx + 1 ?>">

                <!-- Pertanyaan card -->
                <div class="question-card">
                    <div class="question-emoji"><?= $q['emoji'] ?></div>
                    <div class="question-number">Pertanyaan <?= $stepIdx + 1 ?> dari <?= $totalStep ?></div>
                    <h2 class="question-text"><?= htmlspecialchars($q['label']) ?></h2>

                    <?php if ($q['type'] === 'kehadiran'): ?>
                        <!-- Pilihan: Hadir/Absen/Sakit/Izin -->
                        <div class="option-grid" data-group="<?= $q['id'] ?>">
                            <?php foreach ($q['options'] as $opt): ?>
                                <?php
                                $existStatus = $existVal['status'] ?? '';
                                $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                ?>
                                <label class="option-btn option-btn--<?= $opt['color'] ?> <?= $checked ? 'selected' : '' ?>"
                                       for="<?= $q['id'] ?>_<?= $opt['value'] ?>">
                                    <input type="radio"
                                           id="<?= $q['id'] ?>_<?= $opt['value'] ?>"
                                           name="<?= $q['id'] ?>"
                                           value="<?= $opt['value'] ?>"
                                           <?= $checked ?>
                                           class="d-none option-radio"
                                           data-field="<?= $q['id'] ?>">
                                    <i class="bi <?= $opt['icon'] ?> option-icon"></i>
                                    <span><?= $opt['label'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Input keterangan Izin -->
                        <div class="izin-ket-wrap" id="ket_<?= $q['id'] ?>"
                             style="<?= ($existVal['status'] ?? '') === 'izin' ? '' : 'display:none' ?>">
                            <label class="izin-ket-label">
                                <i class="bi bi-chat-text me-1"></i>
                                Tuliskan keterangan izinmu:
                            </label>
                            <textarea name="<?= $q['id'] ?>_ket"
                                      class="izin-ket-input"
                                      rows="2"
                                      placeholder="Contoh: ada acara keluarga, ijin dokter, dll..."><?= htmlspecialchars($existVal['ket'] ?? '') ?></textarea>
                        </div>

                    <?php elseif ($q['type'] === 'quran'): ?>
                        <!-- Pertanyaan Al-Qur'an -->
                        <?php
                        $qType   = $existVal['type']   ?? '';
                        $qJumlah = $existVal['jumlah'] ?? 1;
                        $qSudah  = ($qType === 'tidak' || $qType === '') ? 'belum' : 'iya';
                        ?>
                        <div class="option-grid option-grid--2 mb-3">
                            <label class="option-btn option-btn--success option-btn--big quran-main-btn <?= $qSudah === 'iya' && $qType !== '' ? 'selected' : '' ?>"
                                   for="quran_iya">
                                <input type="radio" id="quran_iya" name="quran_sudah"
                                       value="iya" class="d-none quran-main-radio" <?= $qSudah === 'iya' && $qType !== '' ? 'checked' : '' ?>>
                                <i class="bi bi-check-circle-fill option-icon"></i>
                                <span>Iya, Sudah</span>
                            </label>
                            <label class="option-btn option-btn--danger option-btn--big quran-main-btn <?= $qSudah === 'belum' && $qType !== '' ? 'selected' : '' ?>"
                                   for="quran_belum">
                                <input type="radio" id="quran_belum" name="quran_sudah"
                                       value="belum" class="d-none quran-main-radio" <?= $qSudah === 'belum' && $qType !== '' ? 'checked' : '' ?>>
                                <i class="bi bi-x-circle-fill option-icon"></i>
                                <span>Belum Baca</span>
                            </label>
                        </div>

                        <!-- Pilihan Juz/Halaman, disembunyikan jika Belum -->
                        <div id="quranDetailWrap" style="<?= $qSudah === 'iya' && $qType !== '' ? '' : 'display:none' ?>">
                            <label class="izin-ket-label mb-2 d-block text-center">Berapa banyak yang dibaca?</label>
                            <div class="quran-options">
                                <label class="quran-type-btn <?= $qType === 'halaman' ? 'selected' : '' ?>"
                                       for="quran_halaman">
                                    <input type="radio" id="quran_halaman" name="quran_type"
                                           value="halaman" class="d-none quran-sub-radio"
                                           <?= $qType === 'halaman' ? 'checked' : '' ?>>
                                    <span class="quran-icon">📄</span>
                                    <span>Halaman</span>
                                </label>
                                <label class="quran-type-btn <?= $qType === 'juz' ? 'selected' : '' ?>"
                                       for="quran_juz">
                                    <input type="radio" id="quran_juz" name="quran_type"
                                           value="juz" class="d-none quran-sub-radio"
                                           <?= $qType === 'juz' ? 'checked' : '' ?>>
                                    <span class="quran-icon">📖</span>
                                    <span>Juz</span>
                                </label>
                                <label class="quran-type-btn <?= $qType === 'setengah_juz' ? 'selected' : '' ?>"
                                       for="quran_setengah">
                                    <input type="radio" id="quran_setengah" name="quran_type"
                                           value="setengah_juz" class="d-none quran-sub-radio"
                                           <?= $qType === 'setengah_juz' ? 'checked' : '' ?>>
                                    <span class="quran-icon">📑</span>
                                    <span>½ Juz</span>
                                </label>
                                <!-- Radio "tidak" tersembunyi agar form bisa dikirim -->
                                <input type="radio" id="quran_tidak" name="quran_type" value="tidak" class="d-none quran-sub-radio" <?= $qType === 'tidak' ? 'checked' : '' ?>>
                            </div>
                            
                            <div class="quran-jumlah-wrap mt-3" id="quranJumlahWrap"
                                 style="<?= ($qType === 'setengah_juz' || $qType === 'tidak' || $qType === '') ? 'display:none' : '' ?>">
                                <label class="izin-ket-label" id="quranJumlahLabel">Jumlah <?= $qType === 'juz' ? 'juz' : 'halaman' ?>:</label>
                                <div class="quran-counter">
                                    <button type="button" class="quran-counter-btn" id="btnMin">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <input type="number"
                                           id="quranJumlah"
                                           name="quran_jumlah"
                                           class="quran-counter-input"
                                           value="<?= $qJumlah ?>"
                                           min="1" max="999">
                                    <button type="button" class="quran-counter-btn" id="btnPlus">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($q['type'] === 'buku'): ?>
                        <!-- Pertanyaan Baca Buku -->
                        <?php
                        $bJumlah = $existVal['jumlah'] ?? 1;
                        $bSudah  = ($existVal['status'] ?? '') === 'iya' ? 'iya' : 'belum';
                        if (empty($existVal)) $bSudah = ''; // state awal kosong
                        ?>
                        <div class="option-grid option-grid--2 mb-3">
                            <label class="option-btn option-btn--success option-btn--big buku-main-btn <?= $bSudah === 'iya' ? 'selected' : '' ?>"
                                   for="buku_iya">
                                <input type="radio" id="buku_iya" name="buku_sudah"
                                       value="iya" class="d-none buku-main-radio" <?= $bSudah === 'iya' ? 'checked' : '' ?>>
                                <i class="bi bi-check-circle-fill option-icon"></i>
                                <span>Iya, Sudah</span>
                            </label>
                            <label class="option-btn option-btn--danger option-btn--big buku-main-btn <?= $bSudah === 'belum' ? 'selected' : '' ?>"
                                   for="buku_belum">
                                <input type="radio" id="buku_belum" name="buku_sudah"
                                       value="belum" class="d-none buku-main-radio" <?= $bSudah === 'belum' ? 'checked' : '' ?>>
                                <i class="bi bi-x-circle-fill option-icon"></i>
                                <span>Belum Baca</span>
                            </label>
                        </div>

                        <!-- Input Halaman, disembunyikan jika Belum -->
                        <div id="bukuDetailWrap" style="<?= $bSudah === 'iya' ? '' : 'display:none' ?>">
                            <div class="quran-jumlah-wrap mt-3" id="bukuJumlahWrap">
                                <label class="izin-ket-label text-center d-block mb-2">Berapa halaman yang dibaca?</label>
                                <div class="quran-counter">
                                    <button type="button" class="quran-counter-btn" id="btnMinBuku">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <input type="number"
                                           id="bukuJumlah"
                                           name="buku_jumlah"
                                           class="quran-counter-input"
                                           value="<?= $bJumlah ?>"
                                           min="1" max="999">
                                    <button type="button" class="quran-counter-btn" id="btnPlusBuku">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($q['type'] === 'dluha'): ?>
                        <!-- Shalat Dluha -->
                        <div class="option-grid option-grid--3">
                            <?php foreach ($q['options'] as $opt): ?>
                                <?php
                                $existStatus = $existVal['status'] ?? '';
                                $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                ?>
                                <label class="option-btn option-btn--<?= $opt['color'] ?> <?= $checked ? 'selected' : '' ?>"
                                       for="dluha_<?= $opt['value'] ?>">
                                    <input type="radio"
                                           id="dluha_<?= $opt['value'] ?>"
                                           name="dluha"
                                           value="<?= $opt['value'] ?>"
                                           <?= $checked ?>
                                           class="d-none option-radio">
                                    <i class="bi <?= $opt['icon'] ?> option-icon"></i>
                                    <span><?= $opt['label'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($q['type'] === 'belajar'): ?>
                        <!-- Belajar di kamar -->
                        <div class="option-grid option-grid--2">
                            <?php foreach ($q['options'] as $opt): ?>
                                <?php
                                $existStatus = $existVal['status'] ?? '';
                                $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                ?>
                                <label class="option-btn option-btn--<?= $opt['color'] ?> option-btn--big <?= $checked ? 'selected' : '' ?>"
                                       for="<?= $q['id'] ?>_<?= $opt['value'] ?>">
                                    <input type="radio"
                                           id="<?= $q['id'] ?>_<?= $opt['value'] ?>"
                                           name="<?= $q['id'] ?>"
                                           value="<?= $opt['value'] ?>"
                                           <?= $checked ?>
                                           class="d-none option-radio">
                                    <i class="bi <?= $opt['icon'] ?> option-icon"></i>
                                    <span><?= $opt['label'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
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
                        <button type="button" class="btn-wizard-next" data-step="<?= $stepIdx + 1 ?>">
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

    // ── Tombol Lanjut ───────────────────────────────────────
    document.querySelectorAll('.btn-wizard-next').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (step < TOTAL) showStep(step + 1);
        });
    });

    // ── Tombol Kembali ──────────────────────────────────────
    document.querySelectorAll('.btn-wizard-prev').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (step > 1) showStep(step - 1);
        });
    });

    // ── Pilihan radio → visual selected & Auto-next ──────
    document.querySelectorAll('.option-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const group = this.closest('.option-grid, .quran-options');
            if (group) group.querySelectorAll('.option-btn, .quran-type-btn').forEach(lbl => lbl.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            let autoNext = true;

            // Tampilkan/sembunyikan keterangan izin
            const field = this.dataset.field;
            if (field) {
                const ketWrap = document.getElementById('ket_' + field);
                if (ketWrap) {
                    ketWrap.style.display = this.value === 'izin' ? '' : 'none';
                    if (this.value === 'izin') {
                        ketWrap.querySelector('textarea')?.focus();
                        autoNext = false; // Jangan auto-next karena harus ngetik izin
                    }
                }
            }

            // Auto-next dengan delay agar animasi klik terlihat
            if (autoNext && current < TOTAL) {
                setTimeout(() => showStep(current + 1), 300);
            }
        });
    });

    // ── Quran Main (Iya/Belum) ──────────────────────────────
    document.querySelectorAll('.quran-main-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.quran-main-btn').forEach(l => l.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            const wrap = document.getElementById('quranDetailWrap');
            if (this.value === 'belum') {
                wrap.style.display = 'none';
                document.getElementById('quran_tidak').checked = true;
                if (current < TOTAL) {
                    setTimeout(() => showStep(current + 1), 300);
                }
            } else {
                wrap.style.display = '';
                // Hapus checked dari 'tidak'
                document.getElementById('quran_tidak').checked = false;
            }
        });
    });

    // ── Quran type (Juz/Halaman) ─────────
    document.querySelectorAll('.quran-sub-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.quran-type-btn').forEach(l => l.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            const wrap  = document.getElementById('quranJumlahWrap');
            const label = document.getElementById('quranJumlahLabel');
            if (this.value === 'setengah_juz' || this.value === 'tidak') {
                wrap.style.display = 'none';
                if (current < TOTAL) {
                    setTimeout(() => showStep(current + 1), 300);
                }
            } else {
                wrap.style.display = '';
                if (label) label.textContent = 'Jumlah ' + (this.value === 'juz' ? 'juz' : 'halaman') + ':';
            }
        });
    });

    // ── Counter Al-Qur'an ───────────────────────────────────
    const jumlahInput = document.getElementById('quranJumlah');
    document.getElementById('btnMin')?.addEventListener('click', () => {
        if (jumlahInput && parseInt(jumlahInput.value) > 1) jumlahInput.value = parseInt(jumlahInput.value) - 1;
    });
    document.getElementById('btnPlus')?.addEventListener('click', () => {
        if (jumlahInput) jumlahInput.value = parseInt(jumlahInput.value) + 1;
    });

    // ── Buku Main (Iya/Belum) ──────────────────────────────
    document.querySelectorAll('.buku-main-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.buku-main-btn').forEach(l => l.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            const wrap = document.getElementById('bukuDetailWrap');
            if (this.value === 'belum') {
                wrap.style.display = 'none';
                if (current < TOTAL) {
                    setTimeout(() => showStep(current + 1), 300);
                }
            } else {
                wrap.style.display = '';
            }
        });
    });

    // ── Counter Buku ─────────────────────────────────────────
    const bukuJumlahInput = document.getElementById('bukuJumlah');
    document.getElementById('btnMinBuku')?.addEventListener('click', () => {
        if (bukuJumlahInput && parseInt(bukuJumlahInput.value) > 1) bukuJumlahInput.value = parseInt(bukuJumlahInput.value) - 1;
    });
    document.getElementById('btnPlusBuku')?.addEventListener('click', () => {
        if (bukuJumlahInput) bukuJumlahInput.value = parseInt(bukuJumlahInput.value) + 1;
    });

    // ── Submit loading ──────────────────────────────────────
    document.getElementById('formAbsen').addEventListener('submit', function () {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        btn.disabled = true;
    });

    // ── Keyboard navigation ─────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' && current < TOTAL) showStep(current + 1);
        if (e.key === 'ArrowLeft'  && current > 1)     showStep(current - 1);
    });

})();
</script>
</body>
</html>
