<?php
// Sayfa: varsayılan şablon. Tema ezebilir: app/Themes/{Tema}/modules/Pages/show.php
// Değişkenler: $page, $ancestors (üst sayfalar), $children (yayındaki alt sayfalar), $isDraft, $isFront
$image = $page['image'] ? Request::url('media/' . $page['image']) : null;
?>
<article class="mx-auto max-w-3xl">
  <?php if ($isDraft): ?>
    <div class="alert alert-warning">Bu sayfa <strong>taslak</strong>; sadece siz görüyorsunuz. Yayınlamak için düzenleyicide durumu "Yayında" yapın.</div>
  <?php endif ?>

  <?php if ($ancestors): ?>
    <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground" aria-label="Konum">
      <a href="<?= Request::url('/') ?>" class="hover:text-foreground">Ana sayfa</a>
      <?php foreach ($ancestors as $a): ?>
        <span aria-hidden="true">/</span><a href="<?= Request::url($a['path']) ?>" class="hover:text-foreground"><?= View::e($a['title']) ?></a>
      <?php endforeach ?>
      <span aria-hidden="true">/</span><span class="text-foreground"><?= View::e($page['title']) ?></span>
    </nav>
  <?php endif ?>

  <?php if (!$isFront): ?>
    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($page['title']) ?></h1>
  <?php endif ?>

  <?php if ($image): ?>
    <img src="<?= View::e($image) ?>" alt="" class="mt-8 aspect-[16/9] w-full rounded-lg border object-cover">
  <?php endif ?>

  <?php if (trim((string) $page['body']) !== ''): ?>
    <div class="prose-content mt-8"><?= Hook::filter('content.html', Html::render($page['body']), $page) ?></div>
  <?php endif ?>

  <?php if ($children): ?>
    <div class="mt-12 grid gap-4 border-t pt-10 sm:grid-cols-2">
      <?php foreach ($children as $child): ?>
        <a href="<?= Request::url($child['path']) ?>" class="card group p-5 transition-colors hover:bg-muted/40">
          <p class="font-semibold group-hover:underline group-hover:underline-offset-4"><?= View::e($child['title']) ?></p>
          <?php if ($text = $child['meta_description'] ?: Html::text($child['body'], 120)): ?>
            <p class="mt-1.5 line-clamp-2 text-sm text-muted-foreground"><?= View::e($text) ?></p>
          <?php endif ?>
        </a>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</article>
