<?php // Sepet/ödeme özeti. Değişkenler: $subtotal, $shipping, $fee (kuruş), $total
use App\Modules\Shop\Services\Shop;
?>
<dl class="space-y-2 text-sm">
  <div class="flex justify-between"><dt class="text-muted-foreground"><?= Lang::t('Ara toplam') ?></dt><dd><?= Shop::money($subtotal / 100) ?></dd></div>
  <div class="flex justify-between"><dt class="text-muted-foreground"><?= Lang::t('Kargo') ?></dt><dd><?= $shipping ? Shop::money($shipping / 100) : 'Ücretsiz' ?></dd></div>
  <?php if (!empty($fee)): ?><div class="flex justify-between"><dt class="text-muted-foreground"><?= Lang::t('Kapıda ödeme') ?></dt><dd><?= Shop::money($fee / 100) ?></dd></div><?php endif ?>
  <div class="flex justify-between border-t pt-3 text-base font-semibold"><dt><?= Lang::t('Toplam') ?></dt><dd data-total><?= Shop::money($total / 100) ?></dd></div>
</dl>
