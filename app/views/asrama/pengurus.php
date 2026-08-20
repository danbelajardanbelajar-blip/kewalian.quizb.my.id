<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-person-badge text-primary me-2"></i>
                Pengurus Asrama
            </h1>
            <p class="page-subtitle">Kelola nama dan kontak pengurus masing-masing asrama</p>
        </div>
    </div>
</div>

<?= Flash::render() ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-main shadow-sm">
            <div class="card-header-custom">
                <i class="bi bi-plus-circle me-2"></i> Tambah / Edit Pengurus
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/asrama/simpan" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Asrama</label>
                        <input type="text" class="form-control" name="nama_asrama" id="inputAsrama" placeholder="Contoh: Asrama A" required>
                        <div class="form-text">Jika asrama sudah ada, data pengurus akan ditimpa.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Pengurus</label>
                        <input type="text" class="form-control" name="nama_pengurus" id="inputPengurus" placeholder="Ust. Fulan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No HP / WA (Awali 62)</label>
                        <input type="text" class="form-control" name="no_hp" id="inputHp" placeholder="62812345678" required>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card card-main shadow-sm">
            <div class="card-header-custom">
                <i class="bi bi-list me-2"></i> Daftar Pengurus Asrama
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Asrama</th>
                                <th>Nama Pengurus</th>
                                <th>No WA</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['nama_asrama']) ?></td>
                                <td><?= htmlspecialchars($row['nama_pengurus']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= htmlspecialchars($row['no_hp']) ?>" target="_blank" class="text-decoration-none text-success">
                                        <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($row['no_hp']) ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btn-edit" 
                                        data-asrama="<?= htmlspecialchars($row['nama_asrama']) ?>"
                                        data-pengurus="<?= htmlspecialchars($row['nama_pengurus']) ?>"
                                        data-hp="<?= htmlspecialchars($row['no_hp']) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="<?= BASE_URL ?>/asrama/hapus" method="POST" class="d-inline" onsubmit="return confirm('Yakin menghapus pengurus ini?');">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('inputAsrama').value = this.dataset.asrama;
        document.getElementById('inputPengurus').value = this.dataset.pengurus;
        document.getElementById('inputHp').value = this.dataset.hp;
        document.getElementById('inputAsrama').focus();
    });
});
</script>
