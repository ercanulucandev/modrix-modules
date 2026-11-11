<?php // Proje kartları. Değişken: $items
use App\Modules\Portfolio\Controllers\PortfolioController;
?>
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($items as $p): $cover = PortfolioController::cover($p); ?>
    <a href="<?= Router::url('portfolio.show', ['slug' => $p['slug']]) ?>" data-category="<?= View::e(trim((string) $p['category'])) ?>" class="card group block overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
      <div class="aspect-[4/3] overflow-hidden bg-muted">
        <?php if ($cover): ?><img src="<?= View::e($cover) ?>"<?= App\Services\ResponsiveImages::attrs($cover, '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw') ?> alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105"><?php endif ?>
      </div>
      <div class="p-5">
        <?php if ($p['category'] !== ''): ?><p class="text-xs font-medium uppercase tracking-wider text-muted-foreground"><?= View::e($p['category']) ?></p><?php endif ?>
        <h3 class="mt-1 text-lg font-semibold"><?= View::e($p['title']) ?></h3>
        <?php if ($p['summary'] !== ''): ?><p class="mt-1.5 line-clamp-2 text-sm text-muted-foreground"><?= View::e($p['summary']) ?></p><?php endif ?>
      </div>
    </a>
  <?php endforeach ?>
</div>
