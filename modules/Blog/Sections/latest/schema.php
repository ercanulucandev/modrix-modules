<?php
// Blog bölümü: son yazılar (her temada; tema kendi tasarımını yazmak isterse kendi bölümünü ekler)
return [
    'name'        => 'Son yazılar',
    'description' => 'Blog\'daki son yayınlanan yazılar, kapak görseliyle.',
    'icon'        => 'doc',
    'requires'    => 'Blog',
    'fields'      => [
        'title'     => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Son yazılar'],
        'text'      => ['label' => 'Açıklama', 'type' => 'text', 'default' => ''],
        'count'     => ['label' => 'Yazı sayısı', 'type' => 'select', 'default' => '3', 'options' => ['3' => '3', '6' => '6', '9' => '9']],
        'link_text' => ['label' => 'Tümü bağlantısı', 'type' => 'text', 'default' => 'Tümünü gör', 'help' => 'Boşsa gösterilmez.'],
    ],
];
