<?php
// Yazı. Tema ezebilir: app/Themes/{Tema}/modules/Blog/show.php
// Değişkenler: $post, $category (ya da null), $tags, $related, $isDraft (taslak/zamanlanmış: sadece admin görür)
use App\Modules\Blog\Models\Post;
$cover = Post::cover($post);
?>
<article class="mx-auto max-w-2xl">
  <?php if (!empty($isDraft)): ?>
    <div class="alert alert-warning"><?= Lang::t('Bu yazı') ?> <strong><?= $post['status'] === 'draft' ? 'taslak' : View::date($post['published_at'], true) . ' tarihinde yayına girecek' ?></strong><?= Lang::t('; sadece siz görüyorsunuz.') ?></div>
  <?php endif ?>
  <a href="<?= Router::url('blog.index') ?>" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><?= Lang::t('← Blog') ?></a>
  <?php if ($category): ?>
    <p class="mt-6"><a href="<?= Router::url('blog.category', ['slug' => $category['slug']]) ?>" class="badge hover:bg-muted"><?= View::e($category['name']) ?></a></p>
  <?php endif ?>
  <h1 class="<?= $category ? 'mt-3' : 'mt-6' ?> text-4xl font-semibold tracking-tight"><?= View::e($post['title']) ?></h1>
  <p class="mt-3 text-sm text-muted-foreground"><?= View::date($post['published_at'] ?? $post['created_at'], true) ?><?= Setting::bool('blog_show_views') ? ' · ' . ((int) $post['views'] + 1) . ' okunma' : '' ?></p>
  <?php if ($post['image']): ?><img src="<?= View::e($cover) ?>"<?= App\Services\ResponsiveImages::attrs($cover, '(max-width: 860px) 100vw, 800px') ?> alt="" class="mt-8 aspect-[16/9] w-full rounded-lg border object-cover"><?php endif ?>
  <div class="prose-content mt-8 border-t pt-8"><?= Hook::filter('content.html', Html::render($post['body']), $post) ?></div>

  <?php if ($tags): ?>
    <div class="mt-10 flex flex-wrap gap-2">
      <?php foreach ($tags as $t): ?><a href="<?= Router::url('blog.tag', ['slug' => $t['slug']]) ?>" class="badge hover:bg-muted">#<?= View::e($t['name']) ?></a><?php endforeach ?>
    </div>
  <?php endif ?>
</article>

<?php if ($related): ?>
  <section class="mx-auto mt-16 max-w-4xl border-t pt-10">
    <h2 class="text-xl font-semibold tracking-tight"><?= Lang::t('İlgili yazılar') ?></h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
      <?php foreach ($related as $r): $rc = Post::cover($r); ?>
        <a href="<?= Router::url('blog.show', ['slug' => $r['slug']]) ?>" class="card group overflow-hidden transition hover:shadow-md">
          <?php if ($rc): ?><img src="<?= View::e($rc) ?>" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover"><?php endif ?>
          <div class="p-4">
            <p class="text-xs text-muted-foreground"><?= View::date($r['published_at']) ?></p>
            <h3 class="mt-1 font-semibold leading-snug group-hover:underline"><?= View::e($r['title']) ?></h3>
          </div>
        </a>
      <?php endforeach ?>
    </div>
  </section>
<?php endif ?>
