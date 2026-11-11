/**
 * Sayfa düzenleyici (WordPress düzenleyicisi gibi).
 *
 *  - Başlık yazılırken adres kendiliğinden üretilir (adres elle değiştirilene kadar)
 *  - İçerik: HTML editör (@app/lib/editor.js; görsel ekleme medya kütüphanesinden)
 *  - "Yayınla" / "Güncelle" ve "Taslak olarak kaydet"; Ctrl+S mevcut durumla kaydeder
 *  - Önizle: kaydedilmemiş değişiklik varsa önce kaydeder, sonra sayfayı yeni sekmede açar (taslak admin'e görünür)
 *  - Öne çıkan görsel medya kütüphanesinden; arama motoru açıklaması için Google önizlemesi
 *  - Kaydedilmemiş değişiklik varken sayfadan çıkınca uyarır
 * Sunucu: app/Modules/Pages/Controllers/Admin/PageApiController.php
 */
import { apiClient } from '@app/lib/app.js';
import { h, formatDate } from '@app/lib/dom.js';
import { createEditor } from '@app/lib/editor.js';
import { openMediaPicker } from '@app/lib/media-picker.js';
import { confirmDialog } from '@app/lib/modal.js';
import { openRevisions } from '@app/lib/revisions.js';
import { toast } from '@app/lib/toast.js';

const root = document.querySelector('[data-page-editor]');
const ref = Object.fromEntries([...root.querySelectorAll('[data-ref]')].map((el) => [el.dataset.ref, el]));
const api = apiClient('admin/api/pages');
const cfg = root.dataset;

let page = null;              // kayıtlı hali (API'den)
let editor = null;
let image = null;             // { path, url }
let slugTouched = false;
let dirty = false;
let saving = false;
let parents = [];

// ================================================================ açılış

init();

async function init() {
  setBusy(true);
  try {
    const id = Number(cfg.id) || null;
    const [options, current] = await Promise.all([api.get('/options'), id ? api.get(`/${id}`) : null]);
    page = current?.data ?? null;
    parents = options.data.pages;
    fillOptions(options.data);

    editor = await createEditor(ref.editor, {
      value: page?.body ?? '',
      placeholder: 'Sayfa içeriğini yazın… Başlık, liste, bağlantı, görsel ve tablo ekleyebilirsiniz.',
      onChange: () => { setDirty(); updateSerp(); },
    });

    fill(page);
    bind();
    if (!page) ref.title.focus();
  } catch (err) {
    toast(err.message ?? 'Sayfa yüklenemedi', 'error');
  } finally {
    setBusy(false);
  }
}

function fillOptions({ templates }) {
  ref.template.replaceChildren(...Object.entries(templates).map(([value, label]) => h('option', { value }, label)));
  renderParents();
}

// Üst sayfa seçenekleri: sayfanın kendisi ve kendi alt sayfaları seçilemez
function renderParents() {
  const blocked = new Set();
  if (page) {
    blocked.add(page.id);
    let collecting = false, baseDepth = 0;
    for (const p of parents) {
      if (p.id === page.id) { collecting = true; baseDepth = p.depth; continue; }
      if (collecting && p.depth > baseDepth) blocked.add(p.id); else collecting = false;
    }
  }
  const current = ref.parent.value || (page?.parent_id ?? '');
  ref.parent.replaceChildren(
    h('option', { value: '' }, '(Yok, en üst seviye)'),
    ...parents.filter((p) => !blocked.has(p.id)).map((p) => h('option', { value: p.id }, `${'— '.repeat(p.depth)}${p.title}`)),
  );
  ref.parent.value = String(current ?? '');
}

function fill(p) {
  ref.title.value = p?.title ?? '';
  ref.slug.value = p?.slug ?? '';
  slugTouched = !!p;
  ref.parent.value = String(p?.parent_id ?? '');
  ref.template.value = p?.template ?? 'default';
  ref.order.value = p?.sort_order ?? 0;
  ref.meta.value = p?.meta_description ?? '';
  setImage(p?.image ? { path: p.image, url: p.image_url } : null, false);
  renderStatus();
  updatePrefix();
  updateSerp();
  setDirty(false);
}

// ================================================================ olaylar

