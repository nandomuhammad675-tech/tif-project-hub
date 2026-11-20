/* TIF Hub — UI scripts (vanilla JS) */
(function () {
    'use strict';

    /* ---------- Admin sidebar (mobile) ---------- */
    var sb = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    var tg = document.getElementById('sidebarToggle');
    function toggleSidebar(open) {
        if (!sb) return;
        sb.classList.toggle('open', open);
        if (ov) ov.classList.toggle('open', open);
    }
    if (tg) tg.addEventListener('click', function () { toggleSidebar(!sb.classList.contains('open')); });
    if (ov) ov.addEventListener('click', function () { toggleSidebar(false); });

    /* Progress bar & count-up sekarang ditangani animations.js */

    /* ---------- Gallery + lightbox (halaman detail proyek) ---------- */
    var gal = document.querySelector('[data-gallery]');
    if (gal) {
        var imgs = JSON.parse(gal.dataset.gallery || '[]');
        var idx = 0;
        var main = gal.querySelector('.gallery-main img');
        var thumbs = gal.querySelectorAll('.gallery-thumbs button');
        var lb = document.getElementById('lightbox');
        var lbImg = lb ? lb.querySelector('img') : null;
        var lbCount = lb ? lb.querySelector('.lb-counter') : null;

        function show(i) {
            idx = (i + imgs.length) % imgs.length;
            if (main) { main.src = imgs[idx]; swap(main); }
            thumbs.forEach(function (b, n) { b.classList.toggle('active', n === idx); });
            if (lb && lb.classList.contains('open')) { lbImg.src = imgs[idx]; swap(lbImg); lbCount.textContent = (idx + 1) + ' / ' + imgs.length; }
        }
        // Efek fade + zoom halus tiap ganti foto
        function swap(img) { img.classList.remove('swap'); void img.offsetWidth; img.classList.add('swap'); }
        thumbs.forEach(function (b, n) { b.addEventListener('click', function () { show(n); }); });
        function openLb() { if (!lb || !imgs.length) return; lb.classList.add('open'); lbImg.src = imgs[idx]; swap(lbImg); lbCount.textContent = (idx + 1) + ' / ' + imgs.length; document.body.style.overflow = 'hidden'; }
        function closeLb() { if (!lb) return; lb.classList.remove('open'); document.body.style.overflow = ''; }
        var mainBox = gal.querySelector('.gallery-main');
        if (mainBox && imgs.length) mainBox.addEventListener('click', openLb);
        if (lb) {
            lb.querySelector('.lb-close').addEventListener('click', closeLb);
            lb.querySelector('.lb-prev').addEventListener('click', function (e) { e.stopPropagation(); show(idx - 1); });
            lb.querySelector('.lb-next').addEventListener('click', function (e) { e.stopPropagation(); show(idx + 1); });
            lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });
            document.addEventListener('keydown', function (e) {
                if (!lb.classList.contains('open')) return;
                if (e.key === 'Escape') closeLb();
                if (e.key === 'ArrowLeft') show(idx - 1);
                if (e.key === 'ArrowRight') show(idx + 1);
            });
            if (imgs.length < 2) lb.querySelectorAll('.lb-prev,.lb-next').forEach(function (b) { b.style.display = 'none'; });
        }
    }

    /* ---------- Admin: foto proyek ---------- */
    var dz = document.getElementById('dropzone');
    if (!dz) return;

    var input = dz.querySelector('input[type=file]');
    var grid = document.getElementById('newImages');
    var note = document.getElementById('uploadNote');
    var form = dz.closest('form');

    // Batas dibaca dari server (lihat form.php) supaya pesan error jelas sebelum upload
    var MAX_FILES = parseInt(dz.dataset.maxCount, 10) || 10;
    var FILE_MAX = parseInt(dz.dataset.maxFile, 10) || 5 * 1048576;
    var POST_MAX = parseInt(dz.dataset.maxPost, 10) || 0; // 0 = tanpa batas
    var OVERHEAD = 300 * 1024;                            // perkiraan field form lain
    var MAX_DIM = 1600, QUALITY = 0.85;
    var ALLOWED = /^image\/(jpeg|png|webp|gif)$/;

    var files = [];   // File[] yang akan dikirim
    var keys = [];    // penanda file asli (cegah duplikat), sejajar dengan files
    var urls = [];    // object URL preview, di-revoke tiap render
    var warn = '';
    var busy = 0;

    function fmt(bytes) { return bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB'; }
    function total() { return files.reduce(function (n, f) { return n + f.size; }, 0); }
    function hasFiles(e) { return !!(e.dataTransfer && e.dataTransfer.types && Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') > -1); }

    function syncInput() {
        try {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        } catch (err) { /* browser lama: pakai pilihan asli */ }
        render();
    }

    // Kompres di browser: resize maks 1600px. JPG tetap JPG; PNG/WEBP jadi WEBP (transparansi aman).
    // GIF tidak disentuh agar animasinya tetap jalan.
    function compress(file) {
        return new Promise(function (resolve) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return resolve(file);
            var url = URL.createObjectURL(file);
            var im = new Image();
            im.onload = function () {
                var w = im.naturalWidth, h = im.naturalHeight, r = Math.min(1, MAX_DIM / Math.max(w, h));
                if (r === 1 && file.size < 900 * 1024) { URL.revokeObjectURL(url); return resolve(file); }
                var isJpeg = file.type === 'image/jpeg';
                var c = document.createElement('canvas');
                c.width = Math.round(w * r); c.height = Math.round(h * r);
                var ctx = c.getContext('2d');
                if (isJpeg) { ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); }
                ctx.drawImage(im, 0, 0, c.width, c.height);
                var outType = isJpeg ? 'image/jpeg' : 'image/webp';
                c.toBlob(function (blob) {
                    URL.revokeObjectURL(url);
                    // Safari belum bisa encode WEBP -> blob.type bukan WEBP; pakai file asli saja
                    if (!blob || blob.type !== outType || blob.size >= file.size) return resolve(file);
                    var ext = isJpeg ? '.jpg' : '.webp';
                    resolve(new File([blob], file.name.replace(/\.\w+$/, '') + ext, { type: outType }));
                }, outType, QUALITY);
            };
            im.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
            im.src = url;
        });
    }

    function render() {
        urls.forEach(function (u) { URL.revokeObjectURL(u); });
        urls = [];
        grid.innerHTML = '';
        files.forEach(function (f, i) {
            var tile = document.createElement('div');
            tile.className = 'img-tile';
            var box = document.createElement('div'); box.className = 'img-box';
            var im = document.createElement('img'); im.alt = f.name;
            var u = URL.createObjectURL(f); urls.push(u); im.src = u;
            box.appendChild(im);
            var bar = document.createElement('div'); bar.className = 'tile-bar';
            var nm = document.createElement('span'); nm.textContent = fmt(f.size);
            bar.appendChild(nm);
            var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'rm-btn'; rm.innerHTML = '&times;'; rm.title = 'Batalkan';
            rm.addEventListener('click', function () { files.splice(i, 1); keys.splice(i, 1); warn = ''; syncInput(); });
            tile.appendChild(box); tile.appendChild(bar); tile.appendChild(rm);
            grid.appendChild(tile);
        });
        var msg = busy ? 'Memproses foto…' : (files.length ? files.length + ' foto baru siap diunggah (' + fmt(total()) + ')' : '');
        if (warn) msg += (msg ? ' · ' : '') + warn;
        if (note) note.textContent = msg;
    }

    function addFiles(list) {
        var rejected = [];
        var arr = Array.prototype.slice.call(list).filter(function (f) {
            if (!ALLOWED.test(f.type)) { rejected.push('"' + f.name + '" bukan JPG/PNG/WEBP/GIF'); return false; }
            return true;
        });

        // Lewati duplikat
        var batch = [];
        arr.forEach(function (f) {
            var k = f.name + '|' + f.size + '|' + f.lastModified;
            if (keys.indexOf(k) > -1 || batch.some(function (b) { return b.key === k; })) return;
            batch.push({ file: f, key: k });
        });

        var slots = MAX_FILES - files.length;
        if (batch.length > slots) {
            rejected.push('maksimal ' + MAX_FILES + ' foto sekali simpan');
            batch = batch.slice(0, Math.max(0, slots));
        }
        if (!batch.length) { warn = rejected.join('; '); render(); return; }

        busy++; warn = ''; render();
        Promise.all(batch.map(function (b) { return compress(b.file); })).then(function (done) {
            done.forEach(function (f, n) {
                if (f.size > FILE_MAX) { rejected.push('"' + batch[n].file.name + '" lebih dari ' + fmt(FILE_MAX)); return; }
                if (POST_MAX && total() + f.size + OVERHEAD > POST_MAX) { rejected.push('"' + batch[n].file.name + '" melewati batas total upload server (' + fmt(POST_MAX) + ')'); return; }
                files.push(f); keys.push(batch[n].key);
            });
            warn = rejected.join('; ');
        }).then(function () { busy--; syncInput(); }, function () { busy--; warn = 'Foto gagal diproses.'; syncInput(); });
    }

    input.addEventListener('change', function () { addFiles(Array.prototype.slice.call(input.files)); });
    ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function (e) { if (!hasFiles(e)) return; e.preventDefault(); dz.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('drag'); }); });
    dz.addEventListener('drop', function (e) { if (hasFiles(e) && e.dataTransfer.files.length) addFiles(e.dataTransfer.files); });

    // Cegah submit selagi foto masih diproses (agar tidak ada foto yang tertinggal)
    if (form) form.addEventListener('submit', function (e) {
        if (busy) { e.preventDefault(); alert('Foto masih diproses, tunggu sebentar lalu simpan lagi.'); }
    });

    /* Foto yang sudah ada: tandai hapus / pilih cover / atur urutan */
    var tiles = Array.prototype.slice.call(document.querySelectorAll('.img-tile[data-existing]'));
    var oldGrid = tiles.length ? tiles[0].parentNode : null;

    tiles.forEach(function (tile) {
        var del = tile.querySelector('input[type=checkbox]');
        var cov = tile.querySelector('input[type=radio]');
        if (del) del.addEventListener('change', function () { tile.classList.toggle('to-delete', del.checked); if (del.checked && cov && cov.checked) cov.checked = false; refreshCover(); });
        if (cov) cov.addEventListener('change', refreshCover);
        var pic = tile.querySelector('img'); if (pic) pic.draggable = false;

        // Tombol geser (juga jalan di HP)
        tile.querySelectorAll('.mv-btn').forEach(function (b) {
            b.addEventListener('click', function () {
                if (!oldGrid) return;
                if (b.dataset.move === '-1') {
                    var prev = tile.previousElementSibling;
                    if (prev) oldGrid.insertBefore(tile, prev);
                } else {
                    var next = tile.nextElementSibling;
                    if (next) oldGrid.insertBefore(next, tile);
                }
            });
        });
    });

    // Seret & lepas untuk mengubah urutan (desktop)
    var dragEl = null;
    tiles.forEach(function (tile) {
        tile.addEventListener('dragstart', function (e) {
            dragEl = tile; tile.classList.add('dragging');
            if (e.dataTransfer) { e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', 'tile'); } catch (err) { /* abaikan */ } }
        });
        tile.addEventListener('dragend', function () {
            tile.classList.remove('dragging');
            tiles.forEach(function (t) { t.classList.remove('drag-over'); });
            dragEl = null;
        });
        tile.addEventListener('dragover', function (e) {
            if (!dragEl || dragEl === tile) return;
            e.preventDefault(); tile.classList.add('drag-over');
        });
        tile.addEventListener('dragleave', function () { tile.classList.remove('drag-over'); });
        tile.addEventListener('drop', function (e) {
            e.preventDefault(); tile.classList.remove('drag-over');
            if (!dragEl || dragEl === tile || !oldGrid) return;
            var kids = Array.prototype.slice.call(oldGrid.children);
            if (kids.indexOf(dragEl) < kids.indexOf(tile)) oldGrid.insertBefore(dragEl, tile.nextSibling);
            else oldGrid.insertBefore(dragEl, tile);
        });
    });

    function refreshCover() {
        tiles.forEach(function (t) {
            var r = t.querySelector('input[type=radio]');
            t.classList.toggle('is-cover', !!(r && r.checked));
        });
    }
    refreshCover();
})();
