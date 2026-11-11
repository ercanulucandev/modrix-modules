<?php
// Admin > Sayfalar: liste iskeleti. İçeriği Assets/admin/pages-list.js API'den doldurur.
?>
<div data-pages-list data-edit-url="<?= View::e(Request::url('admin/pages/{id}/edit')) ?>">
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Sayfalar</h1>
      <p class="mt-1 text-sm text-muted-foreground">Hakkımızda, İletişim, KVKK gibi sabit içerikler. Haber ve duyurular için Yazılar'ı kullanın.</p>
    </div>
    <a href="<?= Router::url('admin.pages.create') ?>" class="btn btn-primary">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
      Yeni sayfa
    </a>
  </div>

  <?= View::render('admin/partials/content-languages', ['url' => 'admin/pages']) ?>
  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-1 text-sm" role="tablist" data-ref="filters">
      <?php foreach (['' => 'Tümü', 'published' => 'Yayında', 'draft' => 'Taslak'] as $key => $label): ?>
        <button type="button" class="rounded-md px-3 py-1.5 text-muted-foreground transition-colors hover:text-foreground data-[on]:bg-accent data-[on]:font-medium data-[on]:text-foreground"
                data-filter="<?= $key ?>" <?= $key === '' ? 'data-on' : '' ?>>
          <?= $label ?> <span class="ml-0.5 tabular-nums text-muted-foreground" data-count="<?= $key ?: 'total' ?>"></span>
        </button>
      <?php endforeach ?>
    </div>
    <div class="relative w-full max-w-xs">
      <svg class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="search" placeholder="Başlık ya da adreste ara…" class="input mt-0 pl-8" data-ref="search" aria-label="Ara">
    </div>
  </div>

  <div class="card overflow-x-auto">
    <table class="table">
      <thead><tr><th>Başlık</th><th class="hidden md:table-cell">Şablon</th><th>Durum</th><th class="hidden sm:table-cell">Son değişiklik</th></tr></thead>
      <tbody data-ref="rows">
        <tr><td colspan="4" class="py-10 text-center text-muted-foreground">Yükleniyor…</td></tr>
      </tbody>
    </table>
  </div>
  <p class="mt-3 text-xs text-muted-foreground">Sıralama ve üst sayfa düzenleyicideki "Sayfa özellikleri"nden değişir. Ana sayfa seçimi: Modüller &gt; Sayfalar &gt; Ayarlar.</p>
</div>

<script type="module" src="<?= View::e(Module::asset('Pages', 'admin/pages-list.js')) ?>"></script>
