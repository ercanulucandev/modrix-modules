<?php // Müşteriye randevu e-postası. Değişkenler: $a, $subject, $site, $url
use App\Modules\Booking\Services\Booking;
?>
<div style="font-family:system-ui,-apple-system,Segoe UI,sans-serif;max-width:520px;margin:0 auto;color:#18181b">
  <p><?= Lang::t('Merhaba') ?> <?= View::e($a['name']) ?>,</p>
  <p><strong><?= View::e($subject) ?></strong></p>
  <table style="width:100%;border-collapse:collapse;font-size:14px;margin:12px 0">
    <tr><td style="padding:6px 0;color:#71717a"><?= Lang::t('Hizmet') ?></td><td style="text-align:right"><?= View::e($a['service_name']) ?></td></tr>
    <tr><td style="padding:6px 0;color:#71717a"><?= Lang::t('Zaman') ?></td><td style="text-align:right"><strong><?= View::e(Booking::when($a['starts_at'])) ?></strong></td></tr>
    <?php if ($a['staff_name']): ?><tr><td style="padding:6px 0;color:#71717a"><?= Lang::t('Kiminle') ?></td><td style="text-align:right"><?= View::e($a['staff_name']) ?></td></tr><?php endif ?>
    <tr><td style="padding:6px 0;color:#71717a"><?= Lang::t('Durum') ?></td><td style="text-align:right"><?= Lang::t(Booking::STATUSES[$a['status']]) ?></td></tr>
  </table>
  <p><a href="<?= View::e($url) ?>" style="display:inline-block;padding:10px 18px;background:#18181b;color:#fff;border-radius:6px;text-decoration:none"><?= Lang::t('Randevumu görüntüle') ?></a></p>
  <p style="color:#71717a;font-size:13px"><?= View::e($site) ?></p>
</div>
