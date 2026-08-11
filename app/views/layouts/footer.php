</main>

<!-- Footer -->
<footer class="footer mt-auto py-3">
    <div class="container-fluid px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="footer-brand">
                <i class="bi bi-mortarboard-fill me-1"></i>
                <strong>Dashboard Wali Kelas</strong>
            </div>
            <div class="footer-text">
                &copy; <?= date('Y') ?> — Sistem Manajemen Presensi Harian Santri
                <span class="mx-2 text-muted">|</span>
                <a href="<?= BASE_URL ?>/feedback" class="text-decoration-none text-secondary hover-primary">
                    <i class="bi bi-chat-heart"></i> Kirim Feedback
                </a>
            </div>
            <div class="footer-time" id="footerClock">
                <i class="bi bi-clock me-1"></i>
                <span id="clockDisplay"></span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/public/js/app.js"></script>

</body>
</html>
