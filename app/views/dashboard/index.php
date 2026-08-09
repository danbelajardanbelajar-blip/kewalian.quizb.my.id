<?php
/**
 * Dashboard/index.php — Form Input Presensi Harian (Wali Kelas)
 * Mode Wizard Per Pertanyaan (menyesuaikan Absen Mandiri)
 */
$today = date('Y-m-d');

$pertanyaan = [
    [
        'id'     => 'sekolah',
        'label'  => 'Kehadiran Sekolah',
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
        'label'  => 'Kehadiran Al-Miftah Siang',
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
        'label'  => 'Kehadiran Diniyah Malam',
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
        'label'  => 'Kehadiran Ngaji Pagi',
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
        'label'  => 'Bacaan Al-Qur\'an',
        'icon'   => 'bi-journal-bookmark',
        'emoji'  => '📿',
        'type'   => 'quran',
    ],
    [
        'id'     => 'dluha',
        'label'  => 'Shalat Dluha',
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
        'label'  => 'Belajar di Kamar',
        'icon'   => 'bi-lamp',
        'emoji'  => '📚',
        'type'   => 'belajar',
        'options'=> [
            ['value'=>'iya',   'label'=>'Iya', 'icon'=>'bi-check-circle-fill', 'color'=>'success'],
            ['value'=>'tidak', 'label'=>'Tidak',          'icon'=>'bi-x-circle-fill',     'color'=>'danger'],
        ],
    ],
];

$totalStep = count($pertanyaan);
?>

