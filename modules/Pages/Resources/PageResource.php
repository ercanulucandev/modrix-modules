<?php
namespace App\Modules\Pages\Resources;

use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageService;
use Core\Request;

// API'nin döndüğü sayfa biçimi
final class PageResource
{
    // Liste satırı (içeriksiz)
    public static function summary(array $p): array
    {
        return [
            'id'             => (int) $p['id'],
            'parent_id'      => $p['parent_id'] ? (int) $p['parent_id'] : null,
            'depth'          => (int) ($p['depth'] ?? 0),
            'title'          => $p['title'],
            'path'           => $p['path'],
            'url'            => Request::url($p['path']),
            'status'         => $p['status'],
            'status_label'   => Page::STATUSES[$p['status']] ?? $p['status'],
            'template'       => $p['template'],
            'template_label' => PageService::templates()[$p['template']] ?? $p['template'],
            'sort_order'     => (int) $p['sort_order'],
            'updated_at'     => $p['updated_at'],
        ];
    }

    public static function make(array $p): array
    {
        return self::summary($p) + [
            'slug'             => $p['slug'],
            'body'             => (string) $p['body'],
            'image'            => $p['image'],
            'image_url'        => $p['image'] ? Request::url('media/' . $p['image']) : null,
            'meta_description' => $p['meta_description'],
            'created_at'       => $p['created_at'],
        ];
    }
}
