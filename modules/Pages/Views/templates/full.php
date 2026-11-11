<?php
// Sayfa şablonu "Geniş (başlıksız)": başlık yazılmaz, içerik sayfa genişliğinde. Açılış ve kampanya sayfaları için.
// Değişkenler show.php ile aynı.
?>
<?php if ($isDraft): ?>
  <div class="alert alert-warning">Bu sayfa <strong>taslak</strong>; sadece siz görüyorsunuz.</div>
<?php endif ?>
<?php if ($page['image']): ?>
  <img src="<?= View::e(Request::url('media/' . $page['image'])) ?>" alt="" class="mb-10 max-h-[28rem] w-full rounded-lg border object-cover">
<?php endif ?>
<div class="prose-content max-w-none"><?= Hook::filter('content.html', Html::render($page['body']), $page) ?></div>
