        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Sidebar overlay for mobile -->
<div id="sidebarOverlay" onclick="toggleSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('adminSidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const isOpen   = sidebar.classList.toggle('open');
        overlay.style.display = isOpen ? 'block' : 'none';
    }
</script>
</body>
</html>
