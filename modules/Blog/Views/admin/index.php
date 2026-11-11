<?php
// Admin > Yazılar: liste iskeleti. İçeriği Assets/admin/posts-list.js API'den doldurur.
use App\Modules\Blog\Models\Category;
?>
<div data-posts-list data-edit-url="<?= View::e(Request::url('admin/blog/{id}/edit')) ?>">
  <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Yazılar</h1>
      <p class="mt-1 text-sm text-muted-foreground">Blog yazıları, haberler, duyurular. Sabit içerikler için Sayfalar'ı kullanın.</p>
    </div>
    <a href="<?= Router::url('admin.blog.create') ?>" class="btn btn-primary">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Yeni yazı
    </a>
  </div>
  <?= View::render('Blog::admin/tabs', ['current' => 'posts']) ?>
  <?= View::render('admin/partials/content-languages', ['url' => 'admin/blog']) ?>

  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-1 text-sm" data-ref="filters">
      <?php foreach (['' => 'Tümü', 'published' => 'Yayında', 'scheduled' => 'Zamanlanmış', 'draft' => 'Taslak'] as $key => $label): ?>
        <button type="button" class="rounded-md px-3 py-1.5 text-muted-foreground transition-colors hover:text-foreground data-[on]:bg-accent data-[on]:font-medium data-[on]:text-foreground"
                data-filter="<?= $key ?>" <?= $key === '' ? 'data-on' : '' ?>><?= $label ?> <span class="ml-0.5 tabular-nums" data-count="<?= $key ?: 'total' ?>"></span></button>
      <?php endforeach ?>
    </div>
    <div class="flex flex-wrap gap-2">
      <select class="input mt-0 w-auto" data-ref="category" aria-label="Kategori">
        <option value="">Tüm kategoriler</option>
        <?php foreach (Category::options() as $id => $name): ?><option value="<?= (int) $id ?>"><?= View::e($name) ?></option><?php endforeach ?>
      </select>
      <div class="relative w-60">
        <svg class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" placeholder="Başlık ya da adreste ara…" class="input mt-0 pl-8" data-ref="search" aria-label="Ara">
      </div>
    </div>
  </div>

  <div class="card overflow-x-auto">
    <table class="table">
      <thead><tr><th>Başlık</th><th class="hidden md:table-cell">Kategori</th><th>Durum</th><th class="hidden sm:table-cell">Tarih</th><th class="hidden lg:table-cell">Okunma</th></tr></thead>
      <tbody data-ref="rows"><tr><td colspan="5" class="py-10 text-center text-muted-foreground">Yükleniyor…</td></tr></tbody>
    </table>
  </div>
  <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row" data-ref="pagination"></div>
</div>

<script type="module" src="<?= View::e(Module::asset('Blog', 'admin/posts-list.js')) ?>"></script>
