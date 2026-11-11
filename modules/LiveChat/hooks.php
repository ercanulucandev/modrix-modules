<?php
// Canlı destek: seçilen servisin kodu sayfa sonuna. Çerez onayı modülü açıksa "işlevsel" onayı beklenir.
Hook::on('site.body_end', function (array $html) {
    $provider = Setting::get('livechat_provider');
    $id = (string) Setting::get('livechat_id');
    if (!in_array($provider, ['tawk', 'crisp', 'tidio'], true) || !preg_match('#^[A-Za-z0-9/_-]{3,120}$#', $id) || Theme::previewing()) return $html;

    $gate = Hook::filter('consent.enabled', false);
    $type = $gate ? 'type="text/plain" data-consent="functional"' : '';
    $desktop = Setting::get('livechat_show_on') === 'desktop' ? 'if (matchMedia("(max-width: 767px)").matches) return;' : '';
    $js = json_encode($id);
    $code = match ($provider) {
        'tawk'  => "(function(){ $desktop var s=document.createElement('script'); s.async=true; s.src='https://embed.tawk.to/' + $js; s.charset='UTF-8'; s.setAttribute('crossorigin','*'); document.body.appendChild(s); })();",
        'crisp' => "(function(){ $desktop window.\$crisp=[]; window.CRISP_WEBSITE_ID=$js; var s=document.createElement('script'); s.src='https://client.crisp.chat/l.js'; s.async=true; document.head.appendChild(s); })();",
        'tidio' => "(function(){ $desktop var s=document.createElement('script'); s.src='https://code.tidio.co/' + $js + '.js'; s.async=true; document.body.appendChild(s); })();",
    };
    return [...$html, "<script $type>$code</script>"];
});
