<?php
return [
    'name'        => 'Bülten',
    'description' => 'E-posta bültenine abonelik formu.',
    'icon'        => 'mail',
    'fields'      => [
        'title'  => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Gelişmelerden haberdar olun'],
        'text'   => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'Kampanya ve duyurularımız ayda bir e-postanızda. İstediğiniz zaman ayrılabilirsiniz.'],
        'button' => ['label' => 'Düğme yazısı', 'type' => 'text', 'default' => 'Abone ol'],
        'style'  => ['label' => 'Görünüm', 'type' => 'select', 'default' => 'light', 'options' => ['light' => 'Açık', 'dark' => 'Koyu bant']],
    ],
];
