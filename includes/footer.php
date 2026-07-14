<?php if (isLoggedIn()): ?>
            </div>
        </main>
    </div>
<?php endif; ?>
<script>
function toggleTheme(){const h=document.documentElement;const c=h.getAttribute('data-theme');const n=c==='dark'?'light':'dark';h.setAttribute('data-theme',n);localStorage.setItem('theme',n)}
const savedTheme=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',savedTheme);
setTimeout(()=>{document.querySelectorAll('[style*="background: #D1FAE5"], [style*="background: #FEE2E2"]').forEach(el=>{el.style.opacity='0';el.style.transition='opacity 0.5s';setTimeout(()=>el.remove(),500)})},5000);
document.querySelectorAll('[href*="delete"]').forEach(btn=>{btn.addEventListener('click',e=>{if(!confirm('Yakin ingin menghapus?'))e.preventDefault()})});
function formatRupiah(a){return'Rp '+new Intl.NumberFormat('id-ID').format(a)}
function printStruk(){window.print()}
</script>
</body>
</html>