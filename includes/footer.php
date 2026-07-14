<?php if (isLoggedIn()): ?>
            </div>
        </main>
    </div>
<?php endif; ?>

<script>
function toggleTheme(){var h=document.documentElement;var c=h.getAttribute('data-theme');var n=c==='dark'?'light':'dark';h.setAttribute('data-theme',n);localStorage.setItem('theme',n)}
(function(){var s=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',s)})();
setTimeout(function(){var m=document.querySelectorAll('[style*="background: #D1FAE5"], [style*="background: #FEE2E2"]');m.forEach(function(e){e.style.opacity='0';e.style.transition='opacity 0.5s';setTimeout(function(){if(e.parentNode)e.remove()},500)})},5000);
document.querySelectorAll('[href*="delete"]').forEach(function(b){b.addEventListener('click',function(e){if(!confirm('Yakin hapus?'))e.preventDefault()})});
function formatRupiah(a){return'Rp '+a.toLocaleString('id-ID')}
function printStruk(){window.print()}
console.log('LaundryKu Ready!');
</script>
</body>
</html>