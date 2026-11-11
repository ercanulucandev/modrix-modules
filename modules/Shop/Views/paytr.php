<?php // PayTR ödeme çerçevesi. Değişkenler: $order, $frame ?>
<div class="mx-auto max-w-3xl">
  <h1 class="text-2xl font-semibold tracking-tight">Kartla ödeme · #<?= View::e($order['number']) ?></h1>
  <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
  <iframe src="https://www.paytr.com/odeme/guvenli/<?= View::e($frame) ?>" id="paytriframe" frameborder="0" scrolling="no" style="width:100%;min-height:600px" class="mt-6"></iframe>
  <script>iFrameResize({}, '#paytriframe');</script>
</div>
