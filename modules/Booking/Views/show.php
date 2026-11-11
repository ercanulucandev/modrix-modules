<?php // Müşterinin randevu sayfası. Değişken: $a
use App\Modules\Booking\Services\Booking;
$badge = ['pending' => 'badge-yellow', 'confirmed' => 'badge-green', 'completed' => 'badge-gray', 'cancelled' => 'badge-gray', 'no_show' => 'badge-gray'][$a['status']];
?>
<div class="mx-auto max-w-xl">
  <h1 class="text-3xl font-semibold tracking-tight"><?= Lang::t('Randevunuz') ?></h1>
  <p class="mt-2"><span class="badge <?= $badge ?>"><?= Lang::t(Booking::STATUSES[$a['status']]) ?></span></p>
  <dl class="card mt-6 divide-y text-sm">
    <div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= Lang::t('Hizmet') ?></dt><dd class="text-right font-medium"><?= View::e($a['service_name']) ?></dd></div>
    <div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= Lang::t('Zaman') ?></dt><dd class="text-right font-medium"><?= View::e(Booking::when($a['starts_at'])) ?></dd></div>
    <?php if ($a['staff_name']): ?><div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= Lang::t('Kiminle') ?></dt><dd class="text-right"><?= View::e($a['staff_name']) ?></dd></div><?php endif ?>
    <?php if ($a['price'] !== null): ?><div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= Lang::t('Ücret') ?></dt><dd class="text-right"><?= number_format((float) $a['price'], 2, ',', '.') ?> ₺</dd></div><?php endif ?>
    <div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= Lang::t('Ad soyad') ?></dt><dd class="text-right"><?= View::e($a['name']) ?></dd></div>
  </dl>
  <?php if (Booking::canCancel($a)): ?>
    <form method="post" action="<?= Router::url('booking.cancel', ['token' => $a['token']]) ?>" class="mt-6" onsubmit="return confirm(<?= View::e(json_encode(Lang::t('Randevu iptal edilsin mi?'))) ?>)">
      <?= View::csrf() ?><button class="btn btn-secondary"><?= Lang::t('Randevuyu iptal et') ?></button>
    </form>
  <?php endif ?>
  <p class="mt-6 text-sm text-muted-foreground"><?= Lang::t('Bu sayfanın bağlantısı e-postanızda da var.') ?></p>
</div>
