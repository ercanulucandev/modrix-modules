<?php // Genel bakış: yaklaşan randevular. Değişken: $rows
use App\Modules\Booking\Services\Booking;
?>
<section class="card">
  <div class="flex items-center justify-between border-b px-5 py-4">
    <h2 class="card-title">Yaklaşan randevular</h2>
    <a href="<?= Router::url('admin.booking') ?>" class="btn btn-secondary btn-sm">Tümü</a>
  </div>
  <?php if (!$rows): ?>
    <p class="px-5 py-10 text-center text-sm text-muted-foreground">Yaklaşan randevu yok.</p>
  <?php else: ?>
    <ul class="divide-y text-sm">
      <?php foreach ($rows as $a): ?>
        <li><a href="<?= Router::url('admin.booking.show', ['id' => $a['id']]) ?>" class="flex items-center gap-3 px-5 py-3 hover:bg-muted/50">
          <span class="w-28 shrink-0 text-xs text-muted-foreground"><?= date('d.m', strtotime($a['starts_at'])) ?> <span class="font-mono text-sm font-semibold text-foreground"><?= substr($a['starts_at'], 11, 5) ?></span></span>
          <span class="min-w-0 flex-1"><span class="font-medium"><?= View::e($a['name']) ?></span><span class="block truncate text-xs text-muted-foreground"><?= View::e($a['service_name']) ?><?= $a['staff_name'] ? ' · ' . View::e($a['staff_name']) : '' ?></span></span>
          <?php if ($a['status'] === 'pending'): ?><span class="badge badge-yellow">Onay bekliyor</span><?php endif ?>
        </a></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</section>
