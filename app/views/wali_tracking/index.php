<?php
/**
 * wali_tracking/index.php — Halaman Wali Murid Tracking
 */
$sudahHp      = 0;
$belumHp      = 0;
$sudahKunjung = 0;
$belumKunjung = 0;
foreach ($data as $row) {
    if (!empty($row['no_hp'])) $sudahHp++; else $belumHp++;
    if ((int)$row['total_kunjungan'] > 0) $sudahKunjung++; else $belumKunjung++;
}
$total = count($data);
?>

<div class="page-header mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="page-title">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>
                Wali Murid Tracking
            </h1>
            <p class="page-subtitle">
                Kelas <strong><?= htmlspecialchars($kelas) ?></strong> —
                Pantau kepedulian wali murid terhadap anaknya
            </p>
        </div>
    </div>
</div>

<?= Flash::render() ?>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Total Siswa</div>
            <div class="stat-card-value"><?= $total ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Sudah Isi No HP</div>
            <div class="stat-card-value text-success"><?= $sudahHp ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Belum Isi No HP</div>
            <div class="stat-card-value text-danger"><?= $belumHp ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-card-label">Pernah Lihat Laporan</div>
            <div class="stat-card-value text-info"><?= $sudahKunjung ?></div>
        </div>
    </div>
</div>

<?php if ($belumHp > 0): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>
        <strong>Wali murid belum mengisi nomor HP (<?= $belumHp ?> siswa):</strong><br>
        <small><?= implode(', ', array_map(fn($r) => htmlspecialchars($r['nama']), array_filter($data, fn($r) => empty($r['no_hp'])))) ?></small>
    </div>
</div>
<?php endif; ?>

<?php if ($belumKunjung > 0): ?>
<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-eye-slash-fill mt-1"></i>
    <div>
        <strong>Wali murid belum pernah membuka halaman laporan (<?= $belumKunjung ?> siswa):</strong><br>
        <small><?= implode(', ', array_map(fn($r) => htmlspecialchars($r['nama']), array_filter($data, fn($r) => (int)$r['total_kunjungan'] === 0))) ?></small>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Detail -->