<!-- Include CSS Absen Khusus Form -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/absen.css">
<style>
/* Penyesuaian UI Wali Kelas (Dashboard) dengan CSS Absen */
.dashboard-wizard-container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
}
.student-list-item {
    border-bottom: 1px solid #eee;
    padding: 1rem;
}
.student-list-item:last-child {
    border-bottom: none;
}
.student-name {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
/* Paksa ukuran grid options mengecil dikit di list */
.dashboard-wizard-container .option-btn {
    padding: 0.5rem;
    font-size: 0.85rem;
}
.dashboard-wizard-container .quran-options .quran-type-btn {
    padding: 0.5rem;
    font-size: 0.85rem;
}
</style>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-clipboard2-check me-2 text-primary"></i>
                Input Laporan Wali Kelas
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                <?php if ($isEdit): ?>
                    <span class="badge badge-edit"><i class="bi bi-pencil me-1"></i>Mode Edit</span>
                <?php else: ?>
                    <span class="badge badge-new"><i class="bi bi-plus-circle me-1"></i>Input Baru</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-journal-text me-1"></i> Riwayat Laporan
            </a>
        </div>
    </div>
</div>

<form action="<?= BASE_URL ?>/laporan/simpan" method="POST" id="formPresensi">
    <!-- Topbar Tanggal -->
    <div class="card card-main shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-auto">
                    <label for="tanggal" class="form-label fw-semibold mb-1">
                        <i class="bi bi-calendar3 me-1"></i> Tanggal Input
                    </label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" max="<?= $today ?>" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Progress -->
    <div class="dashboard-wizard-container mb-5">
        <div class="wizard-topbar rounded-top" style="position:relative; z-index:10">
            <div class="wizard-progress-wrap flex-grow-1 ms-3">
                <div class="wizard-progress-bar" id="progressBar" style="width: <?= round(100/$totalStep) ?>%"></div>
            </div>
            <div class="wizard-step-label me-3" id="stepLabel">1 / <?= $totalStep ?></div>
        </div>

        <div class="wizard-container bg-white position-relative" style="min-height: 400px;">
            <?php foreach ($pertanyaan as $stepIdx => $q): ?>
                <?php
                $isFirst = $stepIdx === 0;
                $isLast  = $stepIdx === $totalStep - 1;
                ?>
                <div class="wizard-step <?= $isFirst ? 'wizard-step--active' : '' ?>" data-step="<?= $stepIdx + 1 ?>" id="step<?= $stepIdx + 1 ?>">
                    
                    <div class="p-3 text-center border-bottom bg-light">
                        <div class="question-emoji" style="font-size:2rem; margin-bottom:0"><?= $q['emoji'] ?></div>
                        <h2 class="question-text" style="font-size:1.3rem; margin-top:0.5rem; margin-bottom:0"><?= htmlspecialchars($q['label']) ?></h2>
                    </div>

                    <div class="student-list">
                        <?php foreach ($siswa as $s): ?>
                            <?php 
                                $idSiswa  = $s['id'];
                                $existVal = $existingSiswaData[$s['nama']][$q['id']] ?? []; // Nanti disesuaikan di controller
                                $inputBase = "data[{$idSiswa}][{$q['id']}]";
                            ?>
                            <div class="student-list-item">
                                <div class="student-name">
                                    <div class="wizard-avatar" style="width:30px;height:30px;font-size:14px"><?= mb_substr(ucwords($s['nama']), 0, 1) ?></div>
                                    <?= htmlspecialchars(ucwords(strtolower($s['nama']))) ?>
                                    <input type="hidden" name="data[<?= $idSiswa ?>][nama]" value="<?= htmlspecialchars($s['nama']) ?>">
                                </div>
                                
                                <?php if ($q['type'] === 'kehadiran'): ?>
                                    <div class="option-grid" data-group="<?= $q['id'] ?>_<?= $idSiswa ?>">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <?php
                                            $existStatus = $existVal['status'] ?? '';
                                            $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                            ?>
                                            <label class="option-btn option-btn--<?= $opt['color'] ?> <?= $checked ? 'selected' : '' ?>"
                                                   for="<?= $q['id'] ?>_<?= $idSiswa ?>_<?= $opt['value'] ?>">
                                                <input type="radio"
                                                       id="<?= $q['id'] ?>_<?= $idSiswa ?>_<?= $opt['value'] ?>"
                                                       name="<?= $inputBase ?>[status]"
                                                       value="<?= $opt['value'] ?>"
                                                       <?= $checked ?>
                                                       class="d-none option-radio"
                                                       data-field="<?= $q['id'] ?>_<?= $idSiswa ?>">
                                                <i class="bi <?= $opt['icon'] ?> option-icon"></i>
                                                <span><?= $opt['label'] ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="izin-ket-wrap mt-2" id="ket_<?= $q['id'] ?>_<?= $idSiswa ?>"
                                         style="<?= ($existVal['status'] ?? '') === 'izin' ? '' : 'display:none' ?>">
                                        <input type="text" name="<?= $inputBase ?>[ket]" class="form-control form-control-sm" placeholder="Keterangan izin..." value="<?= htmlspecialchars($existVal['ket'] ?? '') ?>">
                                    </div>

                                <?php elseif ($q['type'] === 'quran'): ?>
                                    <?php
                                    $qType   = $existVal['type']   ?? '';
                                    $qJumlah = $existVal['jumlah'] ?? 1;
                                    ?>
                                    <div class="quran-options">
                                        <label class="quran-type-btn <?= $qType === 'halaman' ? 'selected' : '' ?>"
                                               for="quran_halaman_<?= $idSiswa ?>">
                                            <input type="radio" id="quran_halaman_<?= $idSiswa ?>" name="<?= $inputBase ?>[type]"
                                                   value="halaman" class="d-none quran-radio"
                                                   <?= $qType === 'halaman' ? 'checked' : '' ?> data-id="<?= $idSiswa ?>">
                                            <span class="quran-icon">📄</span>
                                            <span>Hal</span>
                                        </label>
                                        <label class="quran-type-btn <?= $qType === 'juz' ? 'selected' : '' ?>"
                                               for="quran_juz_<?= $idSiswa ?>">
                                            <input type="radio" id="quran_juz_<?= $idSiswa ?>" name="<?= $inputBase ?>[type]"
                                                   value="juz" class="d-none quran-radio"
                                                   <?= $qType === 'juz' ? 'checked' : '' ?> data-id="<?= $idSiswa ?>">
                                            <span class="quran-icon">📖</span>
                                            <span>Juz</span>
                                        </label>
                                        <label class="quran-type-btn <?= $qType === 'setengah_juz' ? 'selected' : '' ?>"
                                               for="quran_setengah_<?= $idSiswa ?>">
                                            <input type="radio" id="quran_setengah_<?= $idSiswa ?>" name="<?= $inputBase ?>[type]"
                                                   value="setengah_juz" class="d-none quran-radio"
                                                   <?= $qType === 'setengah_juz' ? 'checked' : '' ?> data-id="<?= $idSiswa ?>">
                                            <span class="quran-icon">📑</span>
                                            <span>½ Juz</span>
                                        </label>
                                        <label class="quran-type-btn <?= $qType === 'tidak' ? 'selected' : '' ?>"
                                               for="quran_tidak_<?= $idSiswa ?>">
                                            <input type="radio" id="quran_tidak_<?= $idSiswa ?>" name="<?= $inputBase ?>[type]"
                                                   value="tidak" class="d-none quran-radio"
                                                   <?= $qType === 'tidak' ? 'checked' : '' ?> data-id="<?= $idSiswa ?>">
                                            <span class="quran-icon">❌</span>
                                            <span>Belum</span>
                                        </label>
                                    </div>

                                    <div class="quran-jumlah-wrap mt-2" id="quranJumlahWrap_<?= $idSiswa ?>"
                                         style="<?= ($qType === 'setengah_juz' || $qType === 'tidak' || $qType === '') ? 'display:none' : '' ?>">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="mb-0 text-muted small quran-label" id="quranLabel_<?= $idSiswa ?>">Berapa <?= $qType === 'juz' ? 'juz' : 'halaman' ?>?</label>
                                            <input type="number" name="<?= $inputBase ?>[jumlah]" class="form-control form-control-sm" style="width:80px" value="<?= $qJumlah ?>" min="1" max="999">
                                        </div>
                                    </div>

                                <?php elseif ($q['type'] === 'dluha'): ?>
                                    <div class="option-grid option-grid--3" data-group="dluha_<?= $idSiswa ?>">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <?php
                                            $existStatus = $existVal['status'] ?? '';
                                            $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                            ?>
                                            <label class="option-btn option-btn--<?= $opt['color'] ?> <?= $checked ? 'selected' : '' ?>"
                                                   for="dluha_<?= $idSiswa ?>_<?= $opt['value'] ?>">
                                                <input type="radio"
                                                       id="dluha_<?= $idSiswa ?>_<?= $opt['value'] ?>"
                                                       name="<?= $inputBase ?>[status]"
                                                       value="<?= $opt['value'] ?>"
                                                       <?= $checked ?>
                                                       class="d-none option-radio">
                                                <i class="bi <?= $opt['icon'] ?> option-icon"></i>
                                                <span><?= $opt['label'] ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                <?php elseif ($q['type'] === 'belajar'): ?>
                                    <div class="option-grid option-grid--2" data-group="belajar_<?= $idSiswa ?>">
                                        <?php foreach ($q['options'] as $opt): ?>
                                            <?php
                                            $existStatus = $existVal['status'] ?? '';
                                            $checked = ($existStatus === $opt['value']) ? 'checked' : '';
                                            ?>
                                            <label class="option-btn option-btn--<?= $opt['color'] ?> <?= $checked ? 'selected' : '' ?>"
                                                   for="belajar_<?= $idSiswa ?>_<?= $opt['value'] ?>">
                                                <input type="radio"
                                                       id="belajar_<?= $idSiswa ?>_<?= $opt['value'] ?>"
                                                       name="<?= $inputBase ?>[status]"
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
                        <?php endforeach; ?>
                    </div>

                    <div class="wizard-nav p-3 border-top bg-light">
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
                            <button type="submit" class="btn-wizard-submit" id="btnSimpan">
                                <i class="bi bi-check-lg me-1"></i> Selesai &amp; Simpan
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<script>
(function () {
    'use strict';

    const TOTAL = <?= $totalStep ?>;
    let current = 1;

    // Ganti tanggal -> auto reload form
    document.getElementById('tanggal').addEventListener('change', function () {
        window.location.href = '<?= BASE_URL ?>?tanggal=' + this.value;
    });

    function updateProgress(step) {
        const pct = Math.round((step / TOTAL) * 100);
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('stepLabel').textContent = step + ' / ' + TOTAL;
    }

    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => {
            el.classList.remove('wizard-step--active');
        });
        const el = document.getElementById('step' + step);
        if (el) {
            el.classList.add('wizard-step--active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        updateProgress(step);
        current = step;
    }

    document.querySelectorAll('.btn-wizard-next').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (step < TOTAL) showStep(step + 1);
        });
    });

    document.querySelectorAll('.btn-wizard-prev').forEach(btn => {
        btn.addEventListener('click', () => {
            const step = parseInt(btn.dataset.step);
            if (step > 1) showStep(step - 1);
        });
    });

    // Handle radio buttons UI
    document.querySelectorAll('.option-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const group = this.closest('.option-grid');
            if (group) group.querySelectorAll('.option-btn').forEach(lbl => lbl.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            // Handle izin ket
            const field = this.dataset.field;
            if (field) {
                const ketWrap = document.getElementById('ket_' + field);
                if (ketWrap) {
                    ketWrap.style.display = this.value === 'izin' ? '' : 'none';
                    if (this.value === 'izin') {
                        ketWrap.querySelector('input').focus();
                    }
                }
            }
        });
    });

    // Handle Quran options
    document.querySelectorAll('.quran-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            const group = this.closest('.quran-options');
            if (group) group.querySelectorAll('.quran-type-btn').forEach(l => l.classList.remove('selected'));
            this.closest('label')?.classList.add('selected');

            const id = this.dataset.id;
            const wrap = document.getElementById('quranJumlahWrap_' + id);
            const label = document.getElementById('quranLabel_' + id);
            
            if (this.value === 'setengah_juz' || this.value === 'tidak') {
                wrap.style.display = 'none';
            } else {
                wrap.style.display = 'block';
                if (label) label.textContent = 'Berapa ' + (this.value === 'juz' ? 'juz' : 'halaman') + '?';
            }
        });
    });

    document.getElementById('formPresensi').addEventListener('submit', function () {
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        btn.disabled = true;
    });
})();
</script>
