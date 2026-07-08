<?php
// ============================================
// includes/footer.php - Level 1
// Penutup HTML + JavaScript inline
// ============================================
if (isset($_SESSION['user_id'])):
?>
            </div><!-- /.content-area -->
        </main><!-- /.main-content -->
    </div><!-- /.app-container -->
<?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Smart Laundry Ready!');
        setTimeout(function() {
            var msgs = document.querySelectorAll('[style*="background: #D1FAE5"], [style*="background: #FEE2E2"]');
            msgs.forEach(function(msg) {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    if (msg.parentNode) msg.remove();
                }, 500);
            });
        }, 5000);
    });
    function formatRupiah(a) { 
        return 'Rp ' + a.toLocaleString('id-ID'); 
    }
    function printStruk() { 
        window.print(); 
    }
    </script>
</body>
</html>