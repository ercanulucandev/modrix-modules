/**
 * Yazı düzenleyici (WordPress düzenleyicisi gibi).
 *  - Başlık yazılırken adres kendiliğinden üretilir (adres elle değiştirilene kadar)
 *  - İçerik: HTML editör; özet, kategori (+ hızlı yeni kategori), etiketler (yaz + Enter), öne çıkan görsel
 *  - Yayın tarihi: boşsa hemen; ileri tarih seçilirse düğme "Zamanla" olur ve yazı o an yayına girer
 *  - "Yayınla / Güncelle / Zamanla" ve "Taslak olarak kaydet"; Ctrl+S mevcut durumla kaydeder
 *  - Önizle: kaydedilmemiş değişiklik varsa önce kaydeder, sonra yazıyı yeni sekmede açar (taslak ve zamanlanmışı sadece admin görür)
 * Sunucu: app/Modules/Blog/Controllers/Admin/PostApiController.php
 */
import { apiClient } from '@app/lib/app.js';
import { h, formatDate } from '@app/lib/dom.js';
import { createEditor } from '@app/lib/editor.js';
import { openMediaPicker } from '@app/lib/media-picker.js';
import { confirmDialog } from '@app/lib/modal.js';
import { openRevisions } from '@app/lib/revisions.js';
import { toast } from '@app/lib/toast.js';

const root = document.querySelector('[data-post-editor]');
const ref = Object.fromEntries([...root.querySelectorAll('[data-ref]')].map((el) => [el.dataset.ref, el]));
const api = apiClient('admin/api/blog');
const cfg = root.dataset;
const BADGES = { published: 'badge-green', scheduled: 'badge-default', draft: 'badge-yellow' };

let post = null;
let editor = null;
let image = null;
let tags = [];
let slugTouched = false;
let dirty = false;
let saving = false;

init();

async function init() {
  setBusy(true);
  try {
    const id = Number(cfg.id) || null;
    const [options, current] = await Promise.all([api.get('/options'), id ? api.get(`/posts/${id}`) : null]);
    post = current?.data ?? null;
    ref.category.append(...options.data.categories.map((c) => h('option', { value: c.id }, c.name)));
    ref.tagList.replaceChildren(...options.data.tags.map((t) => h('option', { value: t })));

    editor = await createEditor(ref.editor, {
      value: post?.body ?? '',
      placeholder: 'Yazınızı buraya yazın… Başlık, liste, bağlantı, görsel ve tablo ekleyebilirsiniz.',
      onChange: () => { setDirty(); updateSerp(); },
    });
    fill(post);
    bind();
    if (!post) ref.title.focus();
  } catch (err) {
    toast(err.message ?? 'Yazı yüklenemedi', 'error');
  } finally {
    setBusy(false);
  }
}

function fill(p) {
  ref.title.value = p?.title ?? '';
  ref.slug.value = p?.slug ?? '';
  slugTouched = !!p;
  ref.excerpt.value = p?.excerpt ?? '';
  ref.category.value = String(p?.category_id ?? '');
  ref.meta.value = p?.meta_description ?? '';
  ref.date.value = p?.published_at ? p.published_at.replace(' ', 'T').slice(0, 16) : '';
  tags = [...(p?.tags ?? [])];
  renderTags();
  setImage(p?.image ? { path: p.image, url: p.image_url } : null, false);
  ref.prefix.textContent = `${cfg.baseUrl.replace(/^https?:\/\//, '')}/blog/`;
  renderStatus();
  updateSerp();
  setDirty(false);
}

