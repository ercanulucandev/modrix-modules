<?php // Sipariş e-postası (müşteriye). Değişkenler: $order, $items, $event (created | paid | status), $site, $url
use App\Modules\Shop\Services\Shop;
$o = $order;
$intro = match ($event) {
    'created' => 'Siparişinizi aldık, teşekkür ederiz.',
    'paid'    => 'Ödemeniz alındı; siparişiniz hazırlanıyor.',
    default   => 'Siparişinizin durumu güncellendi: <strong>' . Shop::STATUSES[$o['status']] . '</strong>.',
};
?>
<div style="font-family:system-ui,-apple-system,Segoe UI,sans-serif;max-width:560px;margin:0 auto;color:#18181b">
  <p>Merhaba <?= View::e($o['customer_name']) ?>,</p>
  <p><?= $intro ?></p>
  <?php if ($o['tracking_number']): ?><p>Kargo: <?= View::e((string) $o['cargo_company']) ?> · Takip no: <strong><?= View::e($o['tracking_number']) ?></strong></p><?php endif ?>
  <?php if ($event === 'created' && $o['payment_method'] === 'bank'): ?>
    <p><strong><?= Shop::money($o['total']) ?></strong> tutarı aşağıdaki hesaba gönderin, açıklamaya <strong><?= View::e($o['number']) ?></strong> yazın:</p>
    <pre style="background:#f4f4f5;padding:12px;border-radius:6px;white-space:pre-wrap"><?= View::e((string) Setting::get('shop_bank_accounts')) ?></pre>
  <?php endif ?>
  <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px">
    <?php foreach ($items as $i): ?>
      <tr><td style="padding:8px 0;border-bottom:1px solid #e4e4e7"><?= View::e($i['name']) ?> × <?= $i['quantity'] ?></td><td style="padding:8px 0;border-bottom:1px solid #e4e4e7;text-align:right"><?= Shop::money($i['total']) ?></td></tr>
    <?php endforeach ?>
    <tr><td style="padding:8px 0">Kargo</td><td style="text-align:right"><?= (float) $o['shipping_fee'] ? Shop::money($o['shipping_fee']) : 'Ücretsiz' ?></td></tr>
    <?php if ((float) $o['payment_fee']): ?><tr><td style="padding:8px 0">Kapıda ödeme</td><td style="text-align:right"><?= Shop::money($o['payment_fee']) ?></td></tr><?php endif ?>
    <tr><td style="padding:8px 0;font-weight:600">Toplam</td><td style="text-align:right;font-weight:600"><?= Shop::money($o['total']) ?></td></tr>
  </table>
  <p><a href="<?= View::e($url) ?>" style="display:inline-block;padding:10px 18px;background:#18181b;color:#fff;border-radius:6px;text-decoration:none">Siparişimi görüntüle</a></p>
  <p style="color:#71717a;font-size:13px">Sipariş #<?= View::e($o['number']) ?> · <?= View::e($site) ?></p>
</div>
