/**
 * Form oluşturucu (Admin > Formlar > Düzenle).
 *  - Alanlar kart olarak listelenir; tutamaktan sürükleyerek sıralanır (SortableJS)
 *  - Karta tıklayınca ayarları açılır: etiket, tür, zorunlu, genişlik, yer tutucu, seçenekler, açıklama
 *  - Alttaki düğmelerle istenen türde alan eklenir
 *  - Kaydet: PUT /admin/api/forms/{id}  (Ctrl+S). Kaydedilmemiş değişiklikte sayfadan çıkarken uyarır.
 */
import { createHttp } from '@app/lib/http.js';
import { config } from '@app/lib/app.js';
import { h, icon } from '@app/lib/dom.js';
import { toast } from '@app/lib/toast.js';

const root = document.querySelector('[data-form-builder]');
const ref = Object.fromEntries([...root.querySelectorAll('[data-ref]')].map((el) => [el.dataset.ref, el]));
const { form, types } = JSON.parse(document.getElementById('form-data').textContent);
const http = createHttp({ baseUrl: root.dataset.api, csrf: config.csrf, onUnauthorized: () => location.assign(config.loginUrl) });

let fields = form.fields.map((f) => ({ ...f, _key: Math.random().toString(36).slice(2) }));
const open = new Set();
let dirty = false;

// ---------------------------------------------------------------- doldur

ref.name.value = form.name;
ref.slug.value = form.slug;
for (const el of root.querySelectorAll('[data-setting]')) {
  const value = form.settings[el.dataset.setting];
  if (el.type === 'checkbox') el.checked = !!value; else el.value = value ?? '';
  el.addEventListener('input', () => setDirty());
}
ref.name.addEventListener('input', () => setDirty());
ref.slug.addEventListener('input', () => { updateCode(); setDirty(); });
updateCode();
render();
setDirty(false);

import('https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/+esm').then(({ default: Sortable }) => {
  Sortable.create(ref.fields, {
    handle: '.drag', animation: 150, ghostClass: 'opacity-40',
    onEnd: (e) => {
      if (e.oldIndex === e.newIndex) return;
      fields.splice(e.newIndex, 0, fields.splice(e.oldIndex, 1)[0]);
      render();
      setDirty();
    },
  });
}).catch(() => {});

ref.add.addEventListener('click', (e) => {
  const button = e.target.closest('[data-type]');
  if (!button) return;
  const type = button.dataset.type;
  const f = {
    _key: Math.random().toString(36).slice(2), name: '', label: types[type], type, required: false,
    placeholder: '', options: ['select', 'radio'].includes(type) ? ['Seçenek 1', 'Seçenek 2'] : [], width: 'full', help: '',
  };
  fields.push(f);
  open.add(f._key);
  render();
  setDirty();
  ref.fields.lastElementChild?.querySelector('input')?.select();
});

ref.save.addEventListener('click', save);
document.addEventListener('keydown', (e) => { if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(); } });
window.addEventListener('beforeunload', (e) => { if (dirty) e.preventDefault(); });

// ---------------------------------------------------------------- alan kartları

function render() {
  ref.fields.replaceChildren(...fields.map((f, i) => card(f, i)));
  if (!fields.length) ref.fields.append(h('p', { class: 'rounded-lg border border-dashed px-4 py-8 text-center text-sm text-muted-foreground' }, 'Alan yok. Aşağıdan ekleyin.'));
}

