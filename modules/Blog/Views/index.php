<?php
// Blog listesi (tüm yazılar, kategori ya da etiket). Tema ezebilir: app/Themes/{Tema}/modules/Blog/index.php
// Değişkenler: $title, $metaDescription, $posts (sayfalı; category_name, category_slug ile), $categories, $category, $tag
use App\Modules\Blog\Models\Post;
?>
<div class="mb-8 border-b pb-6">
  <?php if ($category || $tag): ?><a href="<?= Router::url('blog.index') ?>" class="text-sm text-muted-foreground hover:text-foreground"><?= Lang::t('← Tüm yazılar') ?></a><?php endif ?>
  <h1 class="mt-1 text-3xl font-semibold tracking-tight"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-2 max-w-2xl leading-7 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
  <?php if ($categories): ?>
    <nav class="mt-5 flex flex-wrap gap-2 text-sm">
      <a href="<?= Router::url('blog.index') ?>" class="rounded-full border px-3 py-1 <?= !$category && !$tag ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted' ?>"><?= Lang::t('Tümü') ?></a>
      <?php foreach ($categories as $c): ?>
        <a href="<?= Router::url('blog.category', ['slug' => $c['slug']]) ?>" class="rounded-full border px-3 py-1 <?= ($category['id'] ?? 0) === $c['id'] ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted' ?>"><?= View::e($c['name']) ?></a>
      <?php endforeach ?>
    </nav>
  <?php endif ?>
</div>

<?php if (!$posts['data']): ?>
  <div class="rounded-lg border border-dashed px-6 py-16 text-center text-sm text-muted-foreground"><?= Lang::t('Henüz yazı yok.') ?></div>
<?php else: ?>
  <div class="divide-y">
    <?php foreach ($posts['data'] as $post): $url = Router::url('blog.show', ['slug' => $post['slug']]); $cover = Post::cover($post); ?>
      <article class="grid gap-5 py-6 first:pt-0 <?= $cover ? 'sm:grid-cols-[12rem_1fr]' : '' ?>">
        <?php if ($cover): ?><a href="<?= $url ?>"><img src="<?= View::e($cover) ?>"<?= App\Services\ResponsiveImages::attrs($cover, '(max-width: 640px) 100vw, 12rem') ?> alt="" loading="lazy" class="aspect-[4/3] w-full rounded-md border object-cover"></a><?php endif ?>
        <div>
          <p class="text-xs text-muted-foreground">
            <?php if ($post['category_name']): ?><a href="<?= Router::url('blog.category', ['slug' => $post['category_slug']]) ?>" class="font-medium text-foreground hover:underline"><?= View::e($post['category_name']) ?></a> · <?php endif ?>
            <?= View::date($post['published_at']) ?><?= Setting::bool('blog_show_views') ? ' · ' . (int) $post['views'] . ' okunma' : '' ?>
          </p>
          <h2 class="mt-1.5 text-xl font-semibold tracking-tight"><a href="<?= $url ?>" class="hover:underline hover:underline-offset-4"><?= View::e($post['title']) ?></a></h2>
          <p class="mt-2 leading-7 text-muted-foreground"><?= View::e($post['excerpt'] ?: Html::text($post['body'], 220)) ?></p>
          <a href="<?= $url ?>" class="mt-3 inline-flex items-center gap-1 text-sm font-medium hover:underline hover:underline-offset-4"><?= Lang::t('Devamını oku →') ?></a>
        </div>
      </article>
    <?php endforeach ?>
  </div>
  <?= View::render('partials/pagination', ['page' => $posts]) ?>
<?php endif ?>
