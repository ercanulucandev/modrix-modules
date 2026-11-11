<?php // Açılır kapanır sorular (sayfa, bölüm ve [faq] ortak). Değişken: $items ?>
<div class="divide-y rounded-lg border bg-card">
  <?php foreach ($items as $i): ?>
    <details id="soru-<?= $i['id'] ?>" class="group px-5 [&_summary::-webkit-details-marker]:hidden">
      <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 font-medium">
        <?= View::e($i['question']) ?>
        <svg class="size-4 shrink-0 text-muted-foreground transition group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
      </summary>
      <div class="pb-5 leading-7 text-muted-foreground"><?= nl2br(View::e($i['answer'])) ?></div>
    </details>
  <?php endforeach ?>
</div>
