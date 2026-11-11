<?php
// Admin > Yazılar > Yeni / Düzenle: WordPress düzenleyicisi gibi. Davranış: Assets/admin/post-editor.js
// Değişken: $post (ya da null)
$siteName = Setting::get('site_name', Config::get('app.name'));
?>
<div data-post-editor
     data-id="<?= $post ? (int) $post['id'] : '' ?>"
     data-list-url="<?= View::e(Router::url('admin.blog.index')) ?>"
     data-edit-url="<?= View::e(Request::url('admin/blog/{id}/edit')) ?>"
     data-base-url="<?= View::e(rtrim((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . Request::url(''), '/')) ?>"
     data-site-name="<?= View::e($siteName) ?>">

  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <a href="<?= Router::url('admin.blog.index') ?>" class="btn btn-ghost size-9 p-0" aria-label="Yazılara dön" title="Yazılara dön">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </a>
      <div class="leading-tight">
        <h1 class="text-lg font-semibold tracking-tight"><?= $post ? 'Yazıyı düzenle' : 'Yeni yazı' ?></h1>
        <p class="text-xs text-muted-foreground" data-ref="saveState">&nbsp;</p>
      </div>
    </div>
    <div class="flex gap-2">
      <button type="button" class="btn btn-ghost hidden" data-ref="history" title="Önceki sürümler">Geçmiş</button>
      <button type="button" class="btn btn-secondary" data-ref="preview">Önizle</button>
      <button type="button" class="btn btn-ghost" data-ref="saveDraft">Taslak olarak kaydet</button>
      <button type="button" class="btn btn-primary" data-ref="publish">Yayınla</button>
    </div>
  </div>

  <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="min-w-0 space-y-4">
      <div data-field="title">
        <label for="post-title" class="sr-only">Başlık</label>
        <input id="post-title" class="w-full border-0 bg-transparent p-0 text-3xl font-semibold tracking-tight outline-none placeholder:text-muted-foreground/60"
               placeholder="Başlık ekleyin" maxlength="200" autocomplete="off" data-ref="title">
      </div>
      <div class="flex min-w-0 flex-wrap items-center gap-1 text-sm text-muted-foreground" data-field="slug">
        <span class="shrink-0">Adres:</span>
        <span class="truncate font-mono text-xs" data-ref="prefix"></span>
        <input class="input mt-0 h-7 w-64 px-2 font-mono text-xs" maxlength="180" placeholder="basliktan-uretilir" spellcheck="false" data-ref="slug" aria-label="Adres">
        <a href="#" target="_blank" class="ml-1 hidden text-xs font-medium text-foreground underline underline-offset-4" data-ref="viewLink">Görüntüle</a>
      </div>
      <div class="card overflow-hidden" data-field="body"><div data-ref="editor" class="min-h-[28rem]"></div></div>
      <div class="card p-4" data-field="excerpt">
        <div class="flex items-center justify-between"><label class="label" for="post-excerpt">Özet</label><span class="text-xs text-muted-foreground">Listelerde görünür; boşsa içeriğin başı</span></div>
        <textarea id="post-excerpt" class="input" rows="2" maxlength="500" data-ref="excerpt"></textarea>
      </div>
    </div>

    <aside class="space-y-4 lg:sticky lg:top-20">
      <?= View::render('admin/partials/translations-box', ['lang' => $lang, 'siblings' => $siblings, 'id' => $post['id'] ?? null, 'edit' => 'admin/blog/{id}/edit', 'translate' => 'admin/blog/{id}/translate/{lang}']) ?>
      <section class="card" data-field="status">
        <h2 class="card-title border-b px-4 py-3 text-sm">Yayın</h2>
        <div class="space-y-3 px-4 py-3 text-sm">
          <div class="flex items-center justify-between gap-3"><span class="text-muted-foreground">Durum</span><span data-ref="statusBadge"><span class="badge badge-gray">Yeni</span></span></div>
          <div data-field="published_at">
            <label class="label" for="post-date">Yayın tarihi</label>
            <input id="post-date" type="datetime-local" class="input" data-ref="date">
            <p class="field-help text-xs">Boşsa yayınladığınız an. İleri bir tarih seçerseniz yazı o zaman kendiliğinden yayına girer.</p>
          </div>
        </div>
        <div class="flex items-center justify-between gap-2 border-t px-4 py-3">
          <button type="button" class="btn btn-danger-ghost btn-sm hidden" data-ref="delete">Sil</button>
          <button type="button" class="btn btn-ghost btn-sm ml-auto hidden" data-ref="unpublish">Yayından kaldır</button>
        </div>
      </section>

      <section class="card" data-field="category_id">
        <div class="flex items-center justify-between border-b px-4 py-3"><h2 class="card-title text-sm">Kategori</h2><button type="button" class="text-xs font-medium hover:underline" data-ref="newCategory">+ Yeni</button></div>
        <div class="p-4"><select class="input mt-0" data-ref="category"><option value="">Kategorisiz</option></select></div>
      </section>

      <section class="card" data-field="tags">
        <h2 class="card-title border-b px-4 py-3 text-sm">Etiketler</h2>
        <div class="p-4">
          <div class="flex min-h-9 flex-wrap items-center gap-1.5 rounded-md border border-input px-2 py-1.5 focus-within:ring-[3px] focus-within:ring-ring/20" data-ref="tagBox">
            <input class="min-w-24 flex-1 bg-transparent text-sm outline-none" placeholder="Yazıp Enter'a basın" list="tag-suggestions" maxlength="40" data-ref="tagInput">
          </div>
          <datalist id="tag-suggestions" data-ref="tagList"></datalist>
          <p class="field-help text-xs">Virgül ya da Enter ile ekleyin. Etiket sayfası: /blog/etiket/...</p>
        </div>
      </section>

      <section class="card" data-field="image">
        <h2 class="card-title border-b px-4 py-3 text-sm">Öne çıkan görsel</h2>
        <div class="p-4">
          <button type="button" class="flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-dashed bg-muted/50 text-sm text-muted-foreground transition hover:bg-muted" data-ref="imagePick">Görsel seç</button>
          <button type="button" class="btn btn-ghost btn-sm mt-2 hidden w-full text-destructive" data-ref="imageClear">Görseli kaldır</button>
          <p class="field-help text-xs">Listelerde kapak, sosyal medyada paylaşım görseli.</p>
        </div>
      </section>

      <section class="card" data-field="meta_description">
        <h2 class="card-title border-b px-4 py-3 text-sm">Arama motorları</h2>
        <div class="space-y-3 p-4">
          <div>
            <div class="flex items-center justify-between"><label class="label" for="post-meta">Açıklama</label><span class="text-xs tabular-nums text-muted-foreground" data-ref="metaCount">0/160</span></div>
            <textarea id="post-meta" class="input" rows="3" maxlength="300" placeholder="Boşsa özet ya da içeriğin başı." data-ref="meta"></textarea>
          </div>
          <div class="rounded-md border bg-background p-3" aria-label="Google önizlemesi">
            <p class="truncate text-xs text-emerald-700" data-ref="serpUrl"></p>
            <p class="mt-0.5 truncate text-[15px] text-blue-700" data-ref="serpTitle"></p>
            <p class="mt-0.5 line-clamp-2 text-xs leading-5 text-muted-foreground" data-ref="serpText"></p>
          </div>
        </div>
      </section>
    </aside>
  </div>
</div>

<script type="module" src="<?= View::e(Module::asset('Blog', 'admin/post-editor.js')) ?>"></script>
