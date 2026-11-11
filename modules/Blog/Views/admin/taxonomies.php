<?php // Admin > Yazılar > Kategoriler ve etiketler. Değişkenler: $categories, $counts, $tags, $editing ?>
<div class="mb-4">
  <h1 class="text-2xl font-semibold tracking-tight">Yazılar</h1>
  <p class="mt-1 text-sm text-muted-foreground">Kategoriler yazıları gruplar (/blog/kategori/...); etiketler yazı düzenleyicide eklenir.</p>
</div>
<?= View::render('Blog::admin/tabs', ['current' => 'taxonomies']) ?>

<div class="grid items-start gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
  <form method="post" action="<?= $editing ? Router::url('admin.blog.categories.update', ['id' => $editing['id']]) : Router::url('admin.blog.categories.store') ?>" class="card space-y-4 p-5">
    <?= View::csrf() ?>
    <h2 class="font-semibold"><?= $editing ? 'Kategoriyi düzenle' : 'Yeni kategori' ?></h2>
    <?= View::render('partials/field', ['name' => 'name', 'label' => 'Ad', 'value' => $editing['name'] ?? null, 'required' => true]) ?>
    <?= View::render('partials/field', ['name' => 'slug', 'label' => 'Adres', 'value' => $editing['slug'] ?? null, 'placeholder' => 'addan-uretilir', 'help' => '/blog/kategori/...']) ?>
    <?= View::render('partials/field', ['name' => 'description', 'label' => 'Açıklama', 'type' => 'textarea', 'rows' => 2, 'value' => $editing['description'] ?? null, 'help' => 'Kategori sayfasında başlığın altında.']) ?>
    <?= View::render('partials/field', ['name' => 'sort_order', 'label' => 'Sıra', 'type' => 'number', 'value' => $editing['sort_order'] ?? 0]) ?>
    <div class="flex gap-2">
      <button class="btn btn-primary"><?= $editing ? 'Kaydet' : 'Ekle' ?></button>
      <?php if ($editing): ?><a href="<?= Router::url('admin.blog.categories') ?>" class="btn btn-ghost">Vazgeç</a><?php endif ?>
    </div>
  </form>

  <div class="space-y-6">
    <div class="card overflow-x-auto">
      <table class="table">
        <thead><tr><th>Kategori</th><th>Yazı</th><th class="text-right">İşlem</th></tr></thead>
        <tbody>
          <?php foreach ($categories as $c): ?>
            <tr>
              <td><span class="font-medium"><?= View::e($c['name']) ?></span><p class="font-mono text-xs text-muted-foreground">/blog/kategori/<?= View::e($c['slug']) ?></p></td>
              <td class="tabular-nums"><?= (int) ($counts[$c['id']] ?? 0) ?></td>
              <td>
                <div class="flex justify-end gap-2">
                  <a href="<?= Request::url('admin/blog/categories?edit=' . (int) $c['id']) ?>" class="btn btn-secondary btn-sm">Düzenle</a>
                  <form method="post" action="<?= Router::url('admin.blog.categories.delete', ['id' => $c['id']]) ?>" data-confirm="<?= View::e($c['name']) ?> silinsin mi? Yazıları silinmez, kategorisiz kalır.">
                    <?= View::csrf() ?><button class="btn btn-danger-ghost btn-sm">Sil</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
          <?php if (!$categories): ?><tr><td colspan="3" class="py-8 text-center text-muted-foreground">Henüz kategori yok.</td></tr><?php endif ?>
        </tbody>
      </table>
    </div>

    <div class="card p-5">
      <h2 class="font-semibold">Etiketler</h2>
      <?php if (!$tags): ?>
        <p class="mt-2 text-sm text-muted-foreground">Henüz etiket yok. Yazı düzenleyicide "Etiketler" alanından eklenir.</p>
      <?php else: ?>
        <div class="mt-3 flex flex-wrap gap-2">
          <?php foreach ($tags as $t): ?>
            <form method="post" action="<?= Router::url('admin.blog.tags.delete', ['id' => $t['id']]) ?>" data-confirm="#<?= View::e($t['name']) ?> etiketi tüm yazılardan kaldırılsın mı?" class="inline-flex items-center gap-1 rounded-full border py-0.5 pl-3 pr-1 text-sm">
              <?= View::csrf() ?>
              <a href="<?= Router::url('blog.tag', ['slug' => $t['slug']]) ?>" target="_blank" class="hover:underline">#<?= View::e($t['name']) ?></a>
              <span class="text-xs text-muted-foreground"><?= (int) $t['posts'] ?></span>
              <button class="flex size-5 items-center justify-center rounded-full text-muted-foreground hover:bg-destructive/10 hover:text-destructive" title="Sil" aria-label="Sil">×</button>
            </form>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>