function bind() {
  ref.title.addEventListener('input', () => { if (!slugTouched) ref.slug.value = slugify(ref.title.value); setDirty(); updateSerp(); });
  ref.slug.addEventListener('input', () => { slugTouched = ref.slug.value !== ''; setDirty(); updateSerp(); });
  ref.slug.addEventListener('blur', () => { ref.slug.value = slugify(ref.slug.value); });
  for (const el of [ref.excerpt, ref.category]) el.addEventListener('input', () => { setDirty(); updateSerp(); });
  ref.meta.addEventListener('input', () => { setDirty(); updateSerp(); });
  ref.date.addEventListener('input', () => { setDirty(); renderStatus(); });

  // Etiketler: Enter / virgül ekler, Backspace (boşken) sonuncuyu siler
  ref.tagInput.addEventListener('keydown', (e) => {
    if ((e.key === 'Enter' || e.key === ',') && ref.tagInput.value.trim()) { e.preventDefault(); addTag(ref.tagInput.value); }
    else if (e.key === 'Backspace' && !ref.tagInput.value && tags.length) { tags.pop(); renderTags(); setDirty(); }
  });
  ref.tagInput.addEventListener('change', () => { if (ref.tagInput.value.trim()) addTag(ref.tagInput.value); });   // datalist seçimi
  ref.tagInput.addEventListener('blur', () => { if (ref.tagInput.value.trim()) addTag(ref.tagInput.value); });
  ref.tagBox.addEventListener('click', (e) => { if (e.target === ref.tagBox) ref.tagInput.focus(); });

  ref.newCategory.addEventListener('click', async () => {
    const name = prompt('Yeni kategorinin adı');
    if (!name?.trim()) return;
    try {
      const { data } = await api.post('/categories', { name: name.trim() });
      ref.category.append(h('option', { value: data.id }, data.name));
      ref.category.value = String(data.id);
      setDirty();
    } catch (err) { toast(err.message, 'error'); }
  });

  ref.imagePick.addEventListener('click', async () => {
    const media = await openMediaPicker({ accept: 'image', title: 'Öne çıkan görsel' });
    if (media) setImage({ path: media.path, url: media.url });
  });
  ref.imageClear.addEventListener('click', () => setImage(null));

  ref.publish.addEventListener('click', () => save('published'));
  ref.saveDraft.addEventListener('click', () => save('draft'));
  ref.unpublish.addEventListener('click', () => save('draft'));
  ref.preview.addEventListener('click', preview);
  ref.history.addEventListener('click', () => openRevisions({ type: 'post', id: post.id, onRestore: (f) => {
    ref.title.value = f.title ?? '';
    ref.excerpt.value = f.excerpt ?? '';
    ref.meta.value = f.meta_description ?? '';
    editor.setHtml(f.body ?? '');
    updateSerp();
    setDirty();
  } }));
  ref.delete.addEventListener('click', remove);
  document.addEventListener('keydown', (e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(post?.status ?? 'draft'); } });
  window.addEventListener('beforeunload', (e) => { if (dirty) e.preventDefault(); });
}

// ---------------------------------------------------------------- etiketler

function addTag(text) {
  for (const part of text.split(',')) {
    const tag = part.trim().replace(/\s+/g, ' ').slice(0, 40);
    if (tag && !tags.some((t) => t.toLocaleLowerCase('tr') === tag.toLocaleLowerCase('tr')) && tags.length < 20) tags.push(tag);
  }
  ref.tagInput.value = '';
  renderTags();
  setDirty();
}

function renderTags() {
  ref.tagBox.querySelectorAll('[data-tag]').forEach((el) => el.remove());
  for (const [i, tag] of tags.entries()) {
    const chip = h('span', { class: 'inline-flex items-center gap-1 rounded-full bg-muted py-0.5 pl-2.5 pr-1 text-xs', 'data-tag': '' }, [
      tag,
      h('button', { type: 'button', class: 'flex size-4 items-center justify-center rounded-full hover:bg-foreground/10', 'aria-label': `${tag} etiketini kaldır`,
        onClick: () => { tags.splice(i, 1); renderTags(); setDirty(); } }, '×'),
    ]);
    ref.tagBox.insertBefore(chip, ref.tagInput);
  }
}

// ---------------------------------------------------------------- kaydet

function values(status) {
  return {
    title: ref.title.value.trim(),
    slug: slugify(ref.slug.value),
    body: editor?.getHtml() ?? '',
    excerpt: ref.excerpt.value.trim(),
    status,
    published_at: ref.date.value ? ref.date.value.replace('T', ' ') + ':00' : null,
    category_id: ref.category.value || null,
    tags,
    image: image?.path ?? null,
    meta_description: ref.meta.value.trim(),
  };
}

async function save(status) {
  if (saving) return null;
  clearErrors();
  if (!ref.title.value.trim()) { showErrors({ title: 'Başlık yazın.' }); ref.title.focus(); return null; }
  if (ref.tagInput.value.trim()) addTag(ref.tagInput.value);

  saving = true;
  setBusy(true);
  try {
    const isNew = !post;
    const result = isNew ? await api.post('/posts', values(status)) : await api.put(`/posts/${post.id}`, values(status));
    post = result.data;
    fill(post);
    if (isNew) {
      history.replaceState(null, '', cfg.editUrl.replace('{id}', post.id));
      root.querySelector('h1').textContent = 'Yazıyı düzenle';
    }
    toast({ published: isNew ? 'Yazı yayınlandı.' : 'Yazı güncellendi.', scheduled: `Yazı ${formatDate(post.published_at)} tarihinde yayına girecek.`, draft: 'Taslak kaydedildi.' }[post.state]);
    return post;
  } catch (err) {
    if (err.isValidation) { showErrors(err.errors); toast('Bazı alanları düzeltin.', 'error'); }
    else toast(err.message, 'error');
    return null;
  } finally {
    saving = false;
    setBusy(false);
  }
}

