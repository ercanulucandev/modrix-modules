<?php // Logo ızgarası. Değişken: $items ?>
<div class="grid grid-cols-2 items-center gap-6 sm:grid-cols-3 lg:grid-cols-5">
  <?php foreach ($items as $c): $logo = App\Services\Collection::imageUrl($c['logo'] ?? null); if (!$logo) continue;
    $img = '<img src="' . View::e($logo) . '" alt="' . View::e($c['name']) . '" loading="lazy" class="mx-auto max-h-12 w-auto object-contain opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0">'; ?>
    <div class="flex h-20 items-center justify-center rounded-lg border bg-card p-4">
      <?= !empty($c['url']) ? '<a href="' . View::e($c['url']) . '" target="_blank" rel="noopener" title="' . View::e($c['name']) . '">' . $img . '</a>' : $img ?>
    </div>
  <?php endforeach ?>
</div>
