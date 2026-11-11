<?php
namespace App\Modules\Blog\Models;

use Core\Database;
use Core\Model;
use Core\QueryBuilder;
use Core\Request;

class Post extends Model
{
    public const STATUSES = ['published' => 'Yayında', 'draft' => 'Taslak'];

    protected static string $table = 'blog_posts';
    protected static array $fillable = ['lang', 'translation_group', 'title', 'slug', 'body', 'excerpt', 'image', 'meta_description', 'status', 'published_at', 'category_id', 'author_id'];

    // Sitede görünenler: yayında ve yayın tarihi gelmiş (ileri tarihli olanlar zamanı gelince kendiliğinden görünür)
    // Yayındaki yazılar; dil: geçerli dil (panelde düzenlenen dil), '*' tüm diller
    public static function visible(?string $lang = null): QueryBuilder
    {
        $lang ??= \Core\Lang::content();
        return static::query()->where('status', 'published')->where('published_at', '<=', date('Y-m-d H:i:s'))
            ->when($lang !== '*', fn($q) => $q->where('lang', $lang));
    }

    public static function published(?int $limit = null): array
    {
        return self::visible()->orderByDesc('published_at')->when($limit, fn($q) => $q->limit($limit))->get();
    }

    public static function findBySlug(string $slug, ?string $lang = null): ?array
    {
        return static::query()->where('lang', $lang ?? \Core\Lang::content())->where('slug', $slug)->first();
    }

    public static function incrementViews(int $id): void
    {
        static::query()->where('id', $id)->increment('views');
    }

    // Durum: 'published' | 'scheduled' (yayında ama tarihi gelecekte) | 'draft'
    public static function state(array $post): string
    {
        if ($post['status'] !== 'published') return 'draft';
        return $post['published_at'] && strtotime($post['published_at']) > time() ? 'scheduled' : 'published';
    }

    // Kapak: öne çıkan görsel, yoksa yazıdaki ilk görsel (adres ya da null)
    public static function cover(array $post): ?string
    {
        if (!empty($post['image'])) return Request::url('media/' . $post['image']);
        return preg_match('/<img[^>]+src="([^"]+)"/i', (string) ($post['body'] ?? ''), $m) ? html_entity_decode($m[1]) : null;
    }

    // Etiketler: [['id', 'name', 'slug'], ...]
    public static function tags(int $postId): array
    {
        return Database::table('blog_tags as t')->join('blog_post_tag as pt', 'pt.tag_id', '=', 't.id')
            ->where('pt.post_id', $postId)->select('t.id', 't.name', 't.slug')->orderBy('t.name')->get();
    }

    // "Merhaba Dünya!" -> "merhaba-dunya"; aynısı varsa sonuna -2, -3 eklenir
    public static function uniqueSlug(string $title, ?int $exceptId = null, ?string $lang = null): string
    {
        $base = self::slugify($title) ?: 'yazi';
        $slug = $base;
        for ($i = 2; ($row = static::findBySlug($slug, $lang)) && (int) $row['id'] !== $exceptId; $i++) $slug = "$base-$i";
        return $slug;
    }

    public static function slugify(string $text): string
    {
        return substr(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($text, [
            'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        ]))), '-'), 0, 180);
    }
}
