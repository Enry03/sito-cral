{{--
    Movimento della pagina. È scritto qui (e non nel file JS del sito) di proposito:
    così menu, comparsa dei contenuti e header funzionano anche se il resto del
    JavaScript non si carica.
--}}
<script>
(function () {
    var d = document, root = d.documentElement;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function start() {
        try {
            // --- header e parallasse allo scorrimento
            var ticking = false;
            var revealTick = function () {};
            var par = d.querySelectorAll('[data-parallax]');

            function frame() {
                var y = window.scrollY || window.pageYOffset;
                root.setAttribute('data-scrolled', y > 16 ? 'true' : 'false');

                if (!reduce && y < 1400) {
                    for (var i = 0; i < par.length; i++) {
                        var v = parseFloat(par[i].getAttribute('data-parallax')) || 0;
                        par[i].style.transform = 'translate3d(0,' + (y * v).toFixed(1) + 'px,0)';
                    }
                }
                revealTick();
                ticking = false;
            }

            function onScroll() {
                if (!ticking) { ticking = true; window.requestAnimationFrame(frame); }
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            frame();

            // --- menu da telefono
            var toggles = d.querySelectorAll('[data-menu-toggle]');

            function setMenu(open) {
                root.setAttribute('data-menu', open ? 'open' : 'closed');
                for (var i = 0; i < toggles.length; i++) { toggles[i].setAttribute('aria-expanded', open ? 'true' : 'false'); }
            }

            for (var t = 0; t < toggles.length; t++) {
                toggles[t].addEventListener('click', function () { setMenu(root.getAttribute('data-menu') !== 'open'); });
            }
            d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setMenu(false); } });
            var links = d.querySelectorAll('.mobile-menu a');
            for (var l = 0; l < links.length; l++) { links[l].addEventListener('click', function () { setMenu(false); }); }

            // --- menu utente (details) si chiude cliccando fuori
            d.addEventListener('click', function (e) {
                var aperti = d.querySelectorAll('details[data-dropdown][open]');
                for (var i = 0; i < aperti.length; i++) { if (!aperti[i].contains(e.target)) { aperti[i].removeAttribute('open'); } }
            });

            // --- contenuti che compaiono mentre si scorre
            var els = d.querySelectorAll('[data-reveal]');
            var groups = d.querySelectorAll('[data-stagger]');
            for (var g = 0; g < groups.length; g++) {
                var step = parseFloat(groups[g].getAttribute('data-stagger')) || 0.08;
                var kids = groups[g].querySelectorAll(':scope > [data-reveal]');
                for (var k = 0; k < kids.length; k++) { kids[k].style.setProperty('--d', (k * step).toFixed(2) + 's'); }
            }
            for (var s = 0; s < els.length; s++) {
                var delay = els[s].getAttribute('data-delay');
                if (delay) { els[s].style.setProperty('--d', delay + 's'); }
            }

            // Si mostra ciò che è entrato nello schermo. Un controllo semplice allo scorrimento
            // (e subito dopo il caricamento) funziona ovunque, anche nei browser più datati.
            function mostraVisibili() {
                var h = window.innerHeight || root.clientHeight;
                for (var n = 0; n < els.length; n++) {
                    var el = els[n];
                    if (el.classList.contains('is-in')) { continue; }
                    var r = el.getBoundingClientRect();
                    if (reduce || (r.top < h * 0.94 && r.bottom > 0)) { el.classList.add('is-in'); }
                }
            }

            revealTick = mostraVisibili;
            window.addEventListener('resize', mostraVisibili);
            mostraVisibili();
            window.setTimeout(mostraVisibili, 300);
            window.setTimeout(mostraVisibili, 1200);
        } catch (err) {
            root.classList.remove('js');
        }
    }

    if (d.readyState === 'loading') { d.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
</script>