function card(f, i) {
  const isOpen = open.has(f._key);
  const title = h('span', { class: 'min-w-0 flex-1 truncate text-sm font-medium' }, f.label || '(etiketsiz)');
  const input = (key, attrs = {}) => {
    const el = h(attrs.textarea ? 'textarea' : 'input', { class: 'input', ...attrs, textarea: null });
    el.value = f[key] ?? '';
    el.addEventListener('input', () => { f[key] = el.value; if (key === 'label') title.textContent = el.value || '(etiketsiz)'; setDirty(); });
    return el;
  };
  const typeSelect = h('select', { class: 'input' }, Object.entries(types).map(([v, l]) => h('option', { value: v, selected: v === f.type }, l)));
  typeSelect.addEventListener('change', () => {
    f.type = typeSelect.value;
    if (['select', 'radio'].includes(f.type) && !f.options.length) f.options = ['Seçenek 1', 'Seçenek 2'];
    render();
    setDirty();
  });
  const required = h('input', { type: 'checkbox', class: 'size-4 accent-primary', checked: f.required });
  required.addEventListener('change', () => { f.required = required.checked; render(); setDirty(); });
  const width = h('select', { class: 'input' }, [h('option', { value: 'full', selected: f.width === 'full' }, 'Tam satır'), h('option', { value: 'half', selected: f.width === 'half' }, 'Yarım (yan yana)')]);
  width.addEventListener('change', () => { f.width = width.value; setDirty(); });
  const options = h('textarea', { class: 'input font-mono text-xs', rows: 4 });
  options.value = (f.options ?? []).join('\n');
  options.addEventListener('input', () => { f.options = options.value.split('\n').map((o) => o.trim()).filter(Boolean); setDirty(); });

  const label = (text, control, cls = '') => h('label', { class: `block ${cls}` }, [h('span', { class: 'label' }, text), control]);
  const body = h('div', { class: `grid gap-4 border-t p-4 sm:grid-cols-2 ${isOpen ? '' : 'hidden'}` }, [
    label(f.type === 'checkbox' ? 'Onay metni' : 'Etiket', input('label', { maxlength: 300 }), 'sm:col-span-2'),
    label('Tür', typeSelect),
    label('Genişlik', width),
    f.type !== 'checkbox' ? label('Yer tutucu', input('placeholder', { maxlength: 120 })) : null,
    label('Alan adı (teknik)', input('name', { maxlength: 50, class: 'input font-mono text-xs', placeholder: 'etiketten-uretilir' })),
    ['select', 'radio'].includes(f.type) ? label('Seçenekler (her satıra bir tane)', options, 'sm:col-span-2') : null,
    label('Açıklama (alanın altında)', input('help', { maxlength: 200 }), 'sm:col-span-2'),
    h('label', { class: 'flex cursor-pointer items-center gap-2 text-sm sm:col-span-2' }, [required, 'Zorunlu alan']),
  ]);

  const action = (text, name, fn, cls = '') => {
    const b = h('button', { type: 'button', class: `btn btn-ghost size-8 p-0 ${cls}`, title: text, 'aria-label': text }, icon(name, 'size-4'));
    b.addEventListener('click', (e) => { e.stopPropagation(); fn(); });
    return b;
  };
  const head = h('div', { class: 'flex cursor-pointer items-center gap-2 px-2 py-2' }, [
    h('span', { class: 'drag flex size-8 shrink-0 cursor-grab items-center justify-center rounded text-muted-foreground hover:bg-accent', title: 'Sürükle' }, icon('grip', 'size-4')),
    title,
    f.required ? h('span', { class: 'badge badge-gray' }, 'Zorunlu') : null,
    h('span', { class: 'hidden text-xs text-muted-foreground sm:inline' }, types[f.type]),
    action('Çoğalt', 'copy', () => { const c = { ...structuredClone(f), _key: Math.random().toString(36).slice(2), name: '' }; fields.splice(i + 1, 0, c); render(); setDirty(); }),
    action('Sil', 'trash', () => { fields.splice(i, 1); render(); setDirty(); }, 'text-destructive'),
    icon('chevron_down', `size-4 text-muted-foreground transition-transform ${isOpen ? 'rotate-180' : ''}`),
  ]);
  head.addEventListener('click', (e) => {
    if (e.target.closest('.drag')) return;
    open.has(f._key) ? open.delete(f._key) : open.add(f._key);
    render();
  });
  return h('div', { class: 'rounded-lg border bg-background' }, [head, body]);
}

// ---------------------------------------------------------------- kaydet

async function save() {
  root.querySelectorAll('[data-error]').forEach((e) => e.remove());
  ref.save.disabled = true;
  try {
    const settings = Object.fromEntries([...root.querySelectorAll('[data-setting]')].map((el) => [el.dataset.setting, el.type === 'checkbox' ? el.checked : el.value]));
    const { data, message } = await http.put('', {
      name: ref.name.value.trim(),
      slug: ref.slug.value.trim(),
      fields: fields.map(({ _key, ...f }) => f),
      settings,
    });
    fields = data.fields.map((f) => ({ ...f, _key: Math.random().toString(36).slice(2) }));
    open.clear();
    ref.slug.value = data.slug;
    updateCode();
    render();
    setDirty(false);
    toast(message);
  } catch (err) {
    if (err.isValidation) {
      for (const [name, msg] of Object.entries(err.errors)) {
        const box = root.querySelector(`[data-field="${CSS.escape(name)}"]`);
        box ? box.append(h('p', { class: 'field-error', 'data-error': '' }, msg)) : toast(msg, 'error');
      }
      toast('Bazı alanları düzeltin.', 'error');
    } else {
      toast(err.message, 'error');
    }
  } finally {
    ref.save.disabled = false;
  }
}

function updateCode() {
  ref.code.textContent = `[form ${ref.slug.value.trim() || '…'}]`;
}

function setDirty(value = true) {
  dirty = value;
  ref.state.textContent = value ? 'Kaydedilmemiş değişiklikler var' : 'Kaydedildi';
}
