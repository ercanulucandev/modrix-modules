<?php
// Admin > Sayfalar > Yeni / Düzenle: WordPress düzenleyicisi gibi. Solda başlık, adres ve içerik; sağda yayın,
// sayfa özellikleri, öne çıkan görsel ve arama motoru önizlemesi. Davranış: Assets/admin/page-editor.js
// Değişken: $page (düzenlenen sayfa ya da null)
$siteName = Setting::get('site_name', Config::get('app.name'));
?>
<div data-page-editor
     data-id="<?= $page ? (int) $page['id'] : '' ?>"
     data-list-url="<?= View::e(Router::url('admin.pages.index')) ?>"
     data-edit-url="<?= View::e(Request::url('admin/pages/{id}/edit')) ?>"
     data-base-url="<?= View::e(rtrim((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . Request::url(''), '/')) ?>"
     data-site-name="<?= View::e($siteName) ?>">

  <!-- Üst çubuk -->
  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <a href="<?= Router::url('admin.pages.index') ?>" class="btn btn-ghost size-9 p-0" aria-label="Sayfalara dön" title="Sayfalara dön">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </a>
      <div class="leading-tight">
        <h1 class="text-lg font-semibold tracking-tight"><?= $page ? 'Sayfayı düzenle' : 'Yeni sayfa' ?></h1>
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
    <!-- İçerik -->
    <div class="min-w-0 space-y-4">
      <div data-field="title">
        <label for="page-title" class="sr-only">Başlık</label>
        <input id="page-title" class="w-full border-0 bg-transparent p-0 text-3xl font-semibold tracking-tight outline-none placeholder:text-muted-foreground/60"
               placeholder="Başlık ekleyin" maxlength="200" autocomplete="off" data-ref="title">
      </div>

      <!-- Kalıcı bağlantı -->
      <div class="flex min-w-0 flex-wrap items-center gap-1 text-sm text-muted-foreground" data-field="slug">
        <span class="shrink-0">Adres:</span>
        <span class="truncate font-mono text-xs" data-ref="prefix"></span>
        <input class="input mt-0 h-7 w-56 px-2 font-mono text-xs" maxlength="100" placeholder="baslik-ten-uretilir" spellcheck="false" data-ref="slug" aria-label="Adres">
        <a href="#" target="_blank" class="ml-1 hidden text-xs font-medium text-foreground underline underline-offset-4" data-ref="viewLink">Görüntüle</a>
      </div>

      <div class="card overflow-hidden" data-field="body">
        <div data-ref="editor" class="min-h-[28rem]"></div>
      </div>
    </div>

    <!-- Kenar -->
    <aside class="space-y-4 lg:sticky lg:top-20">
      <?= View::render('admin/partials/translations-box', ['lang' => $lang, 'siblings' => $siblings, 'id' => $page['id'] ?? null, 'edit' => 'admin/pages/{id}/edit', 'translate' => 'admin/pages/{id}/translate/{lang}']) ?>
      <section class="card">
        <h2 class="card-title border-b px-4 py-3 text-sm">Yayın</h2>
        <dl class="space-y-2 px-4 py-3 text-sm">
          <div class="flex items-center justify-between gap-3"><dt class="text-muted-foreground">Durum</dt><dd data-ref="statusBadge"><span class="badge badge-gray">Yeni</span></dd></div>
          <div class="flex items-center justify-between gap-3"><dt class="text-muted-foreground">Son değişiklik</dt><dd data-ref="updated">—</dd></div>
        </dl>
        <div class="flex items-center justify-between gap-2 border-t px-4 py-3">
          <button type="button" class="btn btn-danger-ghost btn-sm hidden" data-ref="delete">Sil</button>
          <button type="button" class="btn btn-ghost btn-sm ml-auto hidden" data-ref="unpublish">Yayından kaldır</button>
        </div>
      </section>

      <section class="card">
        <h2 class="card-title border-b px-4 py-3 text-sm">Sayfa özellikleri</h2>
        <div class="space-y-4 p-4">
          <div data-field="parent_id">
            <label class="label" for="page-parent">Üst sayfa</label>
            <select id="page-parent" class="input" data-ref="parent"><option value="">(Yok, en üst seviye)</option></select>
            <p class="field-help text-xs">Alt sayfa olursa adresi üst sayfanın altına girer: /kurumsal/ekibimiz</p>
          </div>
          <div data-field="template">
            <label class="label" for="page-template">Şablon</label>
            <select id="page-template" class="input" data-ref="template"></select>
          </div>
          <div data-field="sort_order">
            <label class="label" for="page-order">Sıra</label>
            <input id="page-order" type="number" min="0" max="9999" class="input w-28" value="0" data-ref="order">
            <p class="field-help text-xs">Küçük sayı önce gelir (aynı seviyedeki sayfalar arasında).</p>
          </div>
        </div>
      </section>

      <section class="card" data-field="image">
        <h2 class="card-title border-b px-4 py-3 text-sm">Öne çıkan görsel</h2>
        <div class="p-4">
          <button type="button" class="flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-dashed bg-muted/50 text-sm text-muted-foreground transition hover:bg-muted" data-ref="imagePick">
            Görsel seç
          </button>
          <button type="button" class="btn btn-ghost btn-sm mt-2 hidden w-full text-destructive" data-ref="imageClear">Görseli kaldır</button>
          <p class="field-help text-xs">Sayfanın üstünde ve sosyal medyada paylaşılınca görünür.</p>
        </div>
      </section>

      <section class="card" data-field="meta_description">
        <h2 class="card-title border-b px-4 py-3 text-sm">Arama motorları</h2>
        <div class="space-y-3 p-4">
          <div>
            <div class="flex items-center justify-between">
              <label class="label" for="page-meta">Açıklama</label>
              <span class="text-xs tabular-nums text-muted-foreground" data-ref="metaCount">0/160</span>
            </div>
            <textarea id="page-meta" class="input" rows="3" maxlength="300" placeholder="Boşsa içeriğin başından alınır." data-ref="meta"></textarea>
          </div>
          <!-- Google önizlemesi -->
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

<script type="module" src="<?= View::e(Module::asset('Pages', 'admin/page-editor.js')) ?>"></script>
