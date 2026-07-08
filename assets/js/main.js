document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initAutoHideAlert();
    initConfirmDialogs();
    initPrintButtons();
    initNumberFormatting();
    initKanbanDragDrop();
    initSearchAutocomplete();
    initResponsiveSidebar();
    initClockDisplay();
    console.log('🚀 Smart Laundry Level 1 - Ready!');
});

// ============================================
// TOOLTIPS
// ============================================
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: #1E293B;
                color: white;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 500;
                z-index: 9999;
                white-space: nowrap;
                pointer-events: none;
                animation: fadeIn 0.2s ease;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// ============================================
// AUTO HIDE ALERT / FLASH MESSAGE
// ============================================
function initAutoHideAlert() {
    const alerts = document.querySelectorAll('.alert, [style*="background: #D1FAE5"], [style*="background: #FEE2E2"], [style*="background: #FEF3C7"]');
    
    alerts.forEach(alert => {
        // Auto hide setelah 5 detik
        setTimeout(() => {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }, 5000);
        
        // Tambah tombol close
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('span');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = `
                float: right;
                cursor: pointer;
                font-size: 18px;
                font-weight: bold;
                margin-left: 12px;
                opacity: 0.7;
                transition: opacity 0.2s;
            `;
            closeBtn.addEventListener('click', function() {
                alert.style.transition = 'all 0.3s ease';
                alert.style.opacity = '0';
                alert.style.maxHeight = '0';
                alert.style.padding = '0';
                alert.style.margin = '0';
                alert.style.overflow = 'hidden';
                setTimeout(() => alert.remove(), 300);
            });
            closeBtn.addEventListener('mouseenter', function() {
                this.style.opacity = '1';
            });
            closeBtn.addEventListener('mouseleave', function() {
                this.style.opacity = '0.7';
            });
            alert.appendChild(closeBtn);
        }
    });
}

// ============================================
// CONFIRM DIALOGS
// ============================================
function initConfirmDialogs() {
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Apakah Anda yakin?';
            const title = this.dataset.confirmTitle || 'Konfirmasi';
            
            if (!confirm(title + '\n\n' + message)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    });
}

/**
 * Custom confirm dialog (bisa dipanggil manual)
 */
