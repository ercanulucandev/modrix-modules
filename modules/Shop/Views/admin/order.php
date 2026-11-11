<?php // Admin > Sipariş ayrıntısı. Değişkenler: $order, $items
use App\Modules\Shop\Services\Shop;
$o = $order;
$a = $o['address'];
$b = $o['billing'];
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
  <div class="flex items-center gap-2">
    <a href="<?= Router::url('admin.shop.orders') ?>" class="btn btn-ghost size-9 p-0" aria-label="Siparişlere dön"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
    <div><h1 class="text-2xl font-semibold tracking-tight">Sipariş #<?= View::e($o['number']) ?></h1><p class="text-sm text-muted-foreground"><?= View::date($o['created_at'], true) ?> · <?= View::e((string) $o['ip']) ?></p></div>
  </div>
  <a href="<?= Router::url('shop.order', ['token' => $o['token']]) ?>" target="_blank" class="btn btn-secondary btn-sm">Müşterinin gördüğü sayfa</a>
</div>

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
  <div class="space-y-6">
    <section class="card divide-y">
      <?php foreach ($items as $i): ?>
        <div class="flex justify-between gap-4 p-4 text-sm"><span><span class="font-medium"><?= View::e($i['name']) ?></span><?php if ($i['sku']): ?> <span class="text-xs text-muted-foreground"><?= View::e($i['sku']) ?></span><?php endif ?><span class="block text-muted-foreground"><?= Shop::money($i['price']) ?> × <?= $i['quantity'] ?></span></span><span class="font-medium"><?= Shop::money($i['total']) ?></span></div>
      <?php endforeach ?>
      <div class="p-4"><?= View::render('Shop::totals', ['subtotal' => Shop::cents($o['subtotal']), 'shipping' => Shop::cents($o['shipping_fee']), 'fee' => Shop::cents($o['payment_fee']), 'total' => Shop::cents($o['total'])]) ?></div>
    </section>
    <div class="grid gap-4 text-sm sm:grid-cols-2">
      <section class="card p-5"><p class="font-medium">Müşteri</p><p class="mt-2 text-muted-foreground"><?= View::e($o['customer_name']) ?><br><a href="mailto:<?= View::e($o['email']) ?>" class="hover:underline"><?= View::e($o['email']) ?></a><br><a href="tel:<?= View::e($o['phone']) ?>" class="hover:underline"><?= View::e($o['phone']) ?></a></p></section>
      <section class="card p-5"><p class="font-medium">Teslimat adresi</p><p class="mt-2 text-muted-foreground"><?= nl2br(View::e($a['line'] ?? '')) ?><br><?= View::e(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?> <?= View::e($a['zip'] ?? '') ?></p></section>
      <?php if ($b): ?><section class="card p-5"><p class="font-medium">Kurumsal fatura</p><p class="mt-2 text-muted-foreground"><?= View::e($b['company'] ?? '') ?><br><?= View::e(($b['tax_office'] ?? '') . ' · ' . ($b['tax_number'] ?? '')) ?></p></section><?php endif ?>
      <?php if ($o['note']): ?><section class="card p-5"><p class="font-medium">Müşteri notu</p><p class="mt-2 text-muted-foreground"><?= nl2br(View::e($o['note'])) ?></p></section><?php endif ?>
    </div>
  </div>

  <aside class="space-y-6">
    <section class="card space-y-2 p-5 text-sm">
      <p class="font-medium">Ödeme</p>
      <p><?= Shop::methodLabel($o['payment_method']) ?> · <span class="<?= $o['payment_status'] === 'paid' ? 'text-emerald-600' : 'text-muted-foreground' ?>"><?= Shop::PAYMENT_STATUSES[$o['payment_status']] ?></span></p>
      <?php if ($o['paid_at']): ?><p class="text-muted-foreground">Ödeme: <?= View::date($o['paid_at'], true) ?></p><?php endif ?>
      <?php if ($o['payment_ref']): ?><p class="text-muted-foreground">İşlem no: <span class="font-mono"><?= View::e($o['payment_ref']) ?></span></p><?php endif ?>
      <?php if ($o['payment_status'] !== 'paid' && !in_array($o['status'], ['cancelled', 'refunded'], true)): ?>
        <form method="post" action="<?= Router::url('admin.shop.orders.paid', ['id' => $o['id']]) ?>" class="pt-2" onsubmit="return confirm('Ödeme alındı olarak işaretlensin mi? Müşteriye e-posta gider.')"><?= View::csrf() ?>
          <button class="btn btn-primary btn-sm w-full">Ödeme alındı</button></form>
      <?php endif ?>
    </section>
    <form method="post" action="<?= Router::url('admin.shop.orders.update', ['id' => $o['id']]) ?>" class="card space-y-4 p-5">
      <?= View::csrf() ?>
      <?= View::render('partials/field', ['name' => 'status', 'label' => 'Durum', 'type' => 'select', 'value' => $o['status'], 'options' => Shop::STATUSES, 'help' => 'İptal / iade: ürünler stoğa geri eklenir.']) ?>
      <div class="grid grid-cols-2 gap-3">
        <?= View::render('partials/field', ['name' => 'cargo_company', 'label' => 'Kargo firması', 'value' => $o['cargo_company']]) ?>
        <?= View::render('partials/field', ['name' => 'tracking_number', 'label' => 'Takip no', 'value' => $o['tracking_number']]) ?>
      </div>
      <?= View::render('partials/field', ['name' => 'admin_note', 'label' => 'Not (sadece panelde)', 'type' => 'textarea', 'rows' => 2, 'value' => $o['admin_note']]) ?>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="notify" value="1" class="size-4" checked> Müşteriye e-posta gönder</label>
      <button class="btn btn-primary w-full">Kaydet</button>
    </form>
  </aside>
</div>
