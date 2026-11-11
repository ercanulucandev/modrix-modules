<?php
return [
    'description' => 'Canlı destek servisinde ücretsiz hesap açın, aşağıya kimliği yazın.',
    'fields' => [
        'livechat_provider' => ['label' => 'Servis', 'type' => 'select', 'rules' => 'required|in:none,tawk,crisp,tidio', 'default' => 'none',
                                'options' => ['none' => 'Kapalı', 'tawk' => 'Tawk.to (ücretsiz)', 'crisp' => 'Crisp', 'tidio' => 'Tidio']],
        'livechat_id'       => ['label' => 'Kimlik', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:120|regex:/^[A-Za-z0-9\/_-]*$/',
                                'help' => 'Tawk.to: Yönetim > Sohbet Widget\'ı > bağlantıdaki "tawk.to/" sonrası (ör. 64ab12.../1h2j3k). Crisp: Website ID. Tidio: Public key.'],
        'livechat_show_on'  => ['label' => 'Nerede', 'type' => 'select', 'rules' => 'required|in:all,desktop', 'default' => 'all',
                                'options' => ['all' => 'Her yerde', 'desktop' => 'Sadece masaüstünde']],
    ],
];
