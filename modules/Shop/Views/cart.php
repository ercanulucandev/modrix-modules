<?php // Sepet. Değişkenler: $cart, $methods
use App\Modules\Shop\Services\Shop;
$free = Setting::get('shop_free_over');
?>
<h1 class="text-3xl font-semibold tracking-tight"><?= Lang::t('Sepetim') ?></h1>
<?php if (!$cart['lines']): ?>
  <div class="mt-8 rounded-lg border border-dashed px-6 py-16 text-center">
    <p class="text-muted-foreground"><?= Lang::t('Sepetiniz boş.') ?></p>
    <a href="<?= Router::url('shop.index') ?>" class="btn btn-primary mt-5"><?= Lang::t('Alışverişe başla') ?></a>
  </div>
<?php else: ?>
  <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_22rem]">
    <form method="post" action="<?= Router::url('shop.cart.update') ?>" class="card divide-y">
      <?= View::csrf() ?>
      <?php foreach ($cart['lines'] as $l): ?>
        <div class="flex gap-4 p-4">
          <a href="<?= Router::url('shop.product', ['slug' => $l['product']['slug']]) ?>" class="size-20 shrink-0 overflow-hidden rounded-md border bg-muted"><?php if ($l['image']): ?><img src="<?= View::e($l['image']) ?>" alt="" class="size-full object-cover"><?php endif ?></a>
          <div class="min-w-0 flex-1">
            <a href="<?= Router::url('shop.product', ['slug' => $l['product']['slug']]) ?>" class="font-medium hover:underline"><?= View::e($l['name']) ?></a>
            <p class="text-sm text-muted-foreground"><?= Shop::money($l['price'] / 100) ?></p>
            <?php if (!$l['stock_ok']): ?><p class="mt-1 text-sm text-destructive">Stokta <?= max(0, (int) $l['stock']) ?> adet var; adedi azaltın.</p><?php endif ?>
            <div class="mt-2 flex items-center gap-2">
              <input type="number" name="quantity[<?= View::e($l['key']) ?>]" value="<?= $l['qty'] ?>" min="0" max="<?= App\Modules\Shop\Services\Cart::MAX_QTY ?>" class="input mt-0 h-9 w-20" aria-label="<?= View::e(Lang::t('Adet')) ?>" onchange="this.form.submit()">
              <button name="remove" value="<?= View::e($l['key']) ?>" class="btn btn-ghost btn-sm text-destructive"><?= Lang::t('Kaldır') ?></button>
            </div>
          </div>
          <p class="font-semibold"><?= Shop::money($l['total'] / 100) ?></p>
        </div>
      <?php endforeach ?>
      <div class="flex justify-between p-4"><a href="<?= Router::url('shop.index') ?>" class="btn btn-ghost btn-sm"><?= Lang::t('← Alışverişe devam') ?></a><button class="btn btn-secondary btn-sm"><?= Lang::t('Sepeti güncelle') ?></button></div>
    </form>

    <aside class="card space-y-4 p-5">
      <?= View::render('Shop::totals', ['subtotal' => $cart['subtotal'], 'shipping' => $cart['shipping'], 'total' => $cart['subtotal'] + $cart['shipping']]) ?>
      <?php if ($cart['shipping'] && $free !== null && $free !== ''): ?>
        <p class="rounded-md bg-muted p-3 text-xs"><?= Lang::t('Kargonun bedava olması için') ?> <strong><?= Shop::money(Shop::cents($free) / 100 - $cart['subtotal'] / 100) ?></strong> <?= Lang::t('daha ekleyin.') ?></p>
      <?php endif ?>
      <?php if ($methods): ?><a href="<?= Router::url('shop.checkout') ?>" class="btn btn-primary h-11 w-full"><?= Lang::t('Ödemeye geç') ?></a>
      <?php else: ?><p class="text-sm text-destructive"><?= Lang::t('Şu an sipariş alınamıyor (ödeme yöntemi tanımlanmamış).') ?></p><?php endif ?>
    </aside>
  </div>
<?php endif ?>
