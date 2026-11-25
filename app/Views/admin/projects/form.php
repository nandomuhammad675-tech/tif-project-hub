<?php
$images = $images ?? [];
$limits = \App\Helpers\ImageUploader::limits();
$fmtMb = fn(int $b): string => $b >= 1048576 ? rtrim(rtrim(number_format($b / 1048576, 1), '0'), '.') . ' MB' : round($b / 1024) . ' KB';
$hasCover = false;
foreach ($images as $im) { if ((int) $im['is_cover'] === 1) { $hasCover = true; break; } }
$assigned = $assigned ?? [];
$memberIds = array_map('intval', $members ?? []);   // Ketua selalu urutan pertama
$leaderId  = $memberIds[0] ?? null;
$otherNames = [];
foreach ($students ?? [] as $st) {
    $sid = (int) $st['id'];
    if (in_array($sid, $memberIds, true) && $sid !== $leaderId) {
        $otherNames[] = (string) $st['name'];
    }
}
$mpData = [
    'students' => array_map(fn($st) => [
        'id'    => (int) $st['id'],
        'name'  => (string) $st['name'],
        'nim'   => (string) ($st['nim'] ?? ''),
        'class' => (string) ($st['class'] ?? ''),
        'busy'  => $assigned[(int) $st['id']] ?? null,
    ], $students ?? []),
    'leader' => $leaderId,
    'others' => $otherNames,
];
?>
<style>
    .mp-full { grid-column: 1 / -1; }
    .mp { border: 1px solid var(--border); border-radius: 18px; background: #fff; overflow: hidden; }
    .mp-grid { display: grid; grid-template-columns: 1.15fr 1fr; }
    .mp-col { padding: 1rem 1.1rem 1.1rem; min-width: 0; }
    .mp-col-chosen { background: var(--grad-soft); border-left: 1px solid var(--border); }
    .mp-head { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-bottom: .75rem; font-size: .95rem; }
    .mp-pill { font-size: .72rem; font-weight: 700; color: var(--primary-dark); background: var(--primary-light); border-radius: 999px; padding: .15rem .65rem; }
    .mp-pill.is-solid { background: var(--grad); color: #fff; min-width: 28px; text-align: center; }
    .mp-search { position: relative; }
    .mp-search svg { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--muted); pointer-events: none; }
    .mp-search input { width: 100%; padding: .65rem .9rem .65rem 2.6rem; border: 1px solid var(--border); border-radius: 12px; font: inherit; font-size: .9rem; background: #fff; transition: border-color .15s ease, box-shadow .15s ease; }
    .mp-search input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(3,121,146,.14); }
    .mp-free { display: flex; align-items: center; gap: .45rem; margin: .6rem 0; font-size: .8rem; color: var(--muted); cursor: pointer; font-weight: 600; }
    .mp-free input { accent-color: var(--primary); }
    .mp-list { max-height: 330px; overflow-y: auto; display: grid; gap: .3rem; padding-right: .2rem; overscroll-behavior: contain; }
    .mp-list::-webkit-scrollbar, .mp-chosen::-webkit-scrollbar { width: 8px; }
    .mp-list::-webkit-scrollbar-thumb, .mp-chosen::-webkit-scrollbar-thumb { background: #cfe3ea; border-radius: 8px; }
    .mp-row { display: flex; align-items: center; gap: .7rem; width: 100%; text-align: left; font: inherit; padding: .5rem .6rem; border: 1px solid transparent; border-radius: 12px; background: transparent; cursor: pointer; transition: background .15s ease, border-color .15s ease, transform .15s ease; }
    .mp-row:hover:not(:disabled) { background: var(--primary-light); transform: translateX(2px); }
    .mp-row:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
    .mp-row:disabled { cursor: not-allowed; opacity: .62; }
    .mp-row.is-picked { background: #eaf7fb; border-color: #bfe3ee; }
    .mp-av { width: 38px; height: 38px; border-radius: 12px; flex: none; display: grid; place-items: center; font-size: .8rem; font-weight: 800; color: var(--primary-dark); background: #e3f3f7; }
    .mp-row.is-picked .mp-av { background: var(--grad); color: #fff; }
    .mp-info { flex: 1; min-width: 0; }
    .mp-info strong { display: block; font-size: .9rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mp-info small { display: block; font-size: .76rem; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mp-act { flex: none; width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; color: var(--primary); background: #fff; border: 1px solid var(--border); transition: background .15s ease, color .15s ease, transform .2s cubic-bezier(.34,1.56,.64,1); }
    .mp-act svg { width: 16px; height: 16px; }
    .mp-row:hover:not(:disabled) .mp-act { background: var(--primary); color: #fff; transform: scale(1.1); }
    .mp-row.is-picked .mp-act { background: var(--primary); color: #fff; border-color: var(--primary); }
    .mp-tag { flex: none; max-width: 40%; font-size: .68rem; font-weight: 700; color: #92400e; background: #fef3c7; border-radius: 999px; padding: .15rem .55rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mp-empty { padding: 1.4rem .5rem; text-align: center; font-size: .85rem; color: var(--muted); }

    .mp-chosen { display: grid; gap: .5rem; max-height: 392px; overflow-y: auto; align-content: start; }
    .mp-item { display: flex; align-items: center; gap: .65rem; padding: .55rem .6rem; background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 8px rgba(3,121,146,.06); animation: mpIn .28s cubic-bezier(.34,1.56,.64,1) both; }
    .mp-item.is-lead { border-color: #f2c94c; background: linear-gradient(135deg, #fffdf3, #fff); box-shadow: 0 6px 18px rgba(245,158,11,.14); }
    .mp-item .mp-av { background: var(--grad); color: #fff; }
    .mp-role { display: inline-flex; align-items: center; gap: .25rem; margin-top: .15rem; font-size: .68rem; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; color: var(--primary-dark); }
    .mp-item.is-lead .mp-role { color: #b45309; }
    .mp-role svg { width: 12px; height: 12px; }
    .mp-btn { flex: none; width: 32px; height: 32px; border-radius: 10px; border: 0; display: grid; place-items: center; cursor: pointer; background: #f1f7f9; color: var(--muted); transition: background .15s ease, color .15s ease, transform .2s cubic-bezier(.34,1.56,.64,1); }
    .mp-btn svg { width: 17px; height: 17px; }
    .mp-btn:hover { transform: scale(1.1); }
    .mp-btn.crown:hover { background: #fef3c7; color: #b45309; }
    .mp-btn.del:hover { background: #fee2e2; color: #b91c1c; }
    .mp-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 1px; }
    .mp-none { padding: 2rem 1rem; text-align: center; color: var(--muted); font-size: .86rem; border: 2px dashed #cfe3ea; border-radius: 14px; background: rgba(255,255,255,.6); }
    .mp-none svg { width: 34px; height: 34px; color: var(--primary); opacity: .55; margin-bottom: .4rem; }
    .mp-foot { display: flex; gap: .6rem; align-items: flex-start; padding: .8rem 1.1rem; border-top: 1px solid var(--border); background: #f7fbfc; font-size: .8rem; color: var(--muted); line-height: 1.5; }
    .mp-foot svg { width: 17px; height: 17px; flex: none; margin-top: 2px; color: var(--primary); }
    @keyframes mpIn { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .mp-item { animation: none; } .mp-row, .mp-act, .mp-btn { transition: none; } }
    @media (max-width: 820px) {
        .mp-grid { grid-template-columns: 1fr; }
        .mp-col-chosen { border-left: 0; border-top: 1px solid var(--border); }
    }
    .mp-others { margin-top: .8rem; padding: .75rem .9rem; background: #fff; border: 1px dashed #bfe3ee; border-radius: 12px; font-size: .82rem; display: grid; gap: .2rem; }
    .mp-others[hidden], .mp-warn[hidden] { display: none; }
    .mp-others b { color: var(--primary-dark); }
    .mp-others small { color: var(--muted); }
    .mp-warn { margin-top: .8rem; padding: .75rem .9rem; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; font-size: .82rem; color: #9a3412; line-height: 1.5; }
    /* ---- Tampilan baru: dua langkah (Ketua terpilih -> pilih dari daftar) ---- */
    .mp-step { padding: 1.1rem 1.2rem 1.2rem; background: var(--grad-soft); border-bottom: 1px solid var(--border); }
    .mp-pick { padding: 1.1rem 1.2rem 1.2rem; }
    .mp-title { display: flex; align-items: center; gap: .7rem; margin-bottom: .85rem; }
    .mp-title > div { flex: 1; min-width: 0; }
    .mp-title b { display: block; font-size: .95rem; font-weight: 800; }
    .mp-title small { display: block; font-size: .78rem; color: var(--muted); margin-top: .05rem; }
    .mp-title small em { font-style: normal; font-weight: 800; color: var(--primary-dark); }
    .mp-num { width: 26px; height: 26px; border-radius: 50%; flex: none; display: grid; place-items: center; font-size: .8rem; font-weight: 800; color: #fff; background: var(--grad); }
    .mp-list { margin-top: .75rem; max-height: 300px; }
    .mp-sub { margin: .7rem .2rem .1rem; font-size: .72rem; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--muted); }
    .mp-row { padding: .55rem .65rem; border-color: var(--border); background: #fff; }
    .mp-row:disabled { opacity: 1; background: #f6f9fa; }
    .mp-row:disabled .mp-av { background: #e8eef1; color: var(--muted); }
    .mp-row:disabled .mp-info strong { color: var(--muted); }
    .mp-act { width: auto; height: auto; border-radius: 999px; padding: .3rem .85rem; font-size: .78rem; font-weight: 800; gap: .3rem; display: inline-flex; align-items: center; color: var(--primary-dark); background: var(--primary-light); border-color: transparent; }
    .mp-act svg { width: 14px; height: 14px; }
    .mp-row:hover:not(:disabled) .mp-act { transform: none; }
    .mp-row.is-picked .mp-act { background: var(--primary); color: #fff; }
    .mp-tag { max-width: 46%; }
    .mp-toggle { display: block; width: 100%; margin-top: .7rem; padding: .6rem; font: inherit; font-size: .82rem; font-weight: 800; color: var(--primary); background: transparent; border: 1.5px dashed #bfe3ee; border-radius: 12px; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .mp-toggle:hover { background: var(--primary-light); border-color: var(--primary); }
    .mp-toggle[hidden] { display: none; }
    .mp-item { padding: .75rem .8rem; }
    .mp-item .mp-av { width: 46px; height: 46px; border-radius: 14px; font-size: .9rem; }
    .mp-item .mp-info strong { font-size: 1rem; }
    .mp-clear { flex: none; display: inline-flex; align-items: center; gap: .3rem; font: inherit; font-size: .78rem; font-weight: 800; color: #b91c1c; background: #fee2e2; border: 0; border-radius: 999px; padding: .4rem .8rem; cursor: pointer; transition: background .15s ease; }
    .mp-clear:hover { background: #fecaca; }
    .mp-clear svg { width: 14px; height: 14px; }
    .mp-none { padding: 1.2rem 1rem; }
    .mp-none svg { width: 28px; height: 28px; }
    .mp-foot { align-items: center; }
    /* ---- Dropdown kustom (senada dengan halaman Project War) ---- */
    .dd { position: relative; }
    .dd-native { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; pointer-events: none; }
    .dd-btn { width: 100%; display: flex; align-items: center; gap: .6rem; text-align: left; font: inherit; font-size: .92rem; padding: .75rem .9rem; background: #fff; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer; color: var(--text); font-weight: 600; transition: border-color .15s, box-shadow .15s; }
    .dd-btn:hover, .dd.open .dd-btn { border-color: var(--primary); }
    .dd.open .dd-btn, .dd-btn:focus-visible { box-shadow: 0 0 0 3px rgba(3,121,146,.15); outline: none; }
    .dd.is-empty .dd-btn { color: var(--muted); font-weight: 500; }
    .dd-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .dd-text small { font-weight: 500; color: var(--muted); font-size: .8rem; margin-left: .4rem; }
    .dd-chev { width: 18px; height: 18px; flex: none; color: var(--muted); transition: transform .2s ease; }
    .dd.open .dd-chev { transform: rotate(180deg); color: var(--primary); }
    .dd-pop { position: absolute; z-index: 60; top: calc(100% + 8px); left: 0; right: 0; margin: 0; padding: .4rem; list-style: none; max-height: 280px; overflow-y: auto; background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 24px 60px rgba(2,37,51,.28); }
    .dd-pop.up { top: auto; bottom: calc(100% + 8px); }
    .dd-pop[hidden] { display: none; }
    .dd-opt { display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem; border-radius: 10px; font-size: .9rem; font-weight: 600; cursor: pointer; color: var(--text); transition: background .12s; }
    .dd-opt .ck { width: 16px; height: 16px; flex: none; opacity: 0; }
    .dd-opt .t { flex: 1; min-width: 0; }
    .dd-opt .t small { display: block; font-size: .76rem; font-weight: 500; color: var(--muted); margin-top: .05rem; }
    .dd-opt.is-ph { color: var(--muted); font-weight: 500; }
    .dd-opt.act { background: var(--primary-light); }
    .dd-opt.sel { font-weight: 800; color: var(--primary-dark); }
    .dd-opt.sel .ck { opacity: 1; color: var(--primary); }
</style>
<a href="/admin/projects" class="back-link">← Kembali ke daftar proyek</a>
<div class="page-head mt-2">
    <div>
        <h1><?= $project ? 'Edit' : 'Tambah' ?> Proyek</h1>
        <p><?= $project ? 'Perbarui informasi dan foto proyek.' : 'Isi detail proyek lalu unggah foto-fotonya.' ?></p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="<?= $project ? '/admin/projects/' . (int) $project['id'] : '/admin/projects' ?>">
    <input type="hidden" name="_token" value="<?= e($csrf ?? \App\Core\Session::getCsrfToken()) ?>">

    <div class="card mb-3">
        <div class="form-section">
            <h3><span class="step">1</span> Informasi Dasar</h3>
            <div class="form-group">
                <label>Judul *</label>
                <input type="text" name="title" class="form-control" required value="<?= e($project['title'] ?? '') ?>" placeholder="Contoh: Sistem Informasi Perpustakaan">
            </div>
            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <input type="text" name="short_desc" class="form-control" maxlength="300" value="<?= e($project['short_desc'] ?? '') ?>" placeholder="Satu kalimat yang menarik perhatian">
            </div>
            <div class="form-group">
                <label>Deskripsi Lengkap *</label>
                <textarea name="description" class="form-control" required><?= e($project['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="step">2</span> Foto Proyek</h3>

            <?php if (!empty($images)): ?>
            <div class="subhead">Foto saat ini (<?= count($images) ?>)</div>
            <span class="form-hint" style="margin-bottom:.6rem;">Pilih satu foto sebagai <b>cover</b> (tampil di katalog dan selalu pertama di galeri). Geser foto atau pakai tombol ‹ › untuk mengatur urutan. Centang <b>Hapus</b> untuk membuang foto — semua berlaku setelah menekan Simpan.</span>
            <div class="img-grid">
                <?php foreach ($images as $i => $im):
                    $isCover = (int) $im['is_cover'] === 1 || (!$hasCover && $i === 0);
                ?>
                <div class="img-tile <?= $isCover ? 'is-cover' : '' ?>" data-existing draggable="true">
                    <input type="hidden" name="image_order[]" value="<?= (int) $im['id'] ?>">
                    <span class="cover-flag">COVER</span>
                    <div class="mv-group">
                        <button type="button" class="mv-btn" data-move="-1" aria-label="Geser ke kiri" title="Geser ke kiri">‹</button>
                        <button type="button" class="mv-btn" data-move="1" aria-label="Geser ke kanan" title="Geser ke kanan">›</button>
                    </div>
                    <div class="img-box"><img src="<?= e(\App\Helpers\ImageUploader::url($im['filename'])) ?>" alt="Foto proyek" loading="lazy"></div>
                    <div class="tile-bar">
                        <label><input type="radio" name="cover_image" value="<?= (int) $im['id'] ?>" <?= $isCover ? 'checked' : '' ?>> Cover</label>
                        <label style="color:var(--danger);"><input type="checkbox" name="delete_images[]" value="<?= (int) $im['id'] ?>"> Hapus</label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="subhead">Tambah foto baru</div>
            <?php endif; ?>

            <div class="dropzone" id="dropzone" data-existing="<?= count($images) ?>"
                 data-max-file="<?= (int) $limits['file'] ?>" data-max-post="<?= (int) $limits['post'] ?>" data-max-count="<?= (int) $limits['count'] ?>">
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                <div class="dz-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <h4>Seret &amp; lepas foto di sini</h4>
                <p>atau <b>klik untuk memilih</b> · JPG, PNG, WEBP, GIF · maks <?= e($fmtMb($limits['file'])) ?>/foto · hingga <?= (int) $limits['count'] ?> foto</p>
                <div class="img-grid" id="newImages"></div>
            </div>
            <div class="upload-note"><span id="uploadNote" role="status" aria-live="polite"></span><span>Foto otomatis dikecilkan agar upload cepat<?= $limits['post'] > 0 ? ' · total maks ' . e($fmtMb($limits['post'])) . ' per simpan' : '' ?></span></div>
            <?php if (empty($images)): ?>
            <span class="form-hint">Foto pertama yang diunggah otomatis menjadi cover. Anda bisa mengubahnya nanti.</span>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h3><span class="step">3</span> Kategori &amp; Akademik</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id" class="form-control" data-dd>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= ($project['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester_id" class="form-control" data-dd>
                        <option value="">Pilih semester</option>
                        <?php foreach ($semesters as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= ($project['semester_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mata Kuliah</label>
                    <input type="text" name="course_name" class="form-control" value="<?= e($project['course_name'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="step">4</span> Teknologi &amp; Tautan</h3>
            <div class="form-group">
                <label>Teknologi (pisahkan dengan koma)</label>
                <input type="text" name="technologies" class="form-control" value="<?= e($project['technologies'] ?? '') ?>" placeholder="PHP, MySQL, JavaScript">
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" class="form-control" value="<?= e($project['github_url'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Demo URL</label>
                    <input type="url" name="demo_url" class="form-control" value="<?= e($project['demo_url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="step">5</span> Ketua &amp; Publikasi</h3>
            <div class="form-grid">
                <div class="form-group mp-full">
                    <label>Ketua Kelompok</label>
                    <div class="mp" id="mp">
                        <div class="mp-step">
                            <div class="mp-title"><span class="mp-num">1</span><div><b>Ketua yang dipilih</b><small>Satu mahasiswa yang bertanggung jawab atas proyek ini.</small></div></div>
                            <div class="mp-chosen" id="mp-chosen" aria-live="polite"></div>
                            <div class="mp-others" id="mp-others" hidden></div>
                            <div class="mp-warn" id="mp-warn" hidden></div>
                        </div>
                        <div class="mp-pick">
                            <div class="mp-title"><span class="mp-num">2</span><div><b>Pilih dari daftar</b><small>Cari namanya, lalu klik tombol <em>Pilih</em>.</small></div><span class="mp-pill" id="mp-total"></span></div>
                            <div class="mp-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                                <input type="search" id="mp-q" placeholder="Ketik nama, NIM, atau kelas…" autocomplete="off" aria-label="Cari mahasiswa">
                            </div>
                            <div class="mp-list" id="mp-list" role="list"></div>
                            <button type="button" class="mp-toggle" id="mp-toggle" hidden></button>
                        </div>
                        <div class="mp-foot">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <span>Proyek akan otomatis muncul di profil Ketua. Anggota kelompok ditambahkan sendiri oleh Ketua lewat halaman Profil.</span>
                        </div>
                    </div>
                    <div id="mp-inputs"></div>
                    <script type="application/json" id="mp-data"><?= json_encode($mpData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
                    <noscript>
                        <select name="leader_id" class="form-control">
                            <option value="">— Belum ada Ketua —</option>
                            <?php foreach ($students as $s): if (isset($assigned[(int) $s['id']])) continue; ?>
                            <option value="<?= (int) $s['id'] ?>" <?= ((int) $s['id'] === (int) ($mpData['leader'] ?? 0)) ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['nim']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </noscript>
                </div>
                <div class="form-group">
                    <label>Status Publikasi</label>
                    <select name="is_published" class="form-control" data-dd>
                        <option value="1" <?= ($project['is_published'] ?? 1) == 1 ? 'selected' : '' ?>>Published — tampil di katalog</option>
                        <option value="0" <?= ($project['is_published'] ?? 1) == 0 ? 'selected' : '' ?>>Draft — disembunyikan</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= icon('save') ?> Simpan Proyek</button>
            <a href="/admin/projects" class="btn btn-outline">Batal</a>
        </div>
    </div>
</form>

<script>
(function () {
    var dataEl = document.getElementById('mp-data');
    if (!dataEl) return;
    var data;
    try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

    var students = data.students || [];
    var others = data.others || [];
    var byId = {};
    students.forEach(function (s) { byId[s.id] = s; });

    var hadLeader = !!data.leader;
    var leader = (data.leader && byId[data.leader] && !byId[data.leader].busy) ? data.leader : null;

    var $ = function (id) { return document.getElementById(id); };
    var listEl = $('mp-list'), chosenEl = $('mp-chosen'), inputsEl = $('mp-inputs');
    var qEl = $('mp-q'), totalEl = $('mp-total'), othersEl = $('mp-others'), warnEl = $('mp-warn'), toggleEl = $('mp-toggle');
    var showBusy = false;

    var SVG = {
        plus:  '<path d="M12 5v14M5 12h14"/>',
        check: '<path d="M20 6 9 17l-5-5"/>',
        x:     '<path d="M18 6 6 18M6 6l12 12"/>',
        crown: '<path d="m2 8 4 4 6-8 6 8 4-4-2 12H4z"/>',
        user:  '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'
    };
    function icon(name) {
        var s = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        s.setAttribute('viewBox', '0 0 24 24'); s.setAttribute('fill', 'none'); s.setAttribute('stroke', 'currentColor');
        s.setAttribute('stroke-width', '2'); s.setAttribute('stroke-linecap', 'round'); s.setAttribute('stroke-linejoin', 'round');
        s.setAttribute('aria-hidden', 'true');
        s.innerHTML = SVG[name];
        return s;
    }
    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text != null) n.textContent = text;
        return n;
    }
    function initials(name) {
        var p = String(name).trim().split(/\s+/).slice(0, 2);
        return p.map(function (w) { return w.charAt(0).toUpperCase(); }).join('') || '?';
    }
    function meta(s) { return [s.nim, s.class].filter(Boolean).join(' · '); }

    function choose(id) {
        leader = (leader === id) ? null : id;
        render();
    }

    function row(s, picked) {
        var b = el('button', 'mp-row' + (picked ? ' is-picked' : ''));
        b.type = 'button';
        b.setAttribute('role', 'listitem');
        b.appendChild(el('span', 'mp-av', initials(s.name)));
        var info = el('span', 'mp-info');
        info.appendChild(el('strong', '', s.name));
        info.appendChild(el('small', '', meta(s)));
        b.appendChild(info);
        if (s.busy) {
            b.disabled = true;
            var t = el('span', 'mp-tag', 'Sudah di proyek: ' + s.busy);
            t.title = 'Sudah tergabung di proyek ' + s.busy;
            b.appendChild(t);
        } else {
            var a = el('span', 'mp-act');
            a.appendChild(icon(picked ? 'check' : 'plus'));
            a.appendChild(document.createTextNode(picked ? 'Terpilih' : 'Pilih'));
            b.appendChild(a);
            b.setAttribute('aria-pressed', picked ? 'true' : 'false');
            b.addEventListener('click', function () { choose(s.id); });
        }
        return b;
    }

    function renderList() {
        var q = qEl.value.trim().toLowerCase();
        var match = function (s) { return !q || (s.name + ' ' + s.nim + ' ' + s.class).toLowerCase().indexOf(q) > -1; };
        var free = students.filter(function (s) { return !s.busy && match(s); });
        var busy = students.filter(function (s) { return s.busy && match(s); });

        listEl.textContent = '';
        if (!free.length) {
            var allBusy = !students.some(function (s) { return !s.busy; });
            listEl.appendChild(el('div', 'mp-empty', q ? 'Tidak ada mahasiswa tersedia yang cocok dengan pencarian.' : (allBusy ? 'Semua mahasiswa sudah punya proyek.' : 'Tidak ada mahasiswa tersedia.')));
        }
        free.forEach(function (s) { listEl.appendChild(row(s, leader === s.id)); });

        if (busy.length && showBusy) {
            listEl.appendChild(el('div', 'mp-sub', 'Sudah punya proyek (tidak bisa dipilih)'));
            busy.forEach(function (s) { listEl.appendChild(row(s, false)); });
        }

        if (busy.length) {
            toggleEl.hidden = false;
            toggleEl.textContent = showBusy ? 'Sembunyikan yang sudah punya proyek' : 'Tampilkan ' + busy.length + ' mahasiswa yang sudah punya proyek';
        } else {
            toggleEl.hidden = true;
        }
        totalEl.textContent = students.filter(function (s) { return !s.busy; }).length + ' tersedia';
    }

    function renderChosen() {
        chosenEl.textContent = '';
        if (!leader) {
            var n = el('div', 'mp-none');
            n.appendChild(icon('user'));
            n.appendChild(el('div', '', 'Belum ada Ketua. Klik tombol Pilih pada mahasiswa di daftar bawah.'));
            chosenEl.appendChild(n);
        } else {
            var s = byId[leader];
            var item = el('div', 'mp-item is-lead');
            item.appendChild(el('span', 'mp-av', initials(s.name)));
            var info = el('span', 'mp-info');
            info.appendChild(el('strong', '', s.name));
            info.appendChild(el('small', '', meta(s)));
            var role = el('span', 'mp-role');
            role.appendChild(icon('crown'));
            role.appendChild(document.createTextNode('Ketua'));
            info.appendChild(role);
            item.appendChild(info);
            var d = el('button', 'mp-clear'); d.type = 'button';
            d.title = 'Kosongkan Ketua'; d.setAttribute('aria-label', 'Kosongkan Ketua');
            d.appendChild(icon('x'));
            d.appendChild(document.createTextNode('Hapus'));
            d.addEventListener('click', function () { choose(leader); });
            item.appendChild(d);
            chosenEl.appendChild(item);
        }

        // anggota lain yang sudah ditambahkan Ketua (hanya informasi)
        othersEl.textContent = '';
        if (others.length && leader) {
            othersEl.hidden = false;
            othersEl.appendChild(el('b', '', 'Anggota kelompok saat ini (' + others.length + ')'));
            othersEl.appendChild(el('span', '', others.join(', ')));
            othersEl.appendChild(el('small', '', 'Dikelola oleh Ketua dari halaman Profil-nya.'));
        } else {
            othersEl.hidden = true;
        }

        // peringatan bila Ketua dikosongkan pada proyek yang sudah punya kelompok
        if (!leader && hadLeader) {
            warnEl.hidden = false;
            warnEl.textContent = 'Mengosongkan Ketua akan melepas seluruh anggota dari proyek ini, sehingga proyek kembali tersedia untuk diklaim.';
        } else {
            warnEl.hidden = true;
        }
    }

    function renderInputs() {
        inputsEl.textContent = '';
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = 'leader_id'; i.value = leader ? leader : '';
        inputsEl.appendChild(i);
    }

    function render() { renderList(); renderChosen(); renderInputs(); }

    toggleEl.addEventListener('click', function () { showBusy = !showBusy; renderList(); });
    qEl.addEventListener('input', renderList);
    qEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    render();
})();
</script>

<script>
(function () {
    var CK = '<svg class="ck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    var CHEV = '<svg class="dd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
    var all = [];
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function parts(t) { var p = t.replace(/\s+/g, ' ').trim().split(' — '); return { name: p[0], desc: p[1] || '' }; }

    function build(sel) {
        var root = document.createElement('div'); root.className = 'dd';
        sel.parentNode.insertBefore(root, sel);
        root.appendChild(sel);
        sel.classList.remove('form-control'); sel.classList.add('dd-native');
        sel.tabIndex = -1; sel.setAttribute('aria-hidden', 'true');

        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'dd-btn';
        btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<span class="dd-text"></span>' + CHEV;
        var text = btn.querySelector('.dd-text');
        var pop = document.createElement('ul'); pop.className = 'dd-pop'; pop.setAttribute('role', 'listbox'); pop.hidden = true;
        root.appendChild(btn); root.appendChild(pop);

        var items = [], act = -1;
        Array.prototype.forEach.call(sel.options, function (o) {
            var t = parts(o.text), li = document.createElement('li');
            li.className = 'dd-opt' + (o.value === '' ? ' is-ph' : '');
            li.setAttribute('role', 'option'); li.dataset.v = o.value;
            li.innerHTML = CK + '<span class="t">' + esc(t.name) + (t.desc ? '<small>' + esc(t.desc) + '</small>' : '') + '</span>';
            pop.appendChild(li); items.push(li);
        });

        function paint() {
            var o = sel.options[sel.selectedIndex], t = parts(o ? o.text : '');
            text.innerHTML = esc(t.name) + (t.desc && sel.value !== '' ? '<small>' + esc(t.desc) + '</small>' : '');
            root.classList.toggle('is-empty', sel.value === '');
            items.forEach(function (li) { var on = li.dataset.v === sel.value; li.classList.toggle('sel', on); li.setAttribute('aria-selected', on ? 'true' : 'false'); });
        }
        function setAct(i) {
            act = (i + items.length) % items.length;
            items.forEach(function (li, k) { li.classList.toggle('act', k === act); });
            items[act].scrollIntoView({ block: 'nearest' });
        }
        function close() { pop.hidden = true; root.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
        function open() {
            all.forEach(function (d) { if (d.root !== root) d.close(); });
            pop.classList.remove('up'); pop.hidden = false; root.classList.add('open'); btn.setAttribute('aria-expanded', 'true');
            var r = pop.getBoundingClientRect(), f = root.getBoundingClientRect(), vh = window.innerHeight;
            if (r.bottom > vh - 12 && f.top > vh - f.bottom) pop.classList.add('up');
            setAct(Math.max(0, items.findIndex(function (li) { return li.dataset.v === sel.value; })));
        }
        function pick(li) {
            if (sel.value !== li.dataset.v) { sel.value = li.dataset.v; sel.dispatchEvent(new Event('change', { bubbles: true })); }
            paint(); close(); btn.focus();
        }
        btn.addEventListener('click', function () { pop.hidden ? open() : close(); });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); if (pop.hidden) open(); else setAct(act + (e.key === 'ArrowDown' ? 1 : -1)); }
            else if ((e.key === 'Enter' || e.key === ' ') && !pop.hidden) { e.preventDefault(); pick(items[act]); }
            else if (e.key === 'Escape' || e.key === 'Tab') close();
        });
        pop.addEventListener('click', function (e) { var li = e.target.closest('.dd-opt'); if (li) pick(li); });
        pop.addEventListener('mousemove', function (e) { var li = e.target.closest('.dd-opt'); if (li) setAct(items.indexOf(li)); });
        sel.addEventListener('change', paint);
        paint();
        all.push({ root: root, close: close });
    }

    Array.prototype.forEach.call(document.querySelectorAll('select[data-dd]'), build);
    document.addEventListener('mousedown', function (e) { all.forEach(function (d) { if (!d.root.contains(e.target)) d.close(); }); });
})();
</script>
