<?php
namespace App\Modules\Pages\Services;

use App\Models\Media;
use App\Modules\Pages\Models\Page;
use Core\Database;
use Core\Hook;
use Core\Html;
use Core\HttpException;
use Core\Router;
use Core\Setting;
use Core\Theme;
use Core\Validator;

// Sayfalarla ilgili iş mantığı: doğrulama, adres (path) üretimi, alt sayfaların adreslerinin güncellenmesi.
final class PageService
{
    public const BASE_TEMPLATES = ['default' => 'Varsayılan', 'full' => 'Geniş (başlıksız)'];

    // Kullanılabilir şablonlar: modülün iki şablonu + etkin temanın theme.json'daki "page_templates"
    public static function templates(): array
    {
        return self::BASE_TEMPLATES + (Theme::manifest(Theme::saved())['templates'] ?? []);
    }

    public static function rules(): array
    {
        return [
            'title'            => 'required|max:200',
            'slug'             => 'nullable|max:100|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'body'             => 'nullable|max:500000',
            'status'           => 'required|in:' . implode(',', array_keys(Page::STATUSES)),
            'parent_id'        => 'nullable|integer',
            'template'         => 'required|max:40',
            'sort_order'       => 'nullable|integer|between:0,9999',
            'meta_description' => 'nullable|max:300',
            'image'            => 'nullable|max:255',
        ];
    }

    public static function labels(): array
    {
        return [
            'title' => 'Başlık', 'slug' => 'Adres', 'body' => 'İçerik', 'status' => 'Durum', 'parent_id' => 'Üst sayfa',
            'template' => 'Şablon', 'sort_order' => 'Sıra', 'meta_description' => 'Arama motoru açıklaması', 'image' => 'Öne çıkan görsel',
        ];
    }

    public static function find(int $id): array
    {
        return Page::find($id) ?? throw new HttpException(404, 'Sayfa bulunamadı');
    }

    public static function create(array $data, ?int $authorId = null): array
    {
        $data = self::prepare($data, null);
        $data['author_id'] = $authorId;
        $page = Page::find(Page::create($data));
        Hook::fire('page.created', $page);
        return $page;
    }

    public static function update(int $id, array $data): array
    {
        $page = self::find($id);
        $data = self::prepare($data, $page);

        Database::transaction(function () use ($page, $data) {
            Page::update($page['id'], $data);
            if ($data['path'] !== $page['path']) self::rebuildChildPaths($page['id'], $data['path']);
        });
        // Eski adres yeni adrese 301 ile yönlensin (SEO; alt sayfalar rebuildChildPaths içinde)
        if ($data['path'] !== $page['path'] && $page['status'] === 'published') Hook::fire('url.moved', $page['path'], $data['path']);

        $page = Page::find($page['id']);
        Hook::fire('page.updated', $page);
        return $page;
    }

    // Silinen sayfanın alt sayfaları onun üst sayfasına bağlanır (kaybolmaz), adresleri güncellenir
    public static function delete(int $id): void
    {
        $page = self::find($id);
        $parent = $page['parent_id'] ? Page::find((int) $page['parent_id']) : null;

        Database::transaction(function () use ($page, $parent) {
            foreach (Page::query()->where('parent_id', $page['id'])->get() as $child) {
                $path = self::uniquePath($parent ? $parent['path'] . '/' . $child['slug'] : $child['slug'], (int) $child['id'], $child['lang']);
                Page::update($child['id'], ['parent_id' => $parent['id'] ?? null, 'path' => $path, 'slug' => basename($path)]);
                if ($child['status'] === 'published' && $path !== $child['path']) Hook::fire('url.moved', $child['path'], $path);
                self::rebuildChildPaths((int) $child['id'], $path);
            }
            Page::delete($page['id']);
        });

        if ((int) Setting::get('pages_front_page') === (int) $page['id']) Setting::set(['pages_front_page' => null]);
        Hook::fire('page.deleted', $page);
    }

    // Başka dile çeviri: sayfanın taslak kopyası o dilde (üst sayfanın o dildeki karşılığının altında). Varsa mevcut çeviri.
    public static function translate(int $id, string $lang, ?int $authorId = null): array
    {
        $page = self::find($id);
        $siblings = \App\Services\Translations::siblings('pages', $page);
        if (isset($siblings[$lang])) return Page::find((int) $siblings[$lang]['id']);
        $group = \App\Services\Translations::ensureGroup('pages', $page);

        $parent = null;
        if ($page['parent_id'] && ($p = Page::find((int) $page['parent_id']))) $parent = \App\Services\Translations::siblings('pages', $p)[$lang] ?? null;
        $path = self::uniquePath(($parent ? $parent['path'] . '/' : '') . $page['slug'], null, $lang);
        $newId = Page::create([
            'lang' => $lang, 'translation_group' => $group, 'parent_id' => $parent['id'] ?? null, 'title' => $page['title'], 'slug' => basename($path), 'path' => $path,
            'body' => $page['body'], 'template' => $page['template'], 'image' => $page['image'], 'meta_description' => $page['meta_description'],
            'status' => 'draft', 'sort_order' => $page['sort_order'], 'author_id' => $authorId,
        ]);
        $copy = Page::find($newId);
        Hook::fire('page.created', $copy);
        return $copy;
    }

