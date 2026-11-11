<?php // Admin > Siparişler. Değişkenler: $rows, $counts, $filter
use App\Modules\Shop\Services\Shop;
$badges = ['pending' => 'badge-yellow', 'processing' => 'badge-default', 'shipped' => 'badge-default', 'completed' => 'badge-green', 'cancelled' => 'badge-gray', 'refunded' => 'badge-gray'];
?>
<div class="mb-4 flex flex-wrap items-start justify-between gap-4"><h1 class="text-2xl font-semibold tracking-tight">Siparişler</h1>
  <a href="<?= Request::url('admin/modules/Shop/settings') ?>" class="btn btn-secondary">Mağaza ayarları</a></div>
<form class="mb-4 flex flex-wrap gap-2">
  <select name="status" class="input mt-0 w-auto" onchange="this.form.submit()"><option value="">Tümü</option>
    <?php foreach (Shop::STATUSES as $k => $label): ?><option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $label ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach ?></select>
  <input type="search" name="q" value="<?= View::e($filter['q']) ?>" placeholder="Sipariş no, ad, e-posta, telefon…" class="input mt-0 max-w-xs">
</form>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Sipariş</th><th>Müşteri</th><th>Tutar</th><th>Ödeme</th><th>Durum</th></tr></thead>
    <tbody>
      <?php foreach ($rows['data'] as $o): ?>
        <tr>
          <td><a href="<?= Router::url('admin.shop.orders.show', ['id' => $o['id']]) ?>" class="font-medium hover:underline">#<?= View::e($o['number']) ?></a><span class="block text-xs text-muted-foreground"><?= View::date($o['created_at'], true) ?></span></td>
          <td><?= View::e($o['customer_name']) ?><span class="block text-xs text-muted-foreground"><?= View::e($o['email']) ?></span></td>
          <td class="whitespace-nowrap font-medium"><?= Shop::money($o['total']) ?></td>
          <td class="whitespace-nowrap"><?= Shop::methodLabel($o['payment_method']) ?><span class="block text-xs <?= $o['payment_status'] === 'paid' ? 'text-emerald-600' : 'text-muted-foreground' ?>"><?= Shop::PAYMENT_STATUSES[$o['payment_status']] ?></span></td>
          <td><span class="badge <?= $badges[$o['status']] ?>"><?= Shop::STATUSES[$o['status']] ?></span></td>
        </tr>
      <?php endforeach ?>
      <?php if (!$rows['data']): ?><tr><td colspan="5" class="py-10 text-center text-muted-foreground">Sipariş yok.</td></tr><?php endif ?>
    </tbody>
  </table>
</div>
<div class="mt-4"><?= View::render('partials/pagination', ['page' => $rows]) ?></div>
