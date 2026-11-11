<?php
namespace App\Modules\Pages\Models;

use Core\Model;

class Page extends Model
{
    public const STATUSES = ['published' => 'Yayında', 'draft' => 'Taslak'];

    protected static string $table = 'pages';
    protected static array $fillable = ['lang', 'translation_group', 'parent_id', 'title', 'slug', 'path', 'body', 'template', 'image', 'meta_description', 'status', 'sort_order', 'author_id'];

    // Geçerli dildeki (panelde: düzenlenen dildeki) sayfa
    public static function findByPath(string $path, ?string $lang = null): ?array
    {
        return static::query()->where('lang', $lang ?? \Core\Lang::content())->where('path', trim($path, '/'))->first();
    }

    // Tüm sayfalar ağaç sırasıyla (üst sayfanın hemen altında alt sayfaları), her birinde 'depth'
    public static function tree(?string $status = null): array
    {
        $rows = static::query()
            ->select('id', 'parent_id', 'title', 'slug', 'path', 'template', 'status', 'sort_order', 'created_at', 'updated_at')
            ->where('lang', \Core\Lang::content())
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('sort_order')->orderBy('title')
            ->get();

        $byParent = [];
        foreach ($rows as $row) $byParent[(int) $row['parent_id']][] = $row;

        $out = [];
        $walk = function (int $parent, int $depth) use (&$walk, &$out, $byParent) {
            foreach ($byParent[$parent] ?? [] as $row) {
                $row['depth'] = $depth;
                $out[] = $row;
                $walk((int) $row['id'], $depth + 1);
            }
        };
        $walk(0, 0);

        // Üst sayfası silinmiş/taslakta kalmış (filtre) sayfalar da kaybolmasın
        $seen = array_column($out, 'id');
        foreach ($rows as $row) if (!in_array($row['id'], $seen, true)) $out[] = $row + ['depth' => 0];
        return $out;
    }

    // Sayfanın tüm alt sayfalarının id'leri (her seviyeden)
    public static function descendantIds(int $id): array
    {
        $ids = [];
        $queue = [$id];
        while ($queue) {
            $children = static::query()->whereIn('parent_id', $queue)->pluck('id');
            $children = array_map('intval', $children);
            $ids = [...$ids, ...$children];
            $queue = $children;
        }
        return $ids;
    }

    // Kökten bu sayfaya kadar üst sayfalar (kırıntı menüsü için)
    public static function ancestors(array $page): array
    {
        $chain = [];
        for ($parent = $page['parent_id']; $parent && count($chain) < 10; $parent = $row['parent_id']) {
            $row = static::find((int) $parent);
            if (!$row) break;
            array_unshift($chain, $row);
        }
        return $chain;
    }

    public static function publishedChildren(int $id): array
    {
        return static::query()->where('parent_id', $id)->where('status', 'published')->orderBy('sort_order')->orderBy('title')->get();
    }

    // "Hakkımızda" -> "hakkimizda"
    public static function slugify(string $text): string
    {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($text, [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ]))), '-');
        return substr($slug, 0, 100) ?: 'sayfa';
    }
}