function bind() {
  ref.title.addEventListener('input', () => {
    if (!slugTouched) ref.slug.value = slugify(ref.title.value);
    setDirty(); updateSerp();
  });
  ref.slug.addEventListener('input', () => { slugTouched = ref.slug.value !== ''; setDirty(); updateSerp(); });
  ref.slug.addEventListener('blur', () => { ref.slug.value = slugify(ref.slug.value); });
  ref.parent.addEventListener('change', () => { setDirty(); updatePrefix(); updateSerp(); });
  for (const el of [ref.template, ref.order]) el.addEventListener('input', () => setDirty());
  ref.meta.addEventListener('input', () => { setDirty(); updateSerp(); });

  ref.imagePick.addEventListener('click', async () => {
    const media = await openMediaPicker({ accept: 'image', title: 'Öne çıkan görsel' });
    if (media) setImage({ path: media.path, url: media.url });
  });
  ref.imageClear.addEventListener('click', () => setImage(null));

  ref.publish.addEventListener('click', () => save('published'));
  ref.saveDraft.addEventListener('click', () => save('draft'));
  ref.unpublish.addEventListener('click', () => save('draft'));
  ref.preview.addEventListener('click', preview);
  ref.history.addEventListener('click', () => openRevisions({ type: 'page', id: page.id, onRestore: (f) => {
    ref.title.value = f.title ?? '';
    ref.meta.value = f.meta_description ?? '';
    editor.setHtml(f.body ?? '');
    updateSerp();
    setDirty();
  } }));
  ref.delete.addEventListener('click', remove);

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(page?.status ?? 'draft'); }
  });
  window.addEventListener('beforeunload', (e) => { if (dirty) e.preventDefault(); });
}

// ================================================================ kaydet

function values(status) {
  return {
    title: ref.title.value.trim(),
    slug: slugify(ref.slug.value),
    body: editor?.getHtml() ?? '',
    status,
    parent_id: ref.parent.value || null,
    template: ref.template.value,
    sort_order: ref.order.value || 0,
    meta_description: ref.meta.value.trim(),
    image: image?.path ?? null,
  };
}

async function save(status) {
  if (saving) return null;
  clearErrors();
  if (!ref.title.value.trim()) {
    showErrors({ title: 'Başlık yazın.' });
    ref.title.focus();
    return null;
  }

  saving = true;
  setBusy(true);
  try {
    const result = page ? await api.put(`/${page.id}`, values(status)) : await api.post('/', values(status));
    const isNew = !page;
    page = result.data;
    fill(page);
    if (isNew) {
      history.replaceState(null, '', cfg.editUrl.replace('{id}', page.id));
      root.querySelector('h1').textContent = 'Sayfayı düzenle';
      parents = (await api.get('/options')).data.pages;
      renderParents();
    }
    toast(status === 'published' ? (isNew ? 'Sayfa yayınlandı.' : 'Sayfa güncellendi.') : 'Taslak kaydedildi.');
    return page;
  } catch (err) {
    if (err.isValidation) {
      showErrors(err.errors);
      toast('Bazı alanları düzeltin.', 'error');
    } else {
      toast(err.message, 'error');
    }
    return null;
  } finally {
    saving = false;
    setBusy(false);
  }
}

async function preview() {
  if (!page || dirty) {
    const saved = await save(page?.status ?? 'draft');
    if (!saved) return;
  }
  window.open(page.url, '_blank');
}

async function remove() {
  const ok = await confirmDialog({
    title: 'Sayfa silinsin mi?',
    message: `"${page.title}" kalıcı olarak silinecek. Alt sayfaları varsa bir üst seviyeye taşınır.`,
    confirmText: 'Sil',
    danger: true,
  });
  if (!ok) return;
  try {
    await api.delete(`/${page.id}`);
    dirty = false;
    window.location.assign(cfg.listUrl);
  } catch (err) {
    toast(err.message, 'error');
  }
}

// ================================================================ görünüm

