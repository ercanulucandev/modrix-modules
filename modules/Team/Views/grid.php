<?php // Üye kartları. Değişkenler: $items, $columns (3 | 4)
use App\Services\Collection;
$links = ['linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'x' => 'X'];
?>
<div class="grid gap-6 sm:grid-cols-2 <?= ($columns ?? 3) == 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?>">
  <?php foreach ($items as $m): $photo = Collection::imageUrl($m['photo'] ?? null); ?>
    <article class="card overflow-hidden text-center">
      <div class="aspect-[4/5] bg-muted">
        <?php if ($photo): ?><img src="<?= View::e($photo) ?>"<?= App\Services\ResponsiveImages::attrs($photo, '(max-width: 640px) 100vw, 33vw') ?> alt="<?= View::e($m['name']) ?>" loading="lazy" class="size-full object-cover">
        <?php else: ?><div class="flex size-full items-center justify-center text-5xl font-semibold text-muted-foreground/40"><?= View::e(mb_strtoupper(mb_substr($m['name'], 0, 1))) ?></div><?php endif ?>
      </div>
      <div class="p-5">
        <h3 class="text-lg font-semibold"><?= View::e($m['name']) ?></h3>
        <?php if (!empty($m['role'])): ?><p class="text-sm text-muted-foreground"><?= View::e($m['role']) ?></p><?php endif ?>
        <?php if (!empty($m['bio'])): ?><p class="mt-3 text-sm leading-6 text-muted-foreground"><?= nl2br(View::e($m['bio'])) ?></p><?php endif ?>
        <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm">
          <?php if (!empty($m['email'])): ?><a href="mailto:<?= View::e($m['email']) ?>" class="font-medium hover:underline"><?= Lang::t('E-posta') ?></a><?php endif ?>
          <?php foreach ($links as $key => $label): if (empty($m[$key])) continue; ?>
            <a href="<?= View::e($m[$key]) ?>" target="_blank" rel="noopener" class="font-medium hover:underline"><?= $label ?></a>
          <?php endforeach ?>
        </div>
      </div>
    </article>
  <?php endforeach ?>
</div>
