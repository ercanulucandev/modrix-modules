<?php // /portfolio. Tema ezebilir: app/Themes/{Tema}/modules/Portfolio/index.php. Değişkenler: $title, $metaDescription, $items, $categories ?>
<div class="max-w-2xl">
  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
</div>
<?php if (count($categories) > 1): ?>
  <nav class="mt-6 flex flex-wrap gap-2 text-sm" data-portfolio-filter>
    <button type="button" class="rounded-full border px-3 py-1 font-medium" data-filter="" aria-pressed="true"><?= Lang::t('Tümü') ?></button>
    <?php foreach ($categories as $c): ?><button type="button" class="rounded-full border px-3 py-1 font-medium" data-filter="<?= View::e($c) ?>" aria-pressed="false"><?= View::e($c) ?></button><?php endforeach ?>
  </nav>
  <style>[data-portfolio-filter] [aria-pressed="true"] { background: var(--color-primary); color: var(--color-primary-foreground); border-color: var(--color-primary); }</style>
  <script>
    document.querySelector('[data-portfolio-filter]').addEventListener('click', (e) => {
      const b = e.target.closest('[data-filter]'); if (!b) return;
      document.querySelectorAll('[data-filter]').forEach((x) => x.setAttribute('aria-pressed', x === b));
      document.querySelectorAll('[data-category]').forEach((card) => { card.hidden = b.dataset.filter !== '' && card.dataset.category !== b.dataset.filter; });
    });
  </script>
<?php endif ?>
<div class="mt-8">
  <?php if ($items): ?><?= View::render('Portfolio::grid', ['items' => $items]) ?>
  <?php else: ?><p class="rounded-lg border border-dashed px-6 py-14 text-center text-muted-foreground"><?= Lang::t('Henüz proje eklenmedi.') ?></p><?php endif ?>
</div>
