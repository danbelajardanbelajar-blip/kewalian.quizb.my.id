<div class="page-header mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= BASE_URL ?>/pertanyaan" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="page-title">
                <?= $data ? 'Edit Pertanyaan' : 'Tambah Pertanyaan' ?>
            </h1>
            <p class="page-subtitle mb-0">Konfigurasi pertanyaan dan poin absen</p>
        </div>
    </div>
</div>

<div class="card card-main shadow-sm">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST" id="formPertanyaan">
            <?php if ($data): ?>
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Judul Pertanyaan</label>
                <input type="text" name="judul" class="form-control" required 
                       value="<?= $data ? htmlspecialchars($data['judul']) : '' ?>"
                       placeholder="Misal: Apakah Anda shalat dhuha hari ini?">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Tipe Jawaban</label>
                <select name="tipe" id="tipeSelect" class="form-select">
                    <option value="pilihan_ganda" <?= ($data && $data['tipe'] === 'pilihan_ganda') ? 'selected' : '' ?>>Pilihan Ganda (Radio)</option>
                    <option value="angka" <?= ($data && $data['tipe'] === 'angka') ? 'selected' : '' ?>>Input Angka (Number)</option>
                </select>
            </div>

            <!-- KONFIGURASI PILIHAN GANDA -->
            <div id="configPilihanGanda" class="border rounded p-3 mb-4 bg-light config-section">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Opsi Pilihan Ganda</h5>
                <p class="text-muted small">Tambahkan opsi jawaban, poin yang didapat, dan centang "Wajib Ket" jika pengguna harus mengisi alasan (misal untuk opsi Izin/Sakit).</p>
                
                <div id="opsiContainer">
                    <?php 
                    $opsiData = [];
                    if ($data && $data['tipe'] === 'pilihan_ganda') {
                        $opsiData = json_decode($data['opsi'], true);
                    }
                    
                    if (empty($opsiData)) {
                        // Default options
                        $opsiData = [
                            ['label' => 'Ya / Hadir', 'poin' => 10, 'require_ket' => false],
                            ['label' => 'Tidak / Absen', 'poin' => 0, 'require_ket' => false]
                        ];
                    }
                    ?>
                    
                    <?php foreach ($opsiData as $idx => $op): ?>
                    <div class="row g-2 mb-2 opsi-item align-items-center">
                        <div class="col-sm-5">
                            <input type="text" name="opsi_label[]" class="form-control" placeholder="Label Opsi (Hadir, Izin, dll)" value="<?= htmlspecialchars($op['label'] ?? '') ?>" required>
                        </div>
                        <div class="col-sm-3">
                            <input type="number" name="opsi_poin[]" class="form-control" placeholder="Poin" value="<?= $op['poin'] ?? 0 ?>" required>
                        </div>
                        <div class="col-sm-3 text-center">
                            <div class="form-check form-switch d-inline-block mt-2">
                                <input class="form-check-input" type="checkbox" name="opsi_req_ket[<?= $idx ?>]" value="1" <?= (!empty($op['require_ket'])) ? 'checked' : '' ?>>
                                <label class="form-check-label small">Wajib Ket</label>
                            </div>
                        </div>
                        <div class="col-sm-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-opsi"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="btnAddOpsi">
                    <i class="bi bi-plus"></i> Tambah Opsi
                </button>
            </div>

            <!-- KONFIGURASI ANGKA -->
            <div id="configAngka" class="border rounded p-3 mb-4 bg-light config-section" style="display:none;">
                <h5 class="fw-bold mb-3"><i class="bi bi-123 me-2"></i>Konfigurasi Input Angka</h5>
                <?php 
                    $angkaData = ['poin_per_angka' => 1, 'satuan' => '', 'require_ket' => false];
                    if ($data && $data['tipe'] === 'angka') {
                        $angkaData = json_decode($data['opsi'], true);
                    }
                ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Poin Per Angka</label>
                        <input type="number" name="poin_per_angka" class="form-control" value="<?= $angkaData['poin_per_angka'] ?? 1 ?>">
                        <div class="form-text">Berapa poin yang didapat per kelipatan angka. Misal input "5", poin per angka "2" = total 10 poin.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Satuan (Opsional)</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Halaman, Juz, Menit" value="<?= htmlspecialchars($angkaData['satuan'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="angka_req_ket" value="1" <?= (!empty($angkaData['require_ket'])) ? 'checked' : '' ?>>
                            <label class="form-check-label">Wajibkan Keterangan Tambahan untuk input angka ini</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= (!$data || $data['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="isActive">Tampilkan pertanyaan ini di form absen</label>
                </div>
            </div>

            <hr>
            <div class="text-end">
                <a href="<?= BASE_URL ?>/pertanyaan" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i> Simpan Pertanyaan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeSelect = document.getElementById('tipeSelect');
    const configPilihanGanda = document.getElementById('configPilihanGanda');
    const configAngka = document.getElementById('configAngka');
    
    function toggleConfig() {
        if (tipeSelect.value === 'pilihan_ganda') {
            configPilihanGanda.style.display = 'block';
            configAngka.style.display = 'none';
        } else {
            configPilihanGanda.style.display = 'none';
            configAngka.style.display = 'block';
        }
    }
    
    tipeSelect.addEventListener('change', toggleConfig);
    toggleConfig(); // init

    // Add opsi logic
    const btnAddOpsi = document.getElementById('btnAddOpsi');
    const opsiContainer = document.getElementById('opsiContainer');
    let opsiIndex = document.querySelectorAll('.opsi-item').length || 999;
    
    btnAddOpsi.addEventListener('click', function() {
        opsiIndex++;
        const html = `
            <div class="row g-2 mb-2 opsi-item align-items-center">
                <div class="col-sm-5">
                    <input type="text" name="opsi_label[]" class="form-control" placeholder="Label Opsi" required>
                </div>
                <div class="col-sm-3">
                    <input type="number" name="opsi_poin[]" class="form-control" placeholder="Poin" value="0" required>
                </div>
                <div class="col-sm-3 text-center">
                    <div class="form-check form-switch d-inline-block mt-2">
                        <input class="form-check-input" type="checkbox" name="opsi_req_ket[${opsiIndex}]" value="1">
                        <label class="form-check-label small">Wajib Ket</label>
                    </div>
                </div>
                <div class="col-sm-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-opsi"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        opsiContainer.insertAdjacentHTML('beforeend', html);
    });

    opsiContainer.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-opsi')) {
            const items = document.querySelectorAll('.opsi-item');
            if (items.length > 1) {
                e.target.closest('.opsi-item').remove();
            } else {
                alert('Minimal harus ada 1 opsi.');
            }
        }
    });
});
</script>
