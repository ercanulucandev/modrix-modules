<?php
namespace App\Modules\Blog\Resources;

use App\Modules\Blog\Models\Post;
use Core\Html;
use Core\Request;
use Core\Router;

// API'nin döndüğü yazı biçimi
final class PostResource
{
    private const STATES = ['published' => 'Yayında', 'scheduled' => 'Zamanlanmış', 'draft' => 'Taslak'];

    public static function summary(array $p): array
    {
        $state = Post::state($p);
        return [
            'id'           => (int) $p['id'],
            'title'        => $p['title'],
            'slug'         => $p['slug'],
            'status'       => $p['status'],
            'state'        => $state,
            'state_label'  => self::STATES[$state],
            'published_at' => $p['published_at'],
            'category_id'  => $p['category_id'] ? (int) $p['category_id'] : null,
            'category'     => $p['category'] ?? null,
            'views'        => (int) ($p['views'] ?? 0),
            'thumb'        => $p['image'] ? Request::url('media/' . $p['image']) : null,
            'url'          => Router::url('blog.show', ['slug' => $p['slug']]),
            'updated_at'   => $p['updated_at'],
        ];
    }

    public static function make(array $p): array
    {
        return self::summary($p) + [
            'body'             => (string) $p['body'],
            'excerpt'          => $p['excerpt'],
            'image'            => $p['image'],
            'image_url'        => $p['image'] ? Request::url('media/' . $p['image']) : null,
            'meta_description' => $p['meta_description'],
            'tags'             => array_column(Post::tags((int) $p['id']), 'name'),
            'auto_excerpt'     => Html::text($p['body'], 160),
            'created_at'       => $p['created_at'],
        ];
    }
}