<div class="card card-main shadow-sm">
    <div class="card-header-custom">
        <i class="bi bi-table me-2"></i> Detail Per Siswa
        <small class="ms-2 text-muted fw-normal">— klik header kolom untuk mengurutkan</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tblTracking">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-col="0" style="cursor:pointer;user-select:none;white-space:nowrap">
                            # <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="sortable" data-col="1" style="cursor:pointer;user-select:none;white-space:nowrap">
                            Nama Siswa <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="sortable text-center" data-col="2" style="cursor:pointer;user-select:none;white-space:nowrap">
                            No HP Wali <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="sortable text-center" data-col="3" style="cursor:pointer;user-select:none;white-space:nowrap">
                            Kunjungan Laporan <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="sortable text-center" data-col="4" style="cursor:pointer;user-select:none;white-space:nowrap">
                            Terakhir Kunjung <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="sortable text-center" data-col="5" style="cursor:pointer;user-select:none;white-space:nowrap">
                            Status Kepedulian <i class="bi bi-arrow-down-up text-muted small"></i>
                        </th>
                        <th class="text-center" style="white-space:nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                    <?php
                        $punyaHp      = !empty($row['no_hp']);
                        $sudahBuka    = (int)$row['total_kunjungan'] > 0;
                        $kepedulian   = $punyaHp && $sudahBuka
                            ? ['label' => 'Peduli', 'cls' => 'success', 'icon' => 'bi-heart-fill']
                            : ($punyaHp || $sudahBuka
                                ? ['label' => 'Sebagian', 'cls' => 'warning', 'icon' => 'bi-heart-half']
                                : ['label' => 'Perlu Perhatian', 'cls' => 'danger', 'icon' => 'bi-heart']
                            );
                    ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/laporan/siswa/<?= $row['id'] ?>" class="text-decoration-none fw-bold text-primary" title="Lihat Performa Siswa">
                                <?= htmlspecialchars($row['nama']) ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <?php if ($punyaHp): ?>
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($row['no_hp']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger">
                                    <i class="bi bi-x-circle me-1"></i> Belum diisi
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($sudahBuka): ?>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <i class="bi bi-eye me-1"></i> <?= $row['total_kunjungan'] ?>x
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                    <i class="bi bi-eye-slash me-1"></i> Belum pernah
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row['kunjungan_terakhir']): ?>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($row['kunjungan_terakhir'])) ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $kepedulian['cls'] ?>">
                                <i class="bi <?= $kepedulian['icon'] ?> me-1"></i><?= $kepedulian['label'] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($punyaHp): ?>
                                <button type="button" class="btn btn-outline-success btn-sm p-1 btn-send-wa" 
                                    data-idsiswa="<?= $row['id'] ?>" 
                                    data-tanggal="<?= date('Y-m-d') ?>" 
                                    data-nohp="<?= htmlspecialchars($row['no_hp']) ?>" 
                                    data-nama="<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>" 
                                    title="Kirim Laporan Hari Ini via WA API">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-send-wa').forEach(btn => {
    btn.addEventListener('click', async function() {
        const idSiswa = this.dataset.idsiswa;
        const tanggal = this.dataset.tanggal;
        const noHp = this.dataset.nohp;
        const nama = this.dataset.nama;
        const originalHtml = this.innerHTML;
        
        if (!confirm(`Yakin ingin mengirim laporan hari ini untuk ananda "${nama}" ke WA ${noHp} (menggunakan format template Anda)?`)) return;
        
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        this.disabled = true;
        
        try {
            const response = await fetch('<?= BASE_URL ?>/absen/kirim_wa_manual', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_siswa: idSiswa,
                    tanggal: tanggal
                })
            });
            const res = await response.json();
            
            if (res.success) {
                this.innerHTML = '<i class="bi bi-check-lg"></i>';
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.add('btn-outline-success');
                    this.classList.remove('btn-success');
                    this.disabled = false;
                }, 2000);
            } else {
                alert('Gagal: ' + (res.message || 'Kesalahan tidak diketahui'));
                this.innerHTML = originalHtml;
                this.disabled = false;
            }
        } catch (error) {
            alert('Terjadi kesalahan jaringan.');
            this.innerHTML = originalHtml;
            this.disabled = false;
        }
    });
});

(function () {
    const table   = document.getElementById('tblTracking');
    const tbody   = table.querySelector('tbody');
    const headers = table.querySelectorAll('th.sortable');
    let lastCol   = -1;
    let asc       = true;

    headers.forEach(th => {
        th.addEventListener('click', () => {
            const col = parseInt(th.dataset.col);
            asc = (col === lastCol) ? !asc : true;
            lastCol = col;

            // Update icon
            headers.forEach(h => {
                const ic = h.querySelector('i');
                ic.className = 'bi bi-arrow-down-up text-muted small';
            });
            const icon = th.querySelector('i');
            icon.className = asc
                ? 'bi bi-sort-down text-primary small'
                : 'bi bi-sort-up text-primary small';

            // Sort rows
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const va = a.cells[col]?.innerText.trim().toLowerCase() ?? '';
                const vb = b.cells[col]?.innerText.trim().toLowerCase() ?? '';

                // Numeric sort for columns 0 and 3
                if (col === 0 || col === 3) {
                    const na = parseFloat(va.replace(/[^\d.]/g, '')) || 0;
                    const nb = parseFloat(vb.replace(/[^\d.]/g, '')) || 0;
                    return asc ? na - nb : nb - na;
                }

                // Date sort for column 4
                if (col === 4) {
                    // Convert "dd Mon yyyy hh:mm" ke timestamp sortable
                    const da = va === '—' ? 0 : new Date(va.replace(/(\d+) (\w+) (\d+)/, '$2 $1 $3')).getTime() || 0;
                    const db = vb === '—' ? 0 : new Date(vb.replace(/(\d+) (\w+) (\d+)/, '$2 $1 $3')).getTime() || 0;
                    return asc ? da - db : db - da;
                }

                // Text sort
                return asc ? va.localeCompare(vb) : vb.localeCompare(va);
            });

            // Re-number column 0 after sort
            rows.forEach((row, idx) => {
                row.cells[0].textContent = idx + 1;
                tbody.appendChild(row);
            });
        });
    });
})();
</script>