function showConfirm(message, title = 'Konfirmasi') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.2s ease;
        `;
        
        const dialog = document.createElement('div');
        dialog.style.cssText = `
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: slideUp 0.3s ease;
        `;
        
        dialog.innerHTML = `
            <h3 style="margin-bottom: 12px; font-size: 18px; font-weight: 700;">${title}</h3>
            <p style="margin-bottom: 24px; color: #64748B; font-size: 14px;">${message}</p>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button class="btn-cancel" style="
                    padding: 10px 20px;
                    border: 2px solid #E2E8F0;
                    background: white;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: 600;
                    font-size: 14px;
                    color: #64748B;
                ">Batal</button>
                <button class="btn-confirm" style="
                    padding: 10px 20px;
                    background: #1D4ED8;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: 600;
                    font-size: 14px;
                ">Ya, Lanjutkan</button>
            </div>
        `;
        
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        
        dialog.querySelector('.btn-cancel').addEventListener('click', () => {
            overlay.remove();
            resolve(false);
        });
        
        dialog.querySelector('.btn-confirm').addEventListener('click', () => {
            overlay.remove();
            resolve(true);
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                resolve(false);
            }
        });
    });
}

// ============================================
// PRINT FUNCTIONALITY
// ============================================
function initPrintButtons() {
    // Semua tombol dengan onclick="window.print()" sudah otomatis bekerja
    // Tambahan: styling print
    const printStyle = document.createElement('style');
    printStyle.id = 'print-styles';
    printStyle.textContent = `
        @media print {
            .sidebar, .top-bar, .btn, .no-print, .modal-overlay, 
            form, .nav-item, .btn-logout, .search-bar, .stats-grid {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .content-area {
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .invoice-container {
                border: 2px solid #000 !important;
                padding: 20px !important;
                max-width: 100% !important;
            }
            body {
                background: white !important;
                font-size: 12pt !important;
            }
            @page {
                margin: 1cm;
                size: A4;
            }
        }
    `;
    document.head.appendChild(printStyle);
}

// ============================================
// NUMBER FORMATTING (Rupiah)
// ============================================
function initNumberFormatting() {
    document.querySelectorAll('.format-rupiah').forEach(element => {
        const value = parseFloat(element.textContent) || 0;
        element.textContent = formatRupiah(value);
    });
}

function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

/**
 * Format input angka saat diketik
 */
function setupNumericInput(inputElement, decimalPlaces = 2) {
    inputElement.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Hanya boleh satu titik desimal
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Batasi angka desimal
        if (parts.length === 2 && parts[1].length > decimalPlaces) {
            value = parts[0] + '.' + parts[1].substring(0, decimalPlaces);
        }
        
        this.value = value;
    });
}

// Setup semua input number
document.querySelectorAll('input[type="number"]').forEach(input => {
    setupNumericInput(input);
});

// ============================================
// KANBAN DRAG & DROP (Sederhana)
// ============================================
function initKanbanDragDrop() {
    const kanbanCards = document.querySelectorAll('.kanban-card');
    const kanbanColumns = document.querySelectorAll('.kanban-column');
    
    if (kanbanCards.length === 0 || kanbanColumns.length === 0) return;
    
    kanbanCards.forEach(card => {
        card.setAttribute('draggable', 'true');
        
        card.addEventListener('dragstart', function(e) {
            this.style.opacity = '0.5';
            this.style.transform = 'scale(0.95)';
            e.dataTransfer.setData('text/plain', this.dataset.id || '');
        });
        
        card.addEventListener('dragend', function() {
            this.style.opacity = '1';
            this.style.transform = 'scale(1)';
        });
    });
    
    kanbanColumns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.background = '#E2E8F0';
            this.style.transition = 'background 0.2s ease';
        });
        
        column.addEventListener('dragleave', function() {
            this.style.background = '';
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.background = '';
            
            const cardId = e.dataTransfer.getData('text/plain');
            const card = document.querySelector(`[data-id="${cardId}"]`);
            
            if (card && this !== card.parentElement) {
                this.appendChild(card);
                
                // Trigger event untuk update status (bisa di-custom)
                const event = new CustomEvent('kanbanDrop', {
                    detail: {
                        cardId: cardId,
                        newColumn: this.dataset.status || ''
                    }
                });
                document.dispatchEvent(event);
            }
        });
    });
}

// ============================================
// SEARCH AUTOCOMPLETE
// ============================================
function initSearchAutocomplete() {
    const searchInputs = document.querySelectorAll('.search-input[data-autocomplete]');
    
    searchInputs.forEach(input => {
        let debounceTimer;
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'autocomplete-results';
        resultsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #E2E8F0;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        `;
        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(resultsContainer);
        
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                const url = this.dataset.autocomplete + '?q=' + encodeURIComponent(query);
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        
                        if (data.length === 0) {
                            resultsContainer.innerHTML = '<div style="padding: 12px; color: #94A3B8; text-align: center;">Tidak ditemukan</div>';
                        } else {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.style.cssText = `
                                    padding: 10px 14px;
                                    cursor: pointer;
                                    transition: background 0.2s;
                                    border-bottom: 1px solid #F1F5F9;
                                `;
                                div.textContent = item.name || item.label || item;
                                div.addEventListener('click', () => {
                                    input.value = div.textContent;
                                    resultsContainer.style.display = 'none';
                                    if (input.dataset.onSelect) {
                                        window[input.dataset.onSelect](item);
                                    }
                                });
                                div.addEventListener('mouseenter', function() {
                                    this.style.background = '#F1F5F9';
                                });
                                div.addEventListener('mouseleave', function() {
                                    this.style.background = '';
                                });
                                resultsContainer.appendChild(div);
                            });
                        }
                        
                        resultsContainer.style.display = 'block';
                    })
                    .catch(() => {
                        resultsContainer.style.display = 'none';
                    });
            }, 300);
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.parentElement.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
        
        // Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = resultsContainer.querySelectorAll('div');
            const active = resultsContainer.querySelector('.active');
            let index = -1;
            
            if (active) {
                items.forEach((item, i) => {
                    if (item === active) index = i;
                });
            }
            
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (index < items.length - 1) {
                        if (active) active.classList.remove('active');
                        items[index + 1].classList.add('active');
                        items[index + 1].style.background = '#EFF6FF';
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (index > 0) {
                        if (active) active.classList.remove('active');
                        items[index - 1].classList.add('active');
                        items[index - 1].style.background = '#EFF6FF';
                    }
                    break;
                case 'Enter':
                    if (active) {
                        e.preventDefault();
                        active.click();
                    }
                    break;
                case 'Escape':
                    resultsContainer.style.display = 'none';
                    break;
            }
        });
    });
}

// ============================================
// RESPONSIVE SIDEBAR TOGGLE
// ============================================
function initResponsiveSidebar() {
    // Buat tombol hamburger untuk mobile
    if (window.innerWidth <= 768) {
        createMobileMenuButton();
    }
    
    window.addEventListener('resize', function() {
        const existingBtn = document.getElementById('mobile-menu-btn');
        if (window.innerWidth <= 768 && !existingBtn) {
            createMobileMenuButton();
        } else if (window.innerWidth > 768 && existingBtn) {
            existingBtn.remove();
            document.querySelector('.sidebar')?.classList.remove('sidebar-open');
        }
    });
}

