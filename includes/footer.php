<?php
// ============================================
// includes/footer.php - LaundryKu Level 5
// Penutup HTML + Semua JavaScript Functions
// ============================================

if (isLoggedIn()):
?>
            </div><!-- /.content-area -->
        </main><!-- /.main-content -->
    </div><!-- /.app-container -->
<?php endif; ?>

<!-- ============================================ -->
<!-- JAVASCRIPT - ALL FUNCTIONS -->
<!-- ============================================ -->
<script>
// ============================================
// THEME TOGGLE (Dark/Light Mode)
// ============================================
function toggleTheme() {
    var html = document.documentElement;
    var current = html.getAttribute('data-theme');
    var next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    console.log('Theme changed to: ' + next);
}

// Load saved theme on page load
(function() {
    var savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    console.log('Theme loaded: ' + savedTheme);
})();

// ============================================
// SIDEBAR TOGGLE (Mobile)
// ============================================
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Close sidebar when clicking outside (mobile)
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('sidebar');
    var menuBtn = document.getElementById('mobile-menu-btn');
    if (sidebar && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && e.target !== menuBtn) {
            sidebar.classList.remove('open');
        }
    }
});

// ============================================
// AUTO-HIDE FLASH MESSAGES
// ============================================
setTimeout(function() {
    var messages = document.querySelectorAll('[style*="background: #D1FAE5"], [style*="background: #FEE2E2"], [style*="background: #FEF3C7"]');
    messages.forEach(function(msg) {
        msg.style.opacity = '0';
        msg.style.transition = 'opacity 0.5s ease';
        setTimeout(function() {
            if (msg.parentNode) {
                msg.remove();
            }
        }, 500);
    });
}, 5000);

// ============================================
// CONFIRM DIALOGS (Delete Buttons)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // All delete links
    var deleteButtons = document.querySelectorAll('[href*="delete"], .btn-danger');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('⚠️ Apakah Anda yakin ingin menghapus data ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // All confirm buttons
    var confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var message = this.getAttribute('data-confirm') || 'Apakah Anda yakin?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Search input - submit on Enter
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var url = new URL(window.location.href);
                url.searchParams.set('search', this.value);
                window.location.href = url.toString();
            }
        });
    }

    console.log('✅ LaundryKu Level 5 - All modules loaded!');
    console.log('📊 Dashboard ready');
    console.log('🎨 Theme: ' + (localStorage.getItem('theme') || 'light'));
    console.log('💡 Tips: Klik 🌓 untuk ganti tema');
    console.log('💡 Tips: Ctrl+P untuk print struk');
});

// ============================================
// FORMAT RUPIAH
// ============================================
function formatRupiah(amount) {
    if (amount === null || amount === undefined) return 'Rp 0';
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
}

// ============================================
// FORMAT NUMBER
// ============================================
function formatNumber(amount, decimals) {
    decimals = decimals || 0;
    return parseFloat(amount).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// ============================================
// PRINT STRUK
// ============================================
function printStruk() {
    window.print();
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================
document.addEventListener('keydown', function(e) {
    // Ctrl+P = Print
    if (e.ctrlKey && e.key === 'p') {
        // Biarkan default print handler bekerja
    }
    
    // Ctrl+N = New Transaction
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        var newBtn = document.querySelector('a[href*="create.php"]');
        if (newBtn) {
            window.location.href = newBtn.href;
        }
    }
    
    // Escape = Close modals
    if (e.key === 'Escape') {
        var modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(function(modal) {
            modal.style.display = 'none';
        });
    }
});

// ============================================
// TOAST NOTIFICATION
// ============================================
function showToast(message, type) {
    type = type || 'success';
    var colors = {
        success: { bg: '#D1FAE5', color: '#065F46', icon: '✅' },
        error: { bg: '#FEE2E2', color: '#991B1B', icon: '❌' },
        warning: { bg: '#FEF3C7', color: '#92400E', icon: '⚠️' },
        info: { bg: '#DBEAFE', color: '#1E40AF', icon: 'ℹ️' }
    };
    var style = colors[type] || colors.info;
    
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;background:' + style.bg + ';color:' + style.color + ';padding:14px 20px;border-radius:10px;font-weight:600;font-size:14px;z-index:10001;box-shadow:0 4px 16px rgba(0,0,0,0.1);max-width:400px;display:flex;align-items:center;gap:10px;animation:slideInRight 0.3s ease;';
    toast.innerHTML = '<span>' + style.icon + '</span> ' + message;
    
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(function() {
            if (toast.parentNode) toast.remove();
        }, 300);
    }, 3000);
}

// ============================================
// LOADING STATE
// ============================================
function showLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (!element) return;
    
    element.setAttribute('data-original-text', element.textContent);
    element.disabled = true;
    element.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #ccc;border-top-color:#6366F1;border-radius:50%;animation:spin 0.6s linear infinite;margin-right:8px;"></span> Loading...';
}

function hideLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (!element) return;
    
    element.disabled = false;
    var originalText = element.getAttribute('data-original-text') || 'Submit';
    element.textContent = originalText;
}

// ============================================
// EXPORT TABLE TO CSV
// ============================================
function exportTableToCSV(tableElement, filename) {
    filename = filename || 'export.csv';
    var table = typeof tableElement === 'string' ? document.querySelector(tableElement) : tableElement;
    if (!table) return;
    
    var csv = [];
    var rows = table.querySelectorAll('tr');
    
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('td, th');
        var rowData = [];
        cols.forEach(function(col) {
            var text = col.textContent.trim();
            text = text.replace(/"/g, '""');
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                text = '"' + text + '"';
            }
            rowData.push(text);
        });
        csv.push(rowData.join(','));
    });
    
    var csvString = '\uFEFF' + csv.join('\n');
    var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}

// ============================================
// ADDITIONAL CSS ANIMATIONS
// ============================================
var styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
`;
document.head.appendChild(styleSheet);

// ============================================
// GLOBAL EXPORTS
// ============================================
window.formatRupiah = formatRupiah;
window.formatNumber = formatNumber;
window.showToast = showToast;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.exportTableToCSV = exportTableToCSV;
window.toggleTheme = toggleTheme;
window.toggleSidebar = toggleSidebar;
window.printStruk = printStruk;

console.log('🚀 LaundryKu Level 5 - All systems ready!');
</script>

</body>
</html>