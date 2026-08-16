<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0"><i class="bi bi-whatsapp text-success me-2"></i> Pengaturan Template Pesan WA</h2>
        </div>
        <p class="text-muted mt-1">Atur format pesan otomatis yang akan dikirimkan ke wali murid ketika absen disubmit.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/watemplate/simpan" method="POST">
                    <div class="mb-3">
                        <label for="template" class="form-label fw-bold">Isi Pesan WhatsApp</label>
                        <textarea class="form-control" id="template" name="template" rows="15" required><?= htmlspecialchars($data["template"]) ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembalikan Default
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body">
                <h5 class="card-title fw-bold fs-6 mb-3"><i class="bi bi-info-circle me-1"></i> Variabel Dinamis</h5>
                <p class="small text-muted mb-3">Anda dapat menggunakan kode berikut di dalam template pesan Anda. Kode ini akan otomatis diganti dengan data siswa saat pesan dikirim:</p>
                
                <ul class="list-group list-group-flush small mb-0 rounded">
                    <li class="list-group-item bg-transparent px-2 py-2">
                        <code class="fw-bold">{nama_siswa}</code>
                        <div class="text-muted" style="font-size: 0.8rem;">Nama lengkap siswa (contoh: Budi Santoso)</div>
                    </li>
                    <li class="list-group-item bg-transparent px-2 py-2">
                        <code class="fw-bold">{tanggal}</code>
                        <div class="text-muted" style="font-size: 0.8rem;">Tanggal absen (contoh: 17 Agustus 2026)</div>
                    </li>
                    <li class="list-group-item bg-transparent px-2 py-2">
                        <code class="fw-bold">{rincian}</code>
                        <div class="text-muted" style="font-size: 0.8rem;">Daftar pertanyaan & jawaban dari form absen</div>
                    </li>
                    <li class="list-group-item bg-transparent px-2 py-2">
                        <code class="fw-bold">{rating}</code>
                        <div class="text-muted" style="font-size: 0.8rem;">Rating harian siswa (contoh: 4)</div>
                    </li>
                    <li class="list-group-item bg-transparent px-2 py-2">
                        <code class="fw-bold">{link_laporan}</code>
                        <div class="text-muted" style="font-size: 0.8rem;">Tautan langsung ke halaman laporan wali murid (otomatis login)</div>
                    </li>
                </ul>
                <hr>
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Pastikan untuk selalu menyertakan <b>{rincian}</b> agar wali murid dapat melihat detail laporan harian.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const defaultTemplate = <?= json_encode($data["defaultTemplate"]) ?>;
    
    function resetToDefault() {
        if (confirm("Anda yakin ingin mengembalikan template ke pengaturan awal? Perubahan Anda saat ini akan hilang.")) {
            document.getElementById("template").value = defaultTemplate;
        }
    }
</script>
