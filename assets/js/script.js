// Auto-dismiss alert setelah 3 detik
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        });
    }, 3000);
});

// Konfeti untuk halaman sukses
function launchConfetti() {
    const colors = ['#cc0000', '#ff4444', '#ffaa00', '#333', '#888'];
    for (var i = 0; i < 50; i++) {
        (function (i) {
            setTimeout(function () {
                var s = document.createElement('span');
                var sz = Math.random() * 8 + 5;
                s.style.cssText = 'position:fixed;top:-10px;left:' + (Math.random() * 100) + 'vw;'
                    + 'width:' + sz + 'px;height:' + sz + 'px;'
                    + 'background:' + colors[Math.floor(Math.random() * colors.length)] + ';'
                    + 'border-radius:' + (Math.random() > .5 ? '50%' : '2px') + ';'
                    + 'animation:fall ' + (Math.random() * 2 + 2) + 's linear forwards;'
                    + 'pointer-events:none;z-index:999';
                document.body.appendChild(s);
                setTimeout(function () { s.remove(); }, 4500);
            }, i * 60);
        })(i);
    }
}

// Preview nominal donasi
function updatePreview() {
    var n = parseInt(document.getElementById('inp-jumlah')?.value) || 0;
    var el = document.getElementById('preview-jml');
    if (el) el.textContent = n > 0 ? 'Rp ' + n.toLocaleString('id-ID') : '—';
}
