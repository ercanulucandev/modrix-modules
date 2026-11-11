<?php
namespace App\Modules\Shop\Services;

use Core\Database;
use Core\Request;
use Core\Setting;

// Mağaza yardımcıları: fiyat biçimi, ürün sorguları, ödeme yöntemleri, sipariş durumları
final class Shop
{
    public const STATUSES = [
        'pending'    => 'Ödeme bekleniyor',
        'processing' => 'Hazırlanıyor',
        'shipped'    => 'Kargoda',
        'completed'  => 'Tamamlandı',
        'cancelled'  => 'İptal edildi',
        'refunded'   => 'İade edildi',
    ];
    public const PAYMENT_STATUSES = ['unpaid' => 'Ödenmedi', 'paid' => 'Ödendi', 'failed' => 'Başarısız', 'refunded' => 'İade edildi'];

    // 1234.5 -> "1.234,50 ₺"
    public static function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', '.') . ' ₺';
    }

    // Kuruş cinsinden tam sayı (toplamlarda kayan nokta hatası olmasın)
    public static function cents(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    // Etkin ödeme yöntemleri: ['bank' => 'Havale / EFT', ...]
    public static function paymentMethods(): array
    {
        return array_filter([
            'iyzico' => Setting::bool('shop_iyzico_enabled') && Setting::get('shop_iyzico_key') && Setting::get('shop_iyzico_secret') ? 'Kredi / banka kartı (iyzico)' : null,
            'paytr'  => Setting::bool('shop_paytr_enabled') && Setting::get('shop_paytr_merchant') && Setting::get('shop_paytr_key') ? 'Kredi / banka kartı (PayTR)' : null,
            'bank'   => Setting::bool('shop_bank_enabled') ? 'Havale / EFT' : null,
            'cod'    => Setting::bool('shop_cod_enabled') ? 'Kapıda ödeme' : null,
        ]);
    }

    public static function methodLabel(string $method): string
    {
        return ['iyzico' => 'Kart (iyzico)', 'paytr' => 'Kart (PayTR)', 'bank' => 'Havale / EFT', 'cod' => 'Kapıda ödeme'][$method] ?? $method;
    }

    public static function products()
    {
        return Database::table('shop_products')->where('status', 'published');
    }

    public static function product(int $id): ?array
    {
        $p = Database::table('shop_products')->where('id', $id)->first();
        return $p ? self::decode($p) : null;
    }

    public static function productBySlug(string $slug): ?array
    {
        $p = self::products()->where('slug', $slug)->first();
        return $p ? self::decode($p) : null;
    }

    public static function variants(int $productId): array
    {
        return Database::table('shop_variants')->where('product_id', $productId)->orderBy('sort_order')->orderBy('id')->get();
    }

    public static function decode(array $p): array
    {
        $p['images'] = json_decode((string) ($p['images'] ?? ''), true) ?: [];
        return $p;
    }

    public static function image(array $p, int $i = 0): ?string
    {
        $images = is_array($p['images']) ? $p['images'] : (json_decode((string) $p['images'], true) ?: []);
        return isset($images[$i]) ? Request::url('media/' . $images[$i]) : null;
    }

    // Ürünün (varsa en ucuz varyantın) fiyat aralığı: [min, max]
    public static function priceRange(array $p, ?array $variants = null): array
    {
        $variants ??= self::variants((int) $p['id']);
        if (!$variants) return [(float) $p['price'], (float) $p['price']];
        $prices = array_map(fn($v) => (float) ($v['price'] ?? $p['price']), $variants);
        return [min($prices), max($prices)];
    }

    // Satılabilir mi (stok takibi yoksa her zaman)
    public static function available(array $p, ?array $variants = null): bool
    {
        $variants ??= self::variants((int) $p['id']);
        if ($variants) {
            foreach ($variants as $v) if ($v['stock'] === null || (int) $v['stock'] > 0) return true;
            return false;
        }
        return $p['stock'] === null || (int) $p['stock'] > 0;
    }

    public static function slugify(string $text): string
    {
        return substr(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($text, [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ]))), '-'), 0, 180);
    }

    public static function absolute(string $url): string
    {
        if (str_starts_with($url, 'http')) return $url;
        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $url;
    }
}
