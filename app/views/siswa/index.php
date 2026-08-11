<?php
/**
 * siswa/index.php — Manajemen Data Siswa
 */
?>
<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people me-2 text-primary"></i>
                Manajemen Data Siswa
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                <strong><?= count($siswa) ?></strong> siswa terdaftar
            </p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Siswa -->
    <div class="col-12 col-md-4">
        <div class="card card-main shadow-sm h-100">
            <div class="card-header-custom">
                <i class="bi bi-person-plus me-2"></i> Tambah Siswa Baru
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/siswa/tambah" method="POST" id="formTambah" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="namaBaru" class="form-label fw-semibold">Nama Lengkap Siswa</label>
                        <input type="text"
                               class="form-control"
                               id="namaBaru"
                               name="nama"
                               placeholder="Contoh: AHMAD FADHIL ELFAJRI"
                               required
                               autocomplete="off">
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Nama akan otomatis diubah ke HURUF KAPITAL
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamatBaru" class="form-label fw-semibold">Alamat (Opsional)</label>
                        <textarea class="form-control" id="alamatBaru" name="alamat" rows="2" placeholder="Contoh: Jl. Sudirman No. 123..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fotoBaru" class="form-label fw-semibold">Foto Siswa (Opsional)</label>
                        <input type="file" class="form-control" id="fotoBaru" name="foto" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-person-plus me-1"></i> Tambah Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="col-12 col-md-8">
        <div class="card card-main shadow-sm">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Siswa <span class="badge bg-primary"><?= count($siswa) ?></span>
                </span>
                <div class="input-group input-group-sm" style="max-width:220px">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0"
                           id="searchSiswa" placeholder="Cari nama...">
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($siswa)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people display-6 d-block mb-2"></i>
                        Belum ada siswa terdaftar.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush" id="listSiswa">
                        <?php foreach ($siswa as $i => $s): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center siswa-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="siswa-no" title="ID Siswa"><?= $s['id'] ?></div>
                                    <?php if (!empty($s['foto'])): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/foto_siswa/<?= htmlspecialchars($s['foto']) ?>" alt="Foto" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;border:1px solid #dee2e6;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center text-secondary border" style="width:40px;height:40px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium siswa-nama"><?= htmlspecialchars($s['nama']) ?></span>
                                        <?php if (!empty($s['no_hp'])): ?>
                                            <span class="text-muted small" style="font-size:0.8rem"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($s['no_hp']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['alamat'])): ?>
                                            <span class="text-muted small text-truncate" style="font-size:0.8rem; max-width: 250px;"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['alamat']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-edit-siswa"
                                            data-id="<?= $s['id'] ?>"
                                            data-nama="<?= htmlspecialchars($s['nama'], ENT_QUOTES) ?>"
                                            data-nohp="<?= htmlspecialchars($s['no_hp'] ?? '', ENT_QUOTES) ?>"
                                            data-alamat="<?= htmlspecialchars($s['alamat'] ?? '', ENT_QUOTES) ?>"
                                            title="Edit Siswa">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="<?= BASE_URL ?>/siswa/hapus" method="POST"
                                          class="d-inline form-hapus-siswa"
                                          data-nama="<?= htmlspecialchars($s['nama'], ENT_QUOTES) ?>">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Siswa">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/siswa/edit" method="POST" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="editId">
                <div class="mb-3">
                    <label for="editNama" class="form-label fw-semibold">Nama Lengkap Siswa</label>
                    <input type="text" class="form-control" id="editNama" name="nama" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="editNoHp" class="form-label fw-semibold">Nomor WhatsApp (Opsional)</label>
                    <input type="text" class="form-control" id="editNoHp" name="no_hp" placeholder="Contoh: 6281234567890" autocomplete="off">
                    <div class="form-text text-muted">Awali dengan 62 tanpa spasi/simbol.</div>
                </div>
                <div class="mb-3">
                    <label for="editAlamat" class="form-label fw-semibold">Alamat (Opsional)</label>
                    <textarea class="form-control" id="editAlamat" name="alamat" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label for="editFoto" class="form-label fw-semibold">Ganti Foto Siswa (Opsional)</label>
                    <input type="file" class="form-control" id="editFoto" name="foto" accept="image/*">
                    <div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search siswa
    document.getElementById('searchSiswa')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.siswa-item').forEach(item => {
            const nama = item.querySelector('.siswa-nama').textContent.toLowerCase();
            item.style.display = nama.includes(q) ? '' : 'none';
        });
    });

    // Konfirmasi hapus siswa — pakai data-nama agar aman dari apostrof
    document.querySelectorAll('.form-hapus-siswa').forEach(form => {
        form.addEventListener('submit', function (e) {
            const nama = this.dataset.nama;
            const pesan = 'Hapus siswa ' + nama + '?\nSiswa yang dihapus tidak akan muncul di form presensi.\nData laporan yang sudah ada tidak terpengaruh.';
            if (!confirm(pesan)) e.preventDefault();
        });
    });

    // Modal edit siswa
    const modalEditEl = document.getElementById('modalEditSiswa');
    if (modalEditEl) {
        const modalEdit = new bootstrap.Modal(modalEditEl);
        document.querySelectorAll('.btn-edit-siswa').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('editId').value = this.dataset.id;
                document.getElementById('editNama').value = this.dataset.nama;
                document.getElementById('editNoHp').value = this.dataset.nohp;
                document.getElementById('editAlamat').value = this.dataset.alamat;
                modalEdit.show();
            });
        });
    }
});
</script>
