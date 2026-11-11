<?php
// Admin > Modüller > WhatsApp > Ayarlar. Alan biçimi app/Config/settings.php ile aynı; anahtarlar 'whatsapp_' ile başlar.
// Okuma: Setting::get('whatsapp_phone'), Setting::bool('whatsapp_enabled')
return [
    'description' => "Sitenin köşesinde ziyaretçilerin tek tıkla size WhatsApp'tan yazabileceği düğme.",
    'fields'      => [
        'whatsapp_enabled'  => ['label' => 'Düğmeyi göster', 'type' => 'checkbox', 'default' => '0'],
        'whatsapp_phone'    => ['label' => 'Telefon numarası', 'type' => 'tel', 'rules' => 'nullable|phone',
                                'placeholder' => '0532 123 45 67',
                                'help' => 'WhatsApp kullanan numara. 0532..., +90 532... ya da başka ülke koduyla yazabilirsiniz.'],
        'whatsapp_message'  => ['label' => 'Hazır mesaj', 'type' => 'textarea', 'rules' => 'nullable|max:500',
                                'default' => 'Merhaba, sitenizden yazıyorum: {url}',
                                'help' => 'Sohbet açılınca yazı alanında hazır gelir. {url} bulunulan sayfanın adresiyle değişir.'],
        'whatsapp_label'    => ['label' => 'Düğme yazısı', 'type' => 'text', 'rules' => 'nullable|max:40',
                                'default' => 'Bize yazın', 'help' => 'Masaüstünde simgenin yanında görünür.'],
        'whatsapp_position' => ['label' => 'Konum', 'type' => 'select', 'rules' => 'required|in:right,left', 'default' => 'right',
                                'options' => ['right' => 'Sağ alt köşe', 'left' => 'Sol alt köşe']],
        'whatsapp_show_on'  => ['label' => 'Nerede görünsün', 'type' => 'select', 'rules' => 'required|in:all,mobile,desktop', 'default' => 'all',
                                'options' => ['all' => 'Her yerde', 'mobile' => 'Sadece mobilde', 'desktop' => 'Sadece masaüstünde']],
    ],
];
