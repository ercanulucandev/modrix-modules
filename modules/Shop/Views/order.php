<?php // Müşterinin sipariş sayfası (/order/{token}). Değişkenler: $order, $items, $bank, $canPay, $justPaid
use App\Modules\Shop\Services\Shop;
$o = $order;
$a = $o['address'];
?>
<div class="mx-auto max-w-3xl">
  <p class="text-sm text-muted-foreground">Sipariş #<?= View::e($o['number']) ?> · <?= View::date($o['created_at'], true) ?></p>
  <h1 class="mt-1 text-3xl font-semibold tracking-tight"><?= $o['payment_status'] === 'paid' || $o['payment_method'] === 'cod' ? 'Siparişiniz alındı' : Shop::STATUSES[$o['status']] ?></h1>
  <p class="mt-2"><span class="badge badge-default"><?= Shop::STATUSES[$o['status']] ?></span> <span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-green' : 'badge-gray' ?>"><?= Shop::PAYMENT_STATUSES[$o['payment_status']] ?></span></p>

  <?php if ($justPaid && $o['payment_status'] !== 'paid'): ?>
    <p class="mt-6 rounded-md border bg-muted p-4 text-sm"><?= Lang::t('Ödemeniz kontrol ediliyor; birkaç saniye içinde bu sayfayı yenileyin. Onay e-postası da gönderilecek.') ?></p>
  <?php endif ?>

  <?php if ($o['payment_method'] === 'bank' && $o['payment_status'] !== 'paid' && $o['status'] === 'pending'): ?>
    <section class="card mt-6 border-amber-300 p-6">
      <h2 class="font-semibold"><?= Lang::t('Havale / EFT bilgileri') ?></h2>
      <p class="mt-1 text-sm text-muted-foreground"><strong class="text-foreground"><?= Shop::money($o['total']) ?></strong> <?= Lang::t('tutarı aşağıdaki hesaba gönderin; açıklamaya') ?> <strong class="text-foreground"><?= View::e($o['number']) ?></strong> <?= Lang::t('yazın. Ödeme görülünce siparişiniz hazırlanır.') ?></p>
      <pre class="mt-4 whitespace-pre-wrap rounded-md bg-muted p-4 font-mono text-sm"><?= View::e($bank) ?></pre>
    </section>
  <?php endif ?>

  <?php if ($canPay): ?>
    <form method="post" action="<?= Router::url('shop.order.pay', ['token' => $o['token']]) ?>" class="card mt-6 flex flex-wrap items-center justify-between gap-3 p-5">
      <?= View::csrf() ?>
      <p class="text-sm"><?= Lang::t('Ödeme henüz tamamlanmadı.') ?></p>
      <button class="btn btn-primary">Kartla öde · <?= Shop::money($o['total']) ?></button>
    </form>
  <?php endif ?>

  <?php if ($o['tracking_number']): ?>
    <p class="card mt-6 p-5 text-sm"><?= Lang::t('Kargo:') ?> <strong><?= View::e($o['cargo_company'] ?: '') ?></strong> <?= Lang::t('· Takip no:') ?> <strong class="font-mono"><?= View::e($o['tracking_number']) ?></strong></p>
  <?php endif ?>

  <section class="card mt-6 divide-y">
    <?php foreach ($items as $i): ?>
      <div class="flex justify-between gap-4 p-4 text-sm"><span><?= View::e($i['name']) ?> <span class="text-muted-foreground">× <?= $i['quantity'] ?></span></span><span><?= Shop::money($i['total']) ?></span></div>
    <?php endforeach ?>
    <div class="p-4"><?= View::render('Shop::totals', ['subtotal' => Shop::cents($o['subtotal']), 'shipping' => Shop::cents($o['shipping_fee']), 'fee' => Shop::cents($o['payment_fee']), 'total' => Shop::cents($o['total'])]) ?></div>
  </section>

  <div class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
    <div class="card p-5"><p class="font-medium"><?= Lang::t('Teslimat') ?></p><p class="mt-2 text-muted-foreground"><?= View::e($o['customer_name']) ?><br><?= nl2br(View::e($a['line'] ?? '')) ?><br><?= View::e(($a['district'] ?? '') . ' / ' . ($a['city'] ?? '')) ?> <?= View::e($a['zip'] ?? '') ?><br><?= View::e($o['phone']) ?></p></div>
    <div class="card p-5"><p class="font-medium"><?= Lang::t('Ödeme') ?></p><p class="mt-2 text-muted-foreground"><?= Shop::methodLabel($o['payment_method']) ?><br><?= View::e($o['email']) ?></p></div>
  </div>
  <p class="mt-6 text-sm text-muted-foreground"><?= Lang::t('Bu sayfanın adresini saklayın; siparişinizin durumunu buradan takip edebilirsiniz.') ?></p>
</div>
