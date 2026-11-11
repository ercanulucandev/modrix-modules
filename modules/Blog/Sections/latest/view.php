<?php
// Son yazılar. $s: title, text, count, link_text. Kapak: yazıdaki ilk görsel.
use App\Modules\Blog\Models\Post;

$posts = Post::published((int) $s['count']);
if (!$posts) return;
?>
<section class="py-14 sm:py-16">
  <div class="wrap">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
        <?php if ($s['text'] !== ''): ?><p class="mt-2 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
      </div>
      <?php if ($s['link_text'] !== ''): ?>
        <a href="<?= Router::url('blog.index') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium hover:underline" <?= $edit('link_text') ?>><?= View::e($s['link_text']) ?> <?= Icon::svg('arrow', 'size-4') ?></a>
      <?php endif ?>
    </div>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($posts as $post):
        $cover = App\Modules\Blog\Models\Post::cover($post); ?>
        <a href="<?= Router::url('blog.show', ['slug' => $post['slug']]) ?>" class="card group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
          <?php if ($cover): ?><img src="<?= View::e($cover) ?>" alt="" loading="lazy" class="aspect-[16/9] w-full object-cover"><?php endif ?>
          <div class="flex flex-1 flex-col p-5">
            <p class="text-xs text-muted-foreground"><?= View::date($post['published_at'] ?? $post['created_at']) ?></p>
            <h3 class="mt-2 font-semibold leading-snug group-hover:underline group-hover:underline-offset-4"><?= View::e($post['title']) ?></h3>
            <p class="mt-2 line-clamp-2 text-sm text-muted-foreground"><?= View::e(Html::text($post['body'], 140)) ?></p>
          </div>
        </a>
      <?php endforeach ?>
    </div>
  </div>
</section>
