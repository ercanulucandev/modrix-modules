<?php // Görsel ızgarası + büyütme (lightbox). Değişkenler: $images (medya yolları), $title
use App\Services\Collection;
?>
<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" data-lightbox-group>
  <?php foreach ($images as $i => $path): $url = Collection::imageUrl($path); ?>
    <a href="<?= View::e($url) ?>" class="block aspect-square overflow-hidden rounded-lg bg-muted" data-lightbox aria-label="<?= View::e($title) ?> görsel <?= $i + 1 ?>">
      <img src="<?= View::e($url) ?>"<?= App\Services\ResponsiveImages::attrs($url, '(max-width: 640px) 50vw, 25vw') ?> alt="" loading="lazy" class="size-full object-cover transition duration-300 hover:scale-105">
    </a>
  <?php endforeach ?>
</div>
<?php if (empty($GLOBALS['modrix_lightbox'])): $GLOBALS['modrix_lightbox'] = true; ?>
<script>
// Basit büyütme: tıkla aç, ←/→ gez, Esc/tıkla kapat
(() => {
  let box, img, list = [], at = 0;
  const show = (i) => { at = (i + list.length) % list.length; img.src = list[at].href; };
  const close = () => { box.remove(); box = null; document.removeEventListener('keydown', key); };
  const key = (e) => { if (e.key === 'Escape') close(); if (e.key === 'ArrowRight') show(at + 1); if (e.key === 'ArrowLeft') show(at - 1); };
  document.addEventListener('click', (e) => {
    const a = e.target.closest('[data-lightbox]'); if (!a) return;
    e.preventDefault();
    list = [...a.closest('[data-lightbox-group]').querySelectorAll('[data-lightbox]')];
    box = document.createElement('div');
    box.style.cssText = 'position:fixed;inset:0;z-index:100;display:flex;align-items:center;justify-content:center;background:rgb(0 0 0/.9)';
    box.innerHTML = '<img style="max-width:92vw;max-height:88vh;object-fit:contain;border-radius:6px" alt="">'
      + '<button aria-label="Önceki" style="position:absolute;left:12px;color:#fff;font-size:40px;padding:12px">‹</button>'
      + '<button aria-label="Sonraki" style="position:absolute;right:12px;color:#fff;font-size:40px;padding:12px">›</button>'
      + '<button aria-label="Kapat" style="position:absolute;top:8px;right:16px;color:#fff;font-size:32px;padding:8px">×</button>';
    img = box.querySelector('img');
    const [prev, next, x] = box.querySelectorAll('button');
    prev.onclick = (ev) => { ev.stopPropagation(); show(at - 1); };
    next.onclick = (ev) => { ev.stopPropagation(); show(at + 1); };
    x.onclick = close;
    box.onclick = (ev) => { if (ev.target === box) close(); };
    if (list.length < 2) { prev.hidden = next.hidden = true; }
    document.body.append(box);
    document.addEventListener('keydown', key);
    show(list.indexOf(a));
  });
})();
</script>
<?php endif ?>
