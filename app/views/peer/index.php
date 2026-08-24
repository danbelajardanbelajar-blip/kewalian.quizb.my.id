<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-people-fill text-primary me-2"></i>Bank Soal Peer Review</h1>
        </div>
    </div>
    
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Catatan:</strong> Setiap siswa akan menerima maksimal 2 pertanyaan secara acak dari bank soal aktif saat mereka mengisi laporan harian. Jawaban mereka 100% anonim dan rahasia.
    </div>

    <div class="row">
        <!-- Form Tambah/Edit -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold" id="formTitle">Tambah Pertanyaan</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/peer/simpan" method="POST" id="formPeer">
                        <input type="hidden" name="id" id="inputId" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teks Pertanyaan</label>
                            <textarea class="form-control" name="pertanyaan" id="inputPertanyaan" rows="3" placeholder="Misal: Siapa teman yang paling rajin?" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sifat (Kategori)</label>
                            <select class="form-select" name="sifat" id="inputSifat" required>
                                <option value="positif">Positif (Kebaikan, Prestasi)</option>
                                <option value="negatif">Negatif (Pelanggaran, Bullying)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status" id="inputStatus">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary w-100 d-none" id="btnCancel" onclick="resetForm()">Batal</button>
                            <button type="submit" class="btn btn-primary-custom w-100" id="btnSubmit"><i class="bi bi-save me-1"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Pertanyaan -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Pertanyaan</th>
                                    <th>Sifat</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pertanyaan)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada soal peer review.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pertanyaan as $p): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium"><?= htmlspecialchars($p['pertanyaan']) ?></td>
                                        <td>
                                            <?php if ($p['sifat'] === 'positif'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i class="bi bi-arrow-up-circle me-1"></i>Positif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><i class="bi bi-arrow-down-circle me-1"></i>Negatif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($p['status'] == 1): ?>
                                                <span class="badge bg-primary rounded-pill">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick='editPeer(<?= $p['id'] ?>, <?= json_encode($p['pertanyaan']) ?>, "<?= $p['sifat'] ?>", <?= $p['status'] ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>/peer/hapus" method="POST" class="d-inline" onsubmit="return confirm('Hapus pertanyaan ini? Data vote terkait juga akan terhapus.')">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editPeer(id, pertanyaan, sifat, status) {
    document.getElementById('inputId').value = id;
    document.getElementById('inputPertanyaan').value = pertanyaan;
    document.getElementById('inputSifat').value = sifat;
    document.getElementById('inputStatus').value = status;
    document.getElementById('formTitle').innerText = 'Edit Pertanyaan';
    document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-save me-1"></i> Update';
    document.getElementById('btnCancel').classList.remove('d-none');
}

function resetForm() {
    document.getElementById('inputId').value = '0';
    document.getElementById('formPeer').reset();
    document.getElementById('formTitle').innerText = 'Tambah Pertanyaan';
    document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-plus-circle me-1"></i> Tambah';
    document.getElementById('btnCancel').classList.add('d-none');
}
</script>
