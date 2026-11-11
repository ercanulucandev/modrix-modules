/**
 * Admin > Ürün formu: açıklama için HTML editör (kaydederken gizli alana yazılır) ve seçenek (varyant) satırları.
 */
import { createEditor } from '@app/lib/editor.js';

const form = document.querySelector('[data-product-form]');
const hidden = form.querySelector('[data-description]');
const editor = await createEditor(form.querySelector('[data-editor]'), { value: hidden.value, placeholder: 'Ürünü anlatın: özellikler, ölçüler, malzeme…' });
form.addEventListener('submit', () => { hidden.value = editor.getHtml(); });

const list = form.querySelector('[data-variants]');
const template = form.querySelector('[data-variant-template]');
let index = list.children.length + 1000;
form.querySelector('[data-add-variant]').addEventListener('click', () => {
  const row = template.content.firstElementChild.cloneNode(true);
  row.querySelectorAll('[name]').forEach((el) => { el.name = el.name.replace('__i__', index); });
  index++;
  list.append(row);
  row.querySelector('input').focus();
});
list.addEventListener('click', (e) => { if (e.target.closest('[data-remove-variant]')) e.target.closest('[data-variant]').remove(); });
