<?php
// Modüller > Randevu > Ayarlar
return [
    'description' => 'Çalışma saatleri ve randevu kuralları. Hizmetler ve personel: Admin > Randevular.',
    'fields' => [
        'booking_title'        => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Randevu al'],
        'booking_intro'        => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:500', 'default' => 'Hizmeti ve size uygun saati seçin; randevunuz e-postanıza gelsin.'],
        'booking_hours_1' => ['label' => 'Pazartesi', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '09:00-18:00', 'placeholder' => 'Kapalı', 'help' => 'Ör. 09:00-18:00 ya da öğle arasıyla 09:00-12:00, 13:00-18:00. Boş: kapalı.'],
        'booking_hours_2' => ['label' => 'Salı', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '09:00-18:00', 'placeholder' => 'Kapalı'],
        'booking_hours_3' => ['label' => 'Çarşamba', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '09:00-18:00', 'placeholder' => 'Kapalı'],
        'booking_hours_4' => ['label' => 'Perşembe', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '09:00-18:00', 'placeholder' => 'Kapalı'],
        'booking_hours_5' => ['label' => 'Cuma', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '09:00-18:00', 'placeholder' => 'Kapalı'],
        'booking_hours_6' => ['label' => 'Cumartesi', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '10:00-16:00', 'placeholder' => 'Kapalı'],
        'booking_hours_7' => ['label' => 'Pazar', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:60|regex:/^(\d{2}:\d{2}-\d{2}:\d{2})(,\s*\d{2}:\d{2}-\d{2}:\d{2})*$/', 'default' => '', 'placeholder' => 'Kapalı'],
        'booking_interval'     => ['label' => 'Saat aralığı', 'type' => 'select', 'rules' => 'required|in:10,15,20,30,45,60', 'default' => '30',
                                   'options' => ['10' => '10 dakika', '15' => '15 dakika', '20' => '20 dakika', '30' => '30 dakika', '45' => '45 dakika', '60' => '1 saat'],
                                   'help' => 'Ziyaretçiye sunulan başlangıç saatleri bu aralıkla (09:00, 09:30...).'],
        'booking_notice'       => ['label' => 'En az kaç saat önceden', 'type' => 'number', 'rules' => 'required|integer|between:0,168', 'default' => '2'],
        'booking_days_ahead'   => ['label' => 'En fazla kaç gün sonrası', 'type' => 'number', 'rules' => 'required|integer|between:1,365', 'default' => '30'],
        'booking_cancel_hours' => ['label' => 'Müşteri iptali (saat önceye kadar)', 'type' => 'number', 'rules' => 'required|integer|between:0,168', 'default' => '24', 'help' => '0: müşteri iptal edemez.'],
        'booking_auto_confirm' => ['label' => 'Randevular kendiliğinden onaylansın', 'type' => 'checkbox', 'default' => '1', 'help' => 'Kapalıysa randevu "Onay bekliyor" olur, siz onaylayınca müşteriye e-posta gider.'],
        'booking_closed'       => ['label' => 'Kapalı günler (tatil)', 'type' => 'textarea', 'translatable' => false, 'rules' => 'nullable|max:3000', 'default' => '',
                                   'placeholder' => "2026-12-31\n2027-01-01", 'help' => 'Her satıra bir tarih (YYYY-AA-GG).'],
        'booking_notify_email' => ['label' => 'Yeni randevu bildirimi', 'type' => 'email', 'rules' => 'nullable|email|max:190', 'help' => 'Boşsa Ayarlar > İletişim\'deki e-posta.'],
    ],
];
