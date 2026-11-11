<?php // Admin > Ürünler. Değişkenler: $rows, $categories, $filter
use App\Modules\Shop\Services\Shop;
?>
<div class="mb-4 flex flex-wrap items-start justify-between gap-4">
  <h1 class="text-2xl font-semibold tracking-tight">Ürünler</h1>
  <div class="flex gap-2"><a href="<?= Request::url('admin/modules/Shop/settings') ?>" class="btn btn-secondary">Mağaza ayarları</a>
  <a href="<?= Router::url('admin.shop.products.create') ?>" class="btn btn-primary">Ürün ekle</a></div>
</div>
<form class="mb-4 flex flex-wrap gap-2">
  <select name="category" class="input mt-0 w-auto" onchange="this.form.submit()"><option value="">Tüm kategoriler</option>
    <?php foreach ($categories as $id => $name): ?><option value="<?= $id ?>" <?= $filter['category'] === (int) $id ? 'selected' : '' ?>><?= View::e($name) ?></option><?php endforeach ?></select>
  <select name="status" class="input mt-0 w-auto" onchange="this.form.submit()">
    <?php foreach (['' => 'Hepsi', 'published' => 'Satışta', 'draft' => 'Taslak', 'out' => 'Tükenenler'] as $k => $label): ?><option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select>
  <input type="search" name="q" value="<?= View::e($filter['q']) ?>" placeholder="Ad ya da ürün kodu…" class="input mt-0 max-w-xs">
</form>
<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>Ürün</th><th>Fiyat</th><th>Stok</th><th class="hidden md:table-cell">Kategori</th><th>Durum</th></tr></thead>
    <tbody>
      <?php foreach ($rows['data'] as $p): $img = Shop::image($p); $variants = Shop::variants($p['id']); [$min, $max] = Shop::priceRange($p, $variants);
        $stock = $variants ? (in_array(null, array_column($variants, 'stock'), true) ? null : array_sum(array_column($variants, 'stock'))) : $p['stock']; ?>
        <tr>
          <td><a href="<?= Router::url('admin.shop.products.edit', ['id' => $p['id']]) ?>" class="flex items-center gap-3 font-medium hover:underline">
            <span class="size-10 shrink-0 overflow-hidden rounded-md border bg-muted"><?php if ($img): ?><img src="<?= View::e($img) ?>" alt="" class="size-full object-cover"><?php endif ?></span>
            <span><?= View::e($p['name']) ?><?php if ($p['featured']): ?> <span class="text-amber-500" title="Öne çıkan">★</span><?php endif ?><?php if ($variants): ?><span class="block text-xs font-normal text-muted-foreground"><?= count($variants) ?> seçenek</span><?php endif ?></span></a></td>
          <td class="whitespace-nowrap"><?= $min === $max ? Shop::money($min) : Shop::money($min) . ' – ' . Shop::money($max) ?></td>
          <td><?= $stock === null ? '<span class="text-muted-foreground">∞</span>' : ((int) $stock <= 0 ? '<span class="badge badge-gray">Tükendi</span>' : (int) $stock) ?></td>
          <td class="hidden text-muted-foreground md:table-cell"><?= View::e($categories[$p['category_id']] ?? '—') ?></td>
          <td><span class="badge <?= $p['status'] === 'published' ? 'badge-green' : 'badge-yellow' ?>"><?= $p['status'] === 'published' ? 'Satışta' : 'Taslak' ?></span></td>
        </tr>
      <?php endforeach ?>
      <?php if (!$rows['data']): ?><tr><td colspan="5" class="py-10 text-center text-muted-foreground">Ürün yok. <a href="<?= Router::url('admin.shop.products.create') ?>" class="underline">İlk ürünü ekleyin</a>.</td></tr><?php endif ?>
    </tbody>
  </table>
</div>
<div class="mt-4"><?= View::render('partials/pagination', ['page' => $rows]) ?></div>
