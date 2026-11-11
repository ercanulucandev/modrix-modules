<?php // Genel bakış: son siparişler. Değişken: $orders
use App\Modules\Shop\Services\Shop;
$badges = ['pending' => 'badge-yellow', 'processing' => 'badge-default', 'shipped' => 'badge-default', 'completed' => 'badge-green', 'cancelled' => 'badge-gray', 'refunded' => 'badge-gray'];
?>
<section class="card">
  <div class="flex items-center justify-between border-b px-5 py-4">
    <h2 class="card-title">Son siparişler</h2>
    <a href="<?= Router::url('admin.shop.orders') ?>" class="btn btn-secondary btn-sm">Tümü</a>
  </div>
  <?php if (!$orders): ?>
    <p class="px-5 py-10 text-center text-sm text-muted-foreground">Henüz sipariş yok.</p>
  <?php else: ?>
    <ul class="divide-y text-sm">
      <?php foreach ($orders as $o): ?>
        <li><a href="<?= Router::url('admin.shop.orders.show', ['id' => $o['id']]) ?>" class="flex items-center gap-3 px-5 py-3 hover:bg-muted/50">
          <span class="min-w-0 flex-1"><span class="font-medium"><?= View::e($o['customer_name']) ?></span> <span class="text-muted-foreground">#<?= View::e($o['number']) ?></span>
            <span class="block text-xs text-muted-foreground"><?= View::date($o['created_at'], true) ?></span></span>
          <span class="font-medium tabular-nums"><?= Shop::money($o['total']) ?></span>
          <span class="badge <?= $badges[$o['status']] ?>"><?= Shop::STATUSES[$o['status']] ?></span>
        </a></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
</section>
