<?php // Proje sayfası. Değişkenler: $project, $others
$p = $project;
$facts = array_filter(['Müşteri' => $p['client'], 'Yıl' => $p['year'], 'Kategori' => $p['category']]);
?>
<a href="<?= Router::url('portfolio.index') ?>" class="text-sm text-muted-foreground hover:text-foreground">← <?= View::e(Setting::get('portfolio_title', 'Projelerimiz')) ?></a>
<div class="mt-3 grid gap-8 lg:grid-cols-[2fr_1fr]">
  <div>
    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($p['title']) ?></h1>
    <?php if ($p['summary'] !== ''): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($p['summary']) ?></p><?php endif ?>
    <?php if ($p['description'] !== ''): ?><div class="mt-6 leading-8"><?= nl2br(View::e($p['description'])) ?></div><?php endif ?>
  </div>
  <?php if ($facts || $p['link'] !== ''): ?>
    <aside class="card h-fit space-y-3 p-5 text-sm">
      <?php foreach ($facts as $label => $value): ?><p><span class="block text-xs text-muted-foreground"><?= $label ?></span><?= View::e($value) ?></p><?php endforeach ?>
      <?php if ($p['link'] !== ''): ?><a href="<?= View::e($p['link']) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm w-full"><?= Lang::t('Projeyi gör') ?></a><?php endif ?>
    </aside>
  <?php endif ?>
</div>
<?php if ($p['images']): ?><div class="mt-10"><?= View::render('Portfolio::gallery', ['images' => $p['images'], 'title' => $p['title']]) ?></div><?php endif ?>
<?php if ($others): ?>
  <h2 class="mt-16 text-xl font-semibold tracking-tight"><?= Lang::t('Diğer projeler') ?></h2>
  <div class="mt-5"><?= View::render('Portfolio::grid', ['items' => $others]) ?></div>
<?php endif ?>
