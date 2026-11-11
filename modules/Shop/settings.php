<?php
// Modüller > Mağaza > Ayarlar
return [
    'description' => 'Kargo, ödeme yöntemleri ve bildirimler. Ürünler: Admin > Ürünler, siparişler: Admin > Siparişler.',
    'fields' => [
        'shop_title'          => ['label' => 'Mağaza sayfası başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Mağaza'],
        'shop_per_page'       => ['label' => 'Sayfa başına ürün', 'type' => 'number', 'rules' => 'required|integer|between:4,96', 'default' => '12'],
        'shop_shipping_fee'   => ['label' => 'Kargo ücreti (₺)', 'type' => 'number', 'rules' => 'required|numeric|min:0', 'default' => '0', 'help' => '0: ücretsiz kargo.'],
        'shop_free_over'      => ['label' => 'Şu tutarın üstü kargo bedava (₺)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'default' => '', 'help' => 'Boş: uygulanmaz.'],
        'shop_terms_url'      => ['label' => 'Mesafeli satış sözleşmesi bağlantısı', 'type' => 'text', 'rules' => 'nullable|max:255', 'placeholder' => 'mesafeli-satis-sozlesmesi',
                                  'help' => 'Ödeme sayfasında onay kutusunda bağlantı olur (yasal zorunluluk). Önce Sayfalar\'da sözleşme sayfasını oluşturun.'],
        'shop_notify_email'   => ['label' => 'Yeni sipariş bildirimi', 'type' => 'email', 'rules' => 'nullable|email|max:190', 'help' => 'Boşsa Ayarlar > İletişim\'deki e-posta.'],

        'shop_bank_enabled'   => ['label' => 'Havale / EFT', 'type' => 'checkbox', 'default' => '1'],
        'shop_bank_accounts'  => ['label' => 'Banka hesapları', 'type' => 'textarea', 'rules' => 'nullable|max:2000',
                                  'default' => "Banka: ...\nAlıcı: ...\nIBAN: TR00 0000 0000 0000 0000 0000 00",
                                  'help' => 'Sipariş sonrası müşteriye gösterilir ve e-postayla gönderilir. Açıklamaya sipariş numarasını yazmasını isteyin.'],
        'shop_cod_enabled'    => ['label' => 'Kapıda ödeme', 'type' => 'checkbox', 'default' => '0'],
        'shop_cod_fee'        => ['label' => 'Kapıda ödeme ücreti (₺)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'default' => '0'],

        'shop_iyzico_enabled' => ['label' => 'iyzico ile kartla ödeme', 'type' => 'checkbox', 'default' => '0'],
        'shop_iyzico_sandbox' => ['label' => 'iyzico test modu', 'type' => 'checkbox', 'default' => '1', 'help' => 'Açıkken sandbox-api.iyzipay.com kullanılır (gerçek para çekilmez). Canlıya geçince kapatın.'],
        'shop_iyzico_key'    => ['label' => 'iyzico API anahtarı', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:100'],
        'shop_iyzico_secret'  => ['label' => 'iyzico gizli anahtar', 'type' => 'password', 'rules' => 'nullable|max:100', 'help' => 'Kayıtlıyı değiştirmeyecekseniz boş bırakın.'],

        'shop_paytr_enabled'  => ['label' => 'PayTR ile kartla ödeme', 'type' => 'checkbox', 'default' => '0'],
        'shop_paytr_sandbox'  => ['label' => 'PayTR test modu', 'type' => 'checkbox', 'default' => '1'],
        'shop_paytr_merchant'=> ['label' => 'PayTR mağaza no', 'type' => 'text', 'translatable' => false, 'rules' => 'nullable|max:20'],
        'shop_paytr_key'      => ['label' => 'PayTR merchant key', 'type' => 'password', 'rules' => 'nullable|max:100'],
        'shop_paytr_salt'     => ['label' => 'PayTR merchant salt', 'type' => 'password', 'rules' => 'nullable|max:100',
                                  'help' => 'PayTR panelinde Bildirim URL: https://SITENIZ/api/shop/paytr/notify'],
    ],
];