function renderStatus() {
  const published = page?.status === 'published';
  ref.statusBadge.replaceChildren(page
    ? h('span', { class: `badge ${published ? 'badge-green' : 'badge-yellow'}` }, page.status_label)
    : h('span', { class: 'badge badge-gray' }, 'Kaydedilmedi'));
  ref.updated.textContent = page ? formatDate(page.updated_at) : '—';
  ref.publish.textContent = published ? 'Güncelle' : 'Yayınla';
  ref.saveDraft.classList.toggle('hidden', published);
  ref.unpublish.classList.toggle('hidden', !published);
  ref.delete.classList.toggle('hidden', !page);
  ref.history.classList.toggle('hidden', !page);
  ref.viewLink.classList.toggle('hidden', !page);
  if (page) {
    ref.viewLink.href = page.url;
    ref.viewLink.textContent = published ? 'Görüntüle' : 'Önizle';
  }
}

function parentPath() {
  const id = Number(ref.parent.value);
  if (!id) return '';
  // Seçenek listesi derinlik sırasında: üst zinciri geriye doğru bul
  const index = parents.findIndex((p) => p.id === id);
  if (index < 0) return '';
  const chain = [];
  let depth = parents[index].depth;
  chain.unshift(parents[index]);
  for (let i = index - 1; i >= 0 && depth > 0; i--) {
    if (parents[i].depth === depth - 1) { chain.unshift(parents[i]); depth--; }
  }
  return chain.map((p) => slugify(p.title)).join('/') + '/';
}

function updatePrefix() {
  // Kayıtlı sayfanın üst adresi sunucudakidir; seçim değişince başlıklardan tahmin edilir (kaydedince kesinleşir)
  const savedPrefix = page && String(page.parent_id ?? '') === ref.parent.value ? page.path.slice(0, -page.slug.length) : parentPath();
  ref.prefix.textContent = `${cfg.baseUrl.replace(/^https?:\/\//, '')}/${savedPrefix}`;
}

function updateSerp() {
  const text = ref.meta.value.trim() || plain(editor?.getHtml() ?? '').slice(0, 160);
  ref.metaCount.textContent = `${ref.meta.value.length}/160`;
  ref.metaCount.classList.toggle('text-destructive', ref.meta.value.length > 160);
  ref.serpUrl.textContent = `${ref.prefix.textContent}${slugify(ref.slug.value) || 'sayfa'}`;
  ref.serpTitle.textContent = `${ref.title.value.trim() || 'Sayfa başlığı'} · ${cfg.siteName}`;
  ref.serpText.textContent = text || 'Açıklama yazılmadı; arama motorları içeriğin başını gösterir.';
}

function setImage(value, markDirty = true) {
  image = value;
  ref.imagePick.replaceChildren(value
    ? h('img', { src: value.url, alt: '', class: 'size-full object-cover' })
    : 'Görsel seç');
  ref.imagePick.classList.toggle('border-dashed', !value);
  ref.imageClear.classList.toggle('hidden', !value);
  if (markDirty) setDirty();
}

function setDirty(value = true) {
  dirty = value;
  ref.saveState.textContent = value ? 'Kaydedilmemiş değişiklikler var' : (page ? `Kaydedildi · /${page.path}` : ' ');
}

function setBusy(on) {
  for (const b of [ref.publish, ref.saveDraft, ref.unpublish, ref.preview, ref.delete]) b.disabled = on;
}

function showErrors(errors) {
  for (const [name, message] of Object.entries(errors ?? {})) {
    const field = root.querySelector(`[data-field="${CSS.escape(name)}"]`);
    if (!field) { toast(message, 'error'); continue; }
    field.append(h('p', { class: 'field-error', 'data-error': '' }, message));
    field.querySelector('.input, input')?.classList.add('input-error');
  }
  root.querySelector('[data-error]')?.scrollIntoView({ block: 'center', behavior: 'smooth' });
}

function clearErrors() {
  root.querySelectorAll('[data-error]').forEach((e) => e.remove());
  root.querySelectorAll('.input-error').forEach((e) => e.classList.remove('input-error'));
}

// ================================================================ yardımcılar

// Sunucudaki Page::slugify ile aynı: "Hakkımızda" -> "hakkimizda"
function slugify(text) {
  const map = { ç: 'c', Ç: 'c', ğ: 'g', Ğ: 'g', ı: 'i', İ: 'i', ö: 'o', Ö: 'o', ş: 's', Ş: 's', ü: 'u', Ü: 'u' };
  return String(text).replace(/[çÇğĞıİöÖşŞüÜ]/g, (c) => map[c]).toLowerCase()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 100);
}

function plain(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  return (div.textContent ?? '').replace(/\s+/g, ' ').trim();
}
