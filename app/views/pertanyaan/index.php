<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-ui-radios text-primary me-2"></i>
                Manajemen Pertanyaan
            </h1>
            <p class="page-subtitle">Atur pertanyaan form absen mandiri untuk kelas Anda</p>
        </div>
        <a href="<?= BASE_URL ?>/pertanyaan/tambah" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan
        </a>
    </div>
</div>

<?= Flash::render() ?>

<div class="card card-main shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablePertanyaan">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3" style="width: 40px;"></th>
                        <th class="px-3 py-3" style="width: 50px;">No</th>
                        <th class="py-3">Judul Pertanyaan</th>
                        <th class="py-3">Tipe</th>
                        <th class="py-3">Detail Opsi / Poin</th>
                        <th class="py-3">Status</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pertanyaan)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Belum ada pertanyaan yang dikonfigurasi.<br>
                                <a href="<?= BASE_URL ?>/pertanyaan/tambah" class="btn btn-outline-primary mt-2">Buat Pertanyaan Pertama</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($pertanyaan as $row): ?>
                            <?php $opsi = json_decode($row['opsi'], true); ?>
                            <tr data-id="<?= $row['id'] ?>">
                                <td class="px-3 text-muted drag-handle" style="cursor: grab;">
                                    <i class="bi bi-grip-vertical fs-5"></i>
                                </td>
                                <td class="px-3 fw-bold text-muted nomor-urut"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></td>
                                <td>
                                    <?php if ($row['tipe'] === 'pilihan_ganda'): ?>
                                        <span class="badge bg-info text-dark">Pilihan Ganda</span>
                                    <?php elseif ($row['tipe'] === 'ganda_dan_angka'): ?>
                                        <span class="badge bg-primary">Ganda + Angka</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Input Angka</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['tipe'] === 'pilihan_ganda' || $row['tipe'] === 'ganda_dan_angka'): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php 
                                            $listOpsi = ($row['tipe'] === 'ganda_dan_angka') ? ($opsi['pilihan'] ?? []) : $opsi;
                                            foreach ($listOpsi as $op): ?>
                                                <li>
                                                    - <?= htmlspecialchars($op['label']) ?> 
                                                    <span class="text-success">(Poin: <?= $op['poin'] ?>)</span>
                                                    <?php if(!empty($op['require_ket'])): ?>
                                                        <span class="badge bg-warning text-dark" style="font-size:0.6rem">Wajib Ket</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($op['require_angka'])): ?>
                                                        <span class="badge bg-primary" style="font-size:0.6rem">Wajib Angka</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if ($row['tipe'] === 'ganda_dan_angka'): ?>
                                            <div class="small mt-1 pt-1 border-top border-light">
                                                <span class="text-muted"><i class="bi bi-123"></i> Angka:</span> 
                                                <?= $opsi['angka']['poin_per_angka'] ?? 0 ?> poin/<?= htmlspecialchars($opsi['angka']['satuan'] ?? 'satuan') ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="small">
                                            Poin per angka: <strong><?= $opsi['poin_per_angka'] ?? 0 ?></strong><br>
                                            Satuan: <strong><?= htmlspecialchars($opsi['satuan'] ?? '') ?></strong><br>
                                            <?php if(!empty($opsi['require_ket'])): ?>
                                                <span class="badge bg-warning text-dark mt-1" style="font-size:0.6rem">Wajib Ket</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 text-end">
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL ?>/pertanyaan/edit/<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/pertanyaan/duplikat/<?= $row['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menduplikat pertanyaan ini?');">
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Duplikat">
                                                <i class="bi bi-files"></i>
                                            </button>
                                        </form>
                                        <form action="<?= BASE_URL ?>/pertanyaan/hapus/<?= $row['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pertanyaan ini? Data laporan yang berkaitan dengan pertanyaan ini mungkin terpengaruh.');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector('#tablePertanyaan tbody');
    if (tbody && tbody.querySelector('.drag-handle')) {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function () {
                const rows = tbody.querySelectorAll('tr[data-id]');
                const newOrder = [];
                rows.forEach((row, index) => {
                    newOrder.push(row.dataset.id);
                    // Update nomor urut di tampilan
                    row.querySelector('.nomor-urut').textContent = index + 1;
                });
                
                fetch('<?= BASE_URL ?>/pertanyaan/urut', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ urutan: newOrder })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Gagal menyimpan urutan pertanyaan.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat menyimpan urutan.');
                });
            }
        });
    }
});
</script>
