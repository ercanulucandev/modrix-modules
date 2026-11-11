<?php
return [
    'name'        => 'Ürünler',
    'description' => 'Mağazadan öne çıkan ya da son eklenen ürünler.',
    'icon'        => 'cart',
    'fields'      => [
        'title'     => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Öne çıkan ürünler'],
        'text'      => ['label' => 'Açıklama', 'type' => 'text', 'default' => ''],
        'source'    => ['label' => 'Hangi ürünler', 'type' => 'select', 'default' => 'featured', 'options' => ['featured' => 'Öne çıkanlar', 'latest' => 'Son eklenenler']],
        'count'     => ['label' => 'Ürün sayısı', 'type' => 'select', 'default' => '4', 'options' => ['4' => '4', '8' => '8', '12' => '12']],
        'link_text' => ['label' => 'Tümü bağlantısı', 'type' => 'text', 'default' => 'Tüm ürünler', 'help' => 'Boşsa gösterilmez.'],
    ],
];
