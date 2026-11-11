<?php
namespace App\Modules\Faq\Controllers;

use App\Services\Collection;
use Core\Controller;
use Core\Seo;
use Core\Setting;
use Core\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $items = self::items();
        // Google: soru-cevap zengin sonucu
        if ($items) Seo::add(['@type' => 'FAQPage', 'mainEntity' => array_map(fn($i) => [
            '@type' => 'Question', 'name' => $i['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['answer']],
        ], $items)]);

        return $this->view('Faq::index', [
            'title'           => Setting::get('faq_title', 'Sıkça sorulan sorular'),
            'metaDescription' => Setting::get('faq_description'),
            'groups'          => self::grouped($items),
        ]);
    }

    // Görünür sorular; $group verilirse sadece o grup (adres biçiminde: "odeme" -> "Ödeme")
    public static function items(?string $group = null): array
    {
        return Collection::items('faq', $group ? fn($i) => self::slug((string) $i['group']) === strtolower($group) : null);
    }

    // ['Grup adı' => [sorular]]; grupsuzlar başta ('' anahtarı)
    public static function grouped(array $items): array
    {
        $groups = [];
        foreach ($items as $i) $groups[trim((string) $i['group'])][] = $i;
        uksort($groups, fn($a, $b) => ($a !== '') <=> ($b !== ''));
        return $groups;
    }

    private static function slug(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($text, ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u']))), '-');
    }
}
