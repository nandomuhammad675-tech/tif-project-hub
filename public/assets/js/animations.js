/* TIF Hub — Animation layer v2 (vanilla JS, tanpa library)
   Elemen diberi atribut data-reveal otomatis lewat tabel RULES di bawah, jadi view PHP
   tidak perlu diubah satu-satu. Elemen baru mau ikut animasi? Tambah satu baris di RULES,
   atau pasang data-reveal="up|soft|left|right|zoom|pop|blur" langsung di HTML.
   Jeda manual: data-delay="0.3" (detik).
   Pengaman: kalau ada error apa pun, class `anim` dicabut sehingga halaman tetap tampil normal. */
(function () {
    'use strict';

    var html = document.documentElement;
    var doc = document;
    var reduce = !!(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
    var fine = !!(window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches);
    var hasIO = 'IntersectionObserver' in window;

    function $$(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }

    /* ---------- Angka yang menghitung naik ---------- */
    var COUNT_SEL = '[data-count], .hero-stats strong, .stat-card .value, .kpi-value';

    function prepCount(el) {
        var node = null, i;
        for (i = 0; i < el.childNodes.length; i++) {
            if (el.childNodes[i].nodeType === 3 && /\d/.test(el.childNodes[i].nodeValue)) { node = el.childNodes[i]; break; }
        }
        if (!node) return null;
        var m = node.nodeValue.match(/^(\s*)(\d+)(\s*)$/);
        var target = el.hasAttribute('data-count') ? (parseInt(el.getAttribute('data-count'), 10) || 0) : (m ? parseInt(m[2], 10) : NaN);
        if (isNaN(target)) return null;
        return { el: el, node: node, target: target, pre: m ? m[1] : '', post: m ? m[3] : '' };
    }
    function setCount(c, v) { c.node.nodeValue = c.pre + v + c.post; }
    function runCount(c, delay) {
        setTimeout(function () {
            var t0 = null, dur = 1500;
            function step(ts) {
                if (t0 === null) t0 = ts;
                var p = Math.min(1, (ts - t0) / dur);
                setCount(c, Math.round(c.target * (p === 1 ? 1 : 1 - Math.pow(2, -10 * p))));   // easeOutExpo
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }, delay || 0);
    }

    var counts = $$(COUNT_SEL).map(prepCount).filter(Boolean);
    var bars = $$('.progress > i[data-w]');

    function showFinalValues() {
        counts.forEach(function (c) { setCount(c, c.target); });
        bars.forEach(function (b) { b.style.width = b.getAttribute('data-w') + '%'; });
    }

    /* Tanpa animasi (reduced motion / browser lama): tampilkan nilai akhir saja */
    if (reduce || !hasIO) { showFinalValues(); return; }

    /* ---------- Tabel aturan reveal ----------
       [selector, varian, nested?]  nested=true → tetap dianimasikan walau induknya sudah ber-reveal.
       Footer sengaja tidak dianimasikan. */
    var RULES = [
        ['.hero', 'zoom'],
        ['.page-head', 'up'],
        ['.back-link', 'left'],
        ['.section-title', 'left'],
        ['.filter-bar', 'up'],
        ['.project-grid > *', 'up'],
        ['.ticket-grid > *', 'up'],
        ['.stats-grid > *', 'zoom'],
        ['.kpi-grid > *', 'zoom'],
        ['.dash-grid > *', 'up'],
        ['.detail-header > .gallery', 'left'],
        ['.detail-header > div:not(.gallery)', 'right'],
        ['.table-wrap', 'up'],
        ['.card', 'up'],
        ['.empty-state', 'zoom'],
        ['.pagination', 'up'],
        ['.timeline', 'line'],
        ['.timeline-item', 'left', true],
        ['.detail-meta > *', 'pop', true],
        ['.detail-info-list li', 'soft', true],
        ['.gallery-thumbs button', 'zoom', true],
        ['.mini-list li', 'left', true],
        ['.progress-row', 'soft', true],
        ['.form-group:not(.filter-bar .form-group)', 'soft', true],
        ['.form-actions', 'up', true],
        ['tbody tr', 'soft', true]
    ];

    var hero = doc.querySelector('.hero');
    var queue = [];
    var io = null, io2 = null;

    /* ---------- Judul: pecah jadi kata-kata bertopeng ---------- */
    function splitWords(el) {
        var frag = doc.createDocumentFragment(), i = 0;
        Array.prototype.slice.call(el.childNodes).forEach(function (n) {
            if (n.nodeType !== 3) { frag.appendChild(n.cloneNode(true)); return; }
            n.nodeValue.split(/(\s+)/).forEach(function (t) {
                if (!t) return;
                if (/^\s+$/.test(t)) { frag.appendChild(doc.createTextNode(' ')); return; }
                var w = doc.createElement('span'), s = doc.createElement('span');
                w.className = 'w';
                s.style.setProperty('--i', i++);
                s.textContent = t;
                w.appendChild(s);
                frag.appendChild(w);
            });
        });
        el.textContent = '';
        el.appendChild(frag);
    }

    /* ---------- Hero ---------- */
    function initHero() {
        var logo = hero.querySelector('.hero-logo');
        var h1 = hero.querySelector('h1');
        var p = hero.querySelector('p');
        var search = hero.querySelector('.hero-search');
        var stats = hero.querySelector('.hero-stats');

        if (logo) { logo.setAttribute('data-h', 'pop'); logo.style.setProperty('--hd', '.2s'); }
        if (h1) splitWords(h1);
        if (p) { p.setAttribute('data-h', ''); p.style.setProperty('--hd', '.85s'); }
        if (search) { search.setAttribute('data-h', ''); search.style.setProperty('--hd', '1s'); }
        if (stats) { stats.setAttribute('data-h', ''); stats.style.setProperty('--hd', '1.15s'); }

        for (var i = 0; i < 12; i++) {          // gelembung laut
            var b = doc.createElement('span'), size = 6 + Math.random() * 20;
            b.className = 'bubble';
            b.setAttribute('aria-hidden', 'true');
            b.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (Math.random() * 96) + '%;' +
                '--bt:' + (9 + Math.random() * 9) + 's;--bd:-' + (Math.random() * 14) + 's;' +
                '--bx:' + ((Math.random() < .5 ? -1 : 1) * (10 + Math.random() * 22)) + 'px';
            hero.appendChild(b);
        }

        if (fine) {                              // parallax mengikuti mouse
            hero.addEventListener('pointermove', function (e) {
                var r = hero.getBoundingClientRect();
                hero.style.setProperty('--px', (((e.clientX - r.left) / r.width - .5) * 50) + 'px');
                hero.style.setProperty('--py', (((e.clientY - r.top) / r.height - .5) * 50) + 'px');
            });
            hero.addEventListener('pointerleave', function () {
                hero.style.setProperty('--px', '0px');
                hero.style.setProperty('--py', '0px');
            });
        }

        var input = hero.querySelector('.hero-search input');   // placeholder "diketik" otomatis
        if (input) {
            var base = input.getAttribute('placeholder') || '';
            var words = ['Sistem informasi kampus…', 'Aplikasi mobile…', 'Website toko online…', 'PHP, MySQL, JavaScript…'];
            var wi = 0, ci = 0, del = false;
            (function tick() {
                if (doc.activeElement === input || input.value) {
                    input.setAttribute('placeholder', base);
                    ci = 0; del = false;
                    setTimeout(tick, 900);
                    return;
                }
                var w = words[wi], wait = del ? 32 : 70;
                ci += del ? -1 : 1;
                input.setAttribute('placeholder', w.slice(0, ci) + '|');
                if (!del && ci === w.length) { del = true; wait = 1500; }
                else if (del && ci === 0) { del = false; wi = (wi + 1) % words.length; wait = 400; }
                setTimeout(tick, wait);
            })();
        }
    }

    /* ---------- Reveal ---------- */
    function reveal(el, i) {
        if (el.classList.contains('is-in')) return;
        var d = el.hasAttribute('data-delay') ? (parseFloat(el.getAttribute('data-delay')) || 0) : Math.min(i, 9) * 0.08;
        el.style.setProperty('--d', d + 's');
        el.classList.add('is-in');
        // Setelah selesai, lepas atribut supaya properti asli elemen (hover dll) kembali normal
        setTimeout(function () {
            el.removeAttribute('data-reveal');
            el.style.removeProperty('--d');
        }, (d + 1) * 1000 + 100);
    }

    function byPosition(a, b) { return (a.top - b.top) || (a.left - b.left); }

    function revealVisibleNow() {   // semua yang sudah masuk layar tapi belum terpicu
        var vh = window.innerHeight, list = [];
        queue.forEach(function (el) {
            if (!el.hasAttribute('data-reveal') || el.classList.contains('is-in')) return;
            var r = el.getBoundingClientRect();
            if (r.top < vh) list.push({ el: el, top: r.top, left: r.left });
        });
        list.sort(byPosition).forEach(function (o, i) { reveal(o.el, i); if (io) io.unobserve(o.el); });
    }

    function boot() {
        html.classList.add('anim');
        counts.forEach(function (c) { setCount(c, 0); });

        if (hero) initHero();
        $$('.page-head h1').forEach(function (h) {      // judul halaman ikut animasi kata
            var plain = Array.prototype.every.call(h.children, function (c) { return c.tagName === 'BR'; });
            if (plain) splitWords(h);
        });

        RULES.forEach(function (rule) {
            $$(rule[0]).forEach(function (el) {
                if (el.hasAttribute('data-reveal')) return;
                if (!rule[2] && el.parentElement && el.parentElement.closest('[data-reveal]')) return;
                if (el.closest('.lightbox')) return;
                el.setAttribute('data-reveal', rule[1]);
                queue.push(el);
            });
        });
        $$('[data-reveal]').forEach(function (el) { if (queue.indexOf(el) < 0) queue.push(el); });

        // Observer untuk yang di bawah layar
        io = new IntersectionObserver(function (entries) {
            var vis = entries.filter(function (e) { return e.isIntersecting; }).map(function (e) {
                return { el: e.target, top: e.boundingClientRect.top, left: e.boundingClientRect.left };
            });
            vis.sort(byPosition).forEach(function (o, i) { reveal(o.el, i); io.unobserve(o.el); });
        }, { threshold: 0, rootMargin: '0px 0px -50px 0px' });

        // Yang sudah terlihat saat halaman dibuka → langsung diputar berurutan
        var vh = window.innerHeight, first = [];
        queue.forEach(function (el) {
            var r = el.getBoundingClientRect();
            if (r.top < vh - 40) first.push({ el: el, top: r.top, left: r.left });
            else io.observe(el);
        });
        first.sort(byPosition).forEach(function (o, i) { reveal(o.el, i); });

        // Pengaman: apa pun yang sudah di layar tapi masih tersembunyi akan dimunculkan
        setTimeout(revealVisibleNow, 1200);
        setTimeout(revealVisibleNow, 3000);

        /* Angka & progress bar: jalan saat terlihat */
        io2 = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                io2.unobserve(e.target);
                var el = e.target;
                if (el.tagName === 'I' && el.hasAttribute('data-w')) {
                    setTimeout(function () { el.style.width = el.getAttribute('data-w') + '%'; }, 250);
                } else if (el.__cnt) {
                    runCount(el.__cnt, el.closest('.hero') ? 1000 : 150);
                }
            });
        }, { threshold: 0.4 });
        counts.forEach(function (c) { c.el.__cnt = c; io2.observe(c.el); });
        bars.forEach(function (b) { io2.observe(b); });
        // pengaman angka/progress
        setTimeout(function () { showFinalValuesIfStuck(); }, 6000);

        initChrome();
        initInteractions();
    }

    function showFinalValuesIfStuck() {
        counts.forEach(function (c) { if (c.node.nodeValue.trim() === '0' && c.target !== 0 && c.el.getBoundingClientRect().top < window.innerHeight) setCount(c, c.target); });
        bars.forEach(function (b) { if (!b.style.width || b.style.width === '0px' || b.style.width === '0') { if (b.getBoundingClientRect().top < window.innerHeight) b.style.width = b.getAttribute('data-w') + '%'; } });
    }

    /* ---------- Scroll progress, tombol ke atas, navbar, parallax hero, cahaya kursor ---------- */
    function initChrome() {
        var bar = doc.createElement('div');
        bar.className = 'scroll-progress';
        bar.setAttribute('aria-hidden', 'true');
        doc.body.appendChild(bar);

        var topBtn = doc.createElement('button');
        topBtn.type = 'button';
        topBtn.className = 'to-top';
        topBtn.setAttribute('aria-label', 'Kembali ke atas');
        topBtn.innerHTML =
            '<svg class="ring" viewBox="0 0 44 44" aria-hidden="true"><circle class="trk" cx="22" cy="22" r="19"/><circle class="bar" cx="22" cy="22" r="19"/></svg>' +
            '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
        doc.body.appendChild(topBtn);
        var ring = topBtn.querySelector('.bar');
        topBtn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

        var nav = doc.querySelector('.navbar');
        var lastY = window.pageYOffset || 0, ticking = false;

        function update(fromScroll) {
            ticking = false;
            var y = window.pageYOffset || 0;
            var max = Math.max(1, html.scrollHeight - window.innerHeight);
            var p = Math.min(1, Math.max(0, y / max));
            bar.style.transform = 'scaleX(' + p + ')';
            ring.style.strokeDashoffset = (119.4 * (1 - p)).toFixed(2);
            topBtn.classList.toggle('show', y > 520);

            if (nav) {
                nav.classList.toggle('is-scrolled', y > 8);
                if (y > 260 && y > lastY + 6 && !nav.contains(doc.activeElement)) nav.classList.add('is-hidden');
                else if (y < lastY - 6 || y <= 260) nav.classList.remove('is-hidden');
            }
            if (fromScroll === true && y > 0 && y + window.innerHeight >= html.scrollHeight - 4) revealVisibleNow();
            if (hero && y < hero.offsetHeight + 400) hero.style.setProperty('--sy', (y * 0.18) + 'px');
            lastY = y;
        }
        function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(function () { update(true); }); } }
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        update(false);

        // Cahaya lembut yang mengikuti kursor
        if (fine) {
            var glow = doc.createElement('div');
            glow.className = 'cursor-glow';
            glow.setAttribute('aria-hidden', 'true');
            doc.body.appendChild(glow);
            var gx = -999, gy = -999, tx = gx, ty = gy, running = false;
            var loop = function () {
                gx += (tx - gx) * .12; gy += (ty - gy) * .12;
                glow.style.transform = 'translate3d(' + gx.toFixed(1) + 'px,' + gy.toFixed(1) + 'px,0)';
                if (Math.abs(tx - gx) > .5 || Math.abs(ty - gy) > .5) requestAnimationFrame(loop); else running = false;
            };
            doc.addEventListener('pointermove', function (e) {
                tx = e.clientX; ty = e.clientY;
                if (!glow.classList.contains('on')) { gx = tx; gy = ty; glow.classList.add('on'); }
                if (!running) { running = true; requestAnimationFrame(loop); }
            }, { passive: true });
            doc.documentElement.addEventListener('mouseleave', function () { glow.classList.remove('on'); });
        }
    }

    /* ---------- Gambar, kartu, tombol, pindah halaman ---------- */
    function initInteractions() {
        $$('.project-card .thumb img, .gallery-main img, .cell-thumb img, .mini-thumb img').forEach(function (img) {
            if (img.complete && img.naturalWidth) return;
            img.classList.add('img-loading');
            var done = function () { img.classList.remove('img-loading'); };
            img.addEventListener('load', done, { once: true });
            img.addEventListener('error', done, { once: true });
        });

        if (fine) {
            $$('.project-card, .stat-card, .ticket-card, .kpi').forEach(function (el) {
                var max = el.classList.contains('project-card') ? 5 : 7, raf = 0;
                el.addEventListener('pointermove', function (e) {
                    var cx = e.clientX, cy = e.clientY;
                    if (raf) return;
                    raf = requestAnimationFrame(function () {
                        raf = 0;
                        var r = el.getBoundingClientRect();
                        var x = (cx - r.left) / r.width, y = (cy - r.top) / r.height;
                        el.style.setProperty('--mx', (x * 100) + '%');
                        el.style.setProperty('--my', (y * 100) + '%');
                        if (el.hasAttribute('data-reveal')) return;   // jangan ganggu animasi masuk
                        el.style.transform = 'perspective(900px) rotateX(' + ((.5 - y) * max * 2).toFixed(2) + 'deg) rotateY(' + ((x - .5) * max * 2).toFixed(2) + 'deg) translateY(-6px)';
                    });
                });
                el.addEventListener('pointerleave', function () {
                    if (raf) { cancelAnimationFrame(raf); raf = 0; }
                    el.style.transform = '';
                });
            });

            $$('.btn-primary:not(.btn-sm):not(.btn-block)').forEach(function (el) {   // tombol "magnetik"
                el.addEventListener('pointermove', function (e) {
                    var r = el.getBoundingClientRect();
                    el.style.translate = ((e.clientX - (r.left + r.width / 2)) * .18).toFixed(1) + 'px ' + ((e.clientY - (r.top + r.height / 2)) * .3).toFixed(1) + 'px';
                });
                el.addEventListener('pointerleave', function () { el.style.translate = ''; });
            });
        }

        doc.addEventListener('click', function (e) {            // ripple
            var b = e.target.closest ? e.target.closest('.btn') : null;
            if (!b || b.disabled) return;
            var r = b.getBoundingClientRect(), s = Math.max(r.width, r.height) * 2;
            var sp = doc.createElement('span');
            sp.className = 'ripple';
            sp.style.cssText = 'width:' + s + 'px;height:' + s + 'px;left:' + (e.clientX - r.left - s / 2) + 'px;top:' + (e.clientY - r.top - s / 2) + 'px';
            b.appendChild(sp);
            setTimeout(function () { if (sp.parentNode) sp.parentNode.removeChild(sp); }, 700);
        });

        doc.addEventListener('click', function (e) {            // fade antar halaman
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-no-transition')) return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return;
            var u;
            try { u = new URL(a.href, location.href); } catch (err) { return; }
            if (u.origin !== location.origin) return;
            if (u.pathname === location.pathname && u.search === location.search) return;
            e.preventDefault();
            html.classList.add('leaving');
            setTimeout(function () { location.href = a.href; }, 220);
            setTimeout(function () { html.classList.remove('leaving'); }, 2500);   // jaga-jaga navigasi batal
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted) html.classList.remove('leaving'); });
    }

    /* ---------- Jalankan, dengan pengaman ---------- */
    try {
        boot();
    } catch (err) {
        if (window.console) console.error('[animations] dimatikan karena error:', err);
        html.classList.remove('anim');
        $$('[data-reveal]').forEach(function (el) { el.removeAttribute('data-reveal'); });
        showFinalValues();
    }
})();