function createMobileMenuButton() {
    const topBar = document.querySelector('.top-bar-left');
    if (!topBar) return;
    
    const menuBtn = document.createElement('button');
    menuBtn.id = 'mobile-menu-btn';
    menuBtn.innerHTML = '☰';
    menuBtn.style.cssText = `
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        margin-right: 12px;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background 0.2s;
    `;
    menuBtn.addEventListener('click', toggleSidebar);
    menuBtn.addEventListener('mouseenter', function() {
        this.style.background = '#F1F5F9';
    });
    menuBtn.addEventListener('mouseleave', function() {
        this.style.background = 'none';
    });
    
    topBar.insertBefore(menuBtn, topBar.firstChild);
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('sidebar-open');
        
        if (sidebar.classList.contains('sidebar-open')) {
            // Buat overlay
            if (!overlay) {
                const newOverlay = document.createElement('div');
                newOverlay.id = 'sidebar-overlay';
                newOverlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 99;
                    animation: fadeIn 0.3s ease;
                `;
                newOverlay.addEventListener('click', toggleSidebar);
                document.body.appendChild(newOverlay);
            }
        } else {
            if (overlay) overlay.remove();
        }
    }
}

// ============================================
// CLOCK DISPLAY (Opsional)
// ============================================
function initClockDisplay() {
    const clockElement = document.getElementById('live-clock');
    if (!clockElement) return;
    
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        clockElement.textContent = now.toLocaleDateString('id-ID', options);
    }
    
    updateClock();
    setInterval(updateClock, 1000);
}

// ============================================
// FORM VALIDATION HELPERS
// ============================================
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        const errorElement = input.nextElementSibling?.classList.contains('error-message') 
            ? input.nextElementSibling 
            : null;
        
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#EF4444';
            input.style.background = '#FEF2F2';
            
            if (!errorElement) {
                const error = document.createElement('small');
                error.className = 'error-message';
                error.style.cssText = 'color: #EF4444; font-size: 12px; margin-top: 4px; display: block;';
                error.textContent = input.dataset.errorMessage || 'Field ini wajib diisi';
                input.parentElement.appendChild(error);
            }
        } else {
            input.style.borderColor = '#10B981';
            input.style.background = 'white';
            if (errorElement) errorElement.remove();
        }
    });
    
    return isValid;
}

// ============================================
// NOTIFICATION / TOAST
// ============================================
function showToast(message, type = 'success', duration = 3000) {
    const colors = {
        success: { bg: '#D1FAE5', color: '#065F46', border: '#A7F3D0', icon: '✅' },
        error: { bg: '#FEE2E2', color: '#991B1B', border: '#FECACA', icon: '❌' },
        warning: { bg: '#FEF3C7', color: '#92400E', border: '#FDE68A', icon: '⚠️' },
        info: { bg: '#DBEAFE', color: '#1E40AF', border: '#BFDBFE', icon: 'ℹ️' }
    };
    
    const style = colors[type] || colors.info;
    
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${style.bg};
        color: ${style.color};
        padding: 14px 20px;
        border-radius: 10px;
        border-left: 4px solid ${style.border};
        font-weight: 600;
        font-size: 14px;
        z-index: 10001;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        animation: slideInRight 0.3s ease;
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    toast.innerHTML = `<span>${style.icon}</span> ${message}`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ============================================
// LOADING STATE
// ============================================
function showLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (!element) return;
    
    const originalText = element.textContent;
    element.dataset.originalText = originalText;
    element.disabled = true;
    element.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;"></span> Loading...';
}

function hideLoading(element) {
    if (typeof element === 'string') {
        element = document.querySelector(element);
    }
    if (!element) return;
    
    element.disabled = false;
    element.textContent = element.dataset.originalText || 'Submit';
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter = Submit form
    if (e.ctrlKey && e.key === 'Enter') {
        const form = document.querySelector('form:focus-within');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    }
    
    // Escape = Close modal
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal-overlay[style*="display: flex"]');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
    
    // Ctrl+N = New transaction (jika di halaman transaksi)
    if (e.ctrlKey && e.key === 'n') {
        const newBtn = document.querySelector('a[href*="create.php"]');
        if (newBtn) {
            e.preventDefault();
            window.location.href = newBtn.href;
        }
    }
});

// ============================================
// EXPORT TO CSV (UNTUK LAPORAN)
// ============================================
function exportTableToCSV(tableElement, filename = 'export.csv') {
    const table = typeof tableElement === 'string' 
        ? document.querySelector(tableElement) 
        : tableElement;
    
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            // Escape quotes dan koma
            let text = col.textContent.trim();
            text = text.replace(/"/g, '""');
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                text = '"' + text + '"';
            }
            rowData.push(text);
        });
        csv.push(rowData.join(','));
    });
    
    const csvString = csv.join('\n');
    const blob = new Blob(['\uFEFF' + csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}

// ============================================
// ADDITIONAL ANIMATIONS
// ============================================
// Animasi CSS tambahan
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* Sidebar mobile */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 100;
        }
        
        .sidebar.sidebar-open {
            transform: translateX(0);
        }
    }
    
    /* Custom tooltip */
    .custom-tooltip {
        pointer-events: none;
    }
    
    /* Autocomplete results */
    .autocomplete-results div.active {
        background: #EFF6FF !important;
    }
    
    /* Print styles */
    @media print {
        .no-print { display: none !important; }
    }
`;
document.head.appendChild(additionalStyles);

// ============================================
// GLOBAL EXPORTS
// ============================================
window.formatRupiah = formatRupiah;
window.showToast = showToast;
window.showConfirm = showConfirm;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.exportTableToCSV = exportTableToCSV;
window.validateForm = validateForm;

console.log('✅ Smart Laundry JS - All modules loaded!');