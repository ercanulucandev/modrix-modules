<?php
return [
    'name'        => 'Sıkça sorulanlar',
    'description' => 'Admin > SSS\'deki sorular, açılır kapanır.',
    'icon'        => 'help',
    'fields'      => [
        'title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Sıkça sorulanlar'],
        'text'  => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'Merak ettiklerinizin cevapları.'],
        'group' => ['label' => 'Sadece bu grup', 'type' => 'text', 'default' => '', 'help' => 'Boşsa tüm sorular. Ör. Ödeme'],
        'count' => ['label' => 'En fazla', 'type' => 'select', 'default' => '6', 'options' => ['4' => '4', '6' => '6', '8' => '8', '12' => '12', '50' => 'Hepsi']],
        'link_text' => ['label' => 'Tümü bağlantısı', 'type' => 'text', 'default' => 'Tüm sorular', 'help' => 'Boşsa gösterilmez.'],
    ],
];
