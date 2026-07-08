/**
 * Smart Laundry Level 1 - Main JavaScript
 * Versi Simple & Stabil
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Smart Laundry Ready!');

    // Auto-hide flash messages setelah 5 detik
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

    // Konfirmasi tombol hapus
    document.querySelectorAll('.btn-danger, [onclick*="confirm"]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (this.href && this.href.indexOf('delete') > -1) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            }
        });
    });
});

// Format Rupiah
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Print Struk
function printStruk() {
    window.print();
}