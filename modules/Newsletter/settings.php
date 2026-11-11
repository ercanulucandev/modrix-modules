<?php
return [
    'description' => 'Abonelik formu ve onay e-postası. Aboneler: Admin > Bülten.',
    'fields' => [
        'newsletter_double_optin' => ['label' => 'Çift onay (önerilir)', 'type' => 'checkbox', 'default' => '1',
                                      'help' => 'Abone olan kişiye onay bağlantısı gönderilir; tıklayana kadar listeye girmez. Sahte kayıtları ve şikâyetleri önler.'],
        'newsletter_consent'      => ['label' => 'Onay kutusu metni', 'type' => 'text', 'rules' => 'nullable|max:300',
                                      'default' => 'Kampanya ve duyurularla ilgili e-posta almayı kabul ediyorum.',
                                      'help' => 'Formda işaretlenmesi zorunlu kutu (KVKK / ETK). Boşsa kutu gösterilmez.'],
        'newsletter_policy_url'   => ['label' => 'Aydınlatma metni bağlantısı', 'type' => 'text', 'rules' => 'nullable|max:255', 'placeholder' => 'kvkk',
                                      'help' => 'Onay kutusunun yanında "Aydınlatma metni" bağlantısı.'],
        'newsletter_success'      => ['label' => 'Başarı mesajı', 'type' => 'text', 'rules' => 'required|max:200', 'default' => 'Teşekkürler! Bültenimize abone oldunuz.'],
    ],
];
