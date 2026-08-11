<?php
$adminTitle = 'Soal Default';
$adminActivePage = 'soal_default';
$adminSubtitle = 'Kelola pertanyaan default angket';
$unreadFeedback = $unreadFeedback ?? 0;
require_once APP_PATH . '/views/admin/layout_admin.php';
?>
<div class="admin-content">
    <div class="admin-card card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Soal Default</h5>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table admin-table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th style="width: 40%;">Judul Pertanyaan</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Urutan</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($soalList)): ?>
                            <?php $i=1; foreach($soalList as $soal): ?>
                            <tr>
                                <td class="ps-4"><?= $i++ ?></td>
                                <td>
                                    <div class="fw-bold text-truncate" style="max-width: 350px;" title="<?= htmlspecialchars($soal['judul']) ?>">
                                        <?= mb_strlen($soal['judul']) > 80 ? mb_substr(htmlspecialchars($soal['judul']), 0, 80) . '...' : htmlspecialchars($soal['judul']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($soal['tipe'] === 'pilihan_ganda'): ?>
                                        <span class="badge bg-info text-dark border">Pilihan Ganda</span>
                                    <?php elseif($soal['tipe'] === 'ganda_dan_angka'): ?>
                                        <span class="badge bg-warning text-dark border">Pilihan Ganda & Angka</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($soal['tipe']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($soal['is_active'] == 1): ?>
                                        <span class="badge bg-success rounded-pill badge-active"><i class="bi bi-check-circle"></i> Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill"><i class="bi bi-x-circle"></i> Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3"><?= htmlspecialchars($soal['urutan']) ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <form action="<?= BASE_URL ?>/admin/toggleSoalActive" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $soal['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $soal['is_active'] == 1 ? 'btn-outline-secondary' : 'btn-outline-success' ?>" title="Toggle Status">
                                                <i class="bi <?= $soal['is_active'] == 1 ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Edit Soal" 
                                            onclick='editSoal(<?= json_encode($soal, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="<?= BASE_URL ?>/admin/hapusSoal" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $soal['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Soal" onclick="return confirm('Yakin ingin menghapus soal ini secara permanen?');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-ui-radios fs-1 d-block mb-3 opacity-50"></i>
                                    Belum ada data pertanyaan default.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Soal -->
<div class="modal fade" id="modalTambahSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/admin/simpanSoal" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Soal Default Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label fw-bold">Judul Pertanyaan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="judul" name="judul" rows="2" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipe" class="form-label fw-bold">Tipe Soal <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="pilihan_ganda">Pilihan Ganda</option>
                                <option value="ganda_dan_angka">Pilihan Ganda & Isian Angka</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="urutan" class="form-label fw-bold">Urutan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="urutan" name="urutan" value="<?= count($soalList ?? []) + 1 ?>" required min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="opsi_json" class="form-label fw-bold">Opsi JSON</label>
                        <textarea class="form-control font-monospace" id="opsi_json" name="opsi_json" rows="4" placeholder='[{"label": "Sangat Baik", "skor": 4}, {"label": "Baik", "skor": 3}]'></textarea>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Format JSON sesuai tipe
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Soal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Soal -->
<div class="modal fade" id="modalEditSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= BASE_URL ?>/admin/updateSoal" method="POST">
            <input type="hidden" id="edit_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Soal Default</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_judul" class="form-label fw-bold">Judul Pertanyaan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_judul" name="judul" rows="2" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_tipe" class="form-label fw-bold">Tipe Soal <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_tipe" name="tipe" required>
                                <option value="pilihan_ganda">Pilihan Ganda</option>
                                <option value="ganda_dan_angka">Pilihan Ganda & Isian Angka</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_urutan" class="form-label fw-bold">Urutan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_urutan" name="urutan" required min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_opsi_json" class="form-label fw-bold">Opsi JSON</label>
                        <textarea class="form-control font-monospace" id="edit_opsi_json" name="opsi_json" rows="4"></textarea>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Format JSON sesuai tipe
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Soal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editSoal(soal) {
    document.getElementById('edit_id').value = soal.id;
    document.getElementById('edit_judul').value = soal.judul;
    document.getElementById('edit_tipe').value = soal.tipe;
    document.getElementById('edit_urutan').value = soal.urutan;
    document.getElementById('edit_opsi_json').value = soal.opsi_json || '';
    
    var editModal = new bootstrap.Modal(document.getElementById('modalEditSoal'));
    editModal.show();
}
</script>

<?php require_once APP_PATH . '/views/admin/layout_admin_footer.php'; ?>