    // Kopya: taslak olarak, başlığa "(kopya)" eklenir
    public static function duplicate(int $id, ?int $authorId = null): array
    {
        $page = self::find($id);
        $copy = array_intersect_key($page, array_flip(['parent_id', 'body', 'template', 'image', 'meta_description', 'sort_order']));
        $copy['title'] = mb_substr($page['title'] . ' (kopya)', 0, 200);
        $copy['slug'] = $page['slug'] . '-kopya';
        $copy['status'] = 'draft';
        return self::create($copy, $authorId);
    }

    // ---------------------------------------------------------------- iç

    private static function prepare(array $data, ?array $page): array
    {
        $errors = [];

        // Üst sayfa: var olmalı, kendisi ya da kendi alt sayfası olamaz
        $parentId = (int) ($data['parent_id'] ?? 0) ?: null;
        $parent = null;
        if ($parentId) {
            $parent = Page::find($parentId);
            if (!$parent) $errors['parent_id'] = 'Seçilen üst sayfa bulunamadı.';
            elseif ($page && ($parentId === (int) $page['id'] || in_array($parentId, Page::descendantIds((int) $page['id']), true))) {
                $errors['parent_id'] = 'Bir sayfa kendisinin ya da kendi alt sayfasının altına taşınamaz.';
            }
        }

        if (!isset(self::templates()[$data['template'] ?? ''])) $errors['template'] = 'Geçersiz şablon.';

        // Öne çıkan görsel sadece medya kütüphanesinden
        $image = trim((string) ($data['image'] ?? '')) ?: null;
        if ($image) {
            $media = Media::findByPath($image);
            if (!$media || $media['type'] !== 'image') $errors['image'] = 'Görsel medya kütüphanesinde bulunamadı.';
        }

        // Adres: boşsa başlıktan; en üst seviyede sistemin adresleriyle (blog, admin...) çakışamaz
        $slug = Page::slugify((string) (($data['slug'] ?? '') ?: $data['title']));
        if (!$parent && in_array($slug, self::reserved(), true)) {
            $errors['slug'] = "\"$slug\" adresi sistem tarafından kullanılıyor; başka bir adres yazın.";
        }

        if ($errors) Validator::fail($errors);

        $lang = $page['lang'] ?? \Core\Lang::content();
        $path = self::uniquePath(($parent ? $parent['path'] . '/' : '') . $slug, $page ? (int) $page['id'] : null, $lang);

        return [
            'parent_id'        => $parent['id'] ?? null,
            'title'            => trim($data['title']),
            'slug'             => basename($path),
            'path'             => $path,
            'lang'             => $lang,
            'body'             => Html::sanitize($data['body'] ?? ''),
            'template'         => $data['template'],
            'image'            => $image,
            'meta_description' => trim((string) ($data['meta_description'] ?? '')) ?: null,
            'status'           => $data['status'],
            'sort_order'       => (int) ($data['sort_order'] ?? 0),
        ];
    }

    // Aynı adres varsa sonuna -2, -3 eklenir
    private static function uniquePath(string $path, ?int $exceptId, ?string $lang = null): string
    {
        $candidate = $path;
        for ($i = 2; ($row = Page::findByPath($candidate, $lang)) && (int) $row['id'] !== $exceptId; $i++) $candidate = "$path-$i";
        return $candidate;
    }

    private static function rebuildChildPaths(int $parentId, string $parentPath): void
    {
        foreach (Page::query()->where('parent_id', $parentId)->get() as $child) {
            $path = self::uniquePath($parentPath . '/' . $child['slug'], (int) $child['id'], $child['lang']);
            Page::update($child['id'], ['path' => $path]);
            if ($child['status'] === 'published' && $path !== $child['path']) Hook::fire('url.moved', $child['path'], $path);
            self::rebuildChildPaths((int) $child['id'], $path);
        }
    }

    // Sistemin kullandığı ilk adres parçaları (route'lardan) + ileride gerekebilecekler
    private static function reserved(): array
    {
        return array_unique([...Router::reservedSegments(), 'admin', 'api', 'assets', 'theme', 'media', 'themes', 'storage', 'index.php', ...array_keys(\Core\Lang::all())]);
    }

    public static function stats(): array
    {
        return ['total' => Page::count(), 'published' => Page::count(['status' => 'published']), 'draft' => Page::count(['status' => 'draft'])];
    }
}
