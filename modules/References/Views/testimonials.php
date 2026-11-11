<?php // Yorum kartları. Değişken: $items ?>
<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($items as $t): $photo = App\Services\Collection::imageUrl($t['photo'] ?? null); $rating = (int) ($t['rating'] ?? 5); ?>
    <figure class="card flex flex-col p-6">
      <?php if ($rating > 0): ?><p class="text-amber-500" aria-label="<?= $rating ?> / 5"><?= str_repeat('★', $rating) ?><span class="text-muted-foreground/30"><?= str_repeat('★', 5 - $rating) ?></span></p><?php endif ?>
      <blockquote class="mt-3 flex-1 leading-7">“<?= nl2br(View::e($t['quote'])) ?>”</blockquote>
      <figcaption class="mt-5 flex items-center gap-3">
        <?php if ($photo): ?><img src="<?= View::e($photo) ?>" alt="" loading="lazy" class="size-10 rounded-full object-cover">
        <?php else: ?><span class="flex size-10 items-center justify-center rounded-full bg-muted font-semibold text-muted-foreground"><?= View::e(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></span><?php endif ?>
        <span><span class="block text-sm font-semibold"><?= View::e($t['name']) ?></span>
          <?php if (!empty($t['company'])): ?><span class="block text-xs text-muted-foreground"><?= View::e($t['company']) ?></span><?php endif ?></span>
      </figcaption>
    </figure>
  <?php endforeach ?>
</div>
