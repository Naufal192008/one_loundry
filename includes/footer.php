            </div><!-- /.content-area -->
        </main><!-- /.main-content -->
    </div><!-- /.app-container -->

    <script src="/laundry_lvl1/assets/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-danger[href*="delete"]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        });
        setTimeout(function() {
            var msgs = document.querySelectorAll('[style*="background: #D1FAE5"], [style*="background: #FEE2E2"]');
            msgs.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s ease';
                msg.style.opacity = '0';
                setTimeout(function() {
                    if (msg.parentNode) msg.remove();
                }, 500);
            });
        }, 5000);
    });
    </script>
</body>
</html>