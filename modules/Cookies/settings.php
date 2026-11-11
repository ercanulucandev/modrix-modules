<?php
return [
    'description' => 'Ziyaretçiye çerez bildirimi gösterilir; izin verilmeyen kategorideki kodlar çalıştırılmaz.',
    'fields' => [
        'cookies_text'       => ['label' => 'Bildirim metni', 'type' => 'textarea', 'rules' => 'required|max:600',
                                 'default' => 'Sitemizde deneyiminizi iyileştirmek ve trafiği ölçmek için çerezler kullanıyoruz. Zorunlu çerezler dışındakiler için onayınızı istiyoruz.'],
        'cookies_policy_url' => ['label' => 'Çerez politikası bağlantısı', 'type' => 'text', 'rules' => 'nullable|max:255', 'placeholder' => 'cerez-politikasi'],
        'cookies_accept'     => ['label' => 'Kabul düğmesi', 'type' => 'text', 'rules' => 'required|max:30', 'default' => 'Tümünü kabul et'],
        'cookies_reject'     => ['label' => 'Ret düğmesi', 'type' => 'text', 'rules' => 'required|max:30', 'default' => 'Sadece zorunlu'],
        'cookies_position'   => ['label' => 'Konum', 'type' => 'select', 'rules' => 'required|in:bottom,left', 'default' => 'bottom',
                                 'options' => ['bottom' => 'Altta tam genişlik', 'left' => 'Sol alt köşede kutu']],
        'cookies_analytics'  => ['label' => 'Analitik kategorisi', 'type' => 'checkbox', 'default' => '1', 'help' => 'Google Analytics vb. (Ayarlar > SEO\'daki ölçüm kimliği onaylanınca çalışır).'],
        'cookies_marketing'  => ['label' => 'Pazarlama kategorisi', 'type' => 'checkbox', 'default' => '0', 'help' => 'Reklam pikselleri. Özel koddaki <script type="text/plain" data-consent="marketing"> etiketleri onaylanınca çalışır.'],
        'cookies_functional' => ['label' => 'İşlevsel kategorisi', 'type' => 'checkbox', 'default' => '1', 'help' => 'Canlı destek vb.'],
    ],
];