async function preview() {
  if (!post || dirty) {
    const saved = await save(post?.status ?? 'draft');
    if (!saved) return;
  }
  window.open(post.url, '_blank');
}

async function remove() {
  const ok = await confirmDialog({ title: 'Yazı silinsin mi?', message: `"${post.title}" kalıcı olarak silinecek.`, confirmText: 'Sil', danger: true });
  if (!ok) return;
  try {
    await api.delete(`/posts/${post.id}`);
    dirty = false;
    location.assign(cfg.listUrl);
  } catch (err) {
    toast(err.message, 'error');
  }
}

// ---------------------------------------------------------------- görünüm

function renderStatus() {
  const future = ref.date.value && new Date(ref.date.value) > new Date();
  const state = post?.state;
  ref.statusBadge.replaceChildren(post ? h('span', { class: `badge ${BADGES[state]}` }, post.state_label) : h('span', { class: 'badge badge-gray' }, 'Kaydedilmedi'));
  ref.publish.textContent = future ? 'Zamanla' : (post?.status === 'published' ? 'Güncelle' : 'Yayınla');
  ref.saveDraft.classList.toggle('hidden', post?.status === 'published');
  ref.unpublish.classList.toggle('hidden', post?.status !== 'published');
  ref.delete.classList.toggle('hidden', !post);
  ref.history.classList.toggle('hidden', !post);
  ref.viewLink.classList.toggle('hidden', !post);
  if (post) {
    ref.viewLink.href = post.url;
    ref.viewLink.textContent = state === 'published' ? 'Görüntüle' : 'Önizle';
  }
}

function updateSerp() {
  const text = ref.meta.value.trim() || ref.excerpt.value.trim() || plain(editor?.getHtml() ?? '').slice(0, 160);
  ref.metaCount.textContent = `${ref.meta.value.length}/160`;
  ref.metaCount.classList.toggle('text-destructive', ref.meta.value.length > 160);
  ref.serpUrl.textContent = `${ref.prefix.textContent}${slugify(ref.slug.value) || 'yazi'}`;
  ref.serpTitle.textContent = `${ref.title.value.trim() || 'Yazı başlığı'} · ${cfg.siteName}`;
  ref.serpText.textContent = text || 'Açıklama yazılmadı; arama motorları içeriğin başını gösterir.';
}

function setImage(value, markDirty = true) {
  image = value;
  ref.imagePick.replaceChildren(value ? h('img', { src: value.url, alt: '', class: 'size-full object-cover' }) : 'Görsel seç');
  ref.imagePick.classList.toggle('border-dashed', !value);
  ref.imageClear.classList.toggle('hidden', !value);
  if (markDirty) setDirty();
}

function setDirty(value = true) {
  dirty = value;
  ref.saveState.textContent = value ? 'Kaydedilmemiş değişiklikler var' : (post ? `Kaydedildi · /blog/${post.slug}` : ' ');
}

function setBusy(on) {
  for (const b of [ref.publish, ref.saveDraft, ref.unpublish, ref.preview, ref.delete]) b.disabled = on;
}

function showErrors(errors) {
  for (const [name, message] of Object.entries(errors ?? {})) {
    const field = root.querySelector(`[data-field="${CSS.escape(name)}"]`);
    if (!field) { toast(message, 'error'); continue; }
    field.append(h('p', { class: 'field-error px-4 pb-3', 'data-error': '' }, message));
  }
  root.querySelector('[data-error]')?.scrollIntoView({ block: 'center', behavior: 'smooth' });
}

function clearErrors() {
  root.querySelectorAll('[data-error]').forEach((e) => e.remove());
}

// Sunucudaki Post::slugify ile aynı
function slugify(text) {
  const map = { ç: 'c', Ç: 'c', ğ: 'g', Ğ: 'g', ı: 'i', İ: 'i', ö: 'o', Ö: 'o', ş: 's', Ş: 's', ü: 'u', Ü: 'u' };
  return String(text).replace(/[çÇğĞıİöÖşŞüÜ]/g, (c) => map[c]).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 180);
}

function plain(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  return (div.textContent ?? '').replace(/\s+/g, ' ').trim();
}
