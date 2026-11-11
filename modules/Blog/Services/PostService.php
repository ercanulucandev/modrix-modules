<?php
namespace App\Modules\Blog\Services;

use App\Models\Media;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Tag;
use Core\Database;
use Core\Hook;
use Core\Html;
use Core\HttpException;
use Core\Validator;

// Yazılarla ilgili iş mantığı. Controller'lar sadece bunu çağırır.
final class PostService
{
    public static function rules(): array
    {
        return [
            'title'            => 'required|max:200',
            'slug'             => 'nullable|max:180|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'body'             => 'nullable|max:200000',
            'excerpt'          => 'nullable|max:500',
            'status'           => 'required|in:' . implode(',', array_keys(Post::STATUSES)),
            'published_at'     => 'nullable|date',
            'category_id'      => 'nullable|integer',
            'image'            => 'nullable|max:255',
            'meta_description' => 'nullable|max:300',
        ];
    }

    public static function labels(): array
    {
        return [
            'title' => 'Başlık', 'slug' => 'Adres', 'body' => 'İçerik', 'excerpt' => 'Özet', 'status' => 'Durum',
            'published_at' => 'Yayın tarihi', 'category_id' => 'Kategori', 'image' => 'Öne çıkan görsel', 'meta_description' => 'Arama motoru açıklaması',
        ];
    }

    // Admin listesi. $filters: q, status (published|scheduled|draft), category
    public static function paginate(array $filters = [], int $perPage = 20, ?int $page = null): array
    {
        $now = date('Y-m-d H:i:s');
        $status = $filters['status'] ?? null;
        return Database::table('blog_posts as p')
            ->leftJoin('blog_categories as c', 'c.id', '=', 'p.category_id')
            ->select('p.id', 'p.title', 'p.slug', 'p.status', 'p.published_at', 'p.views', 'p.image', 'p.updated_at', 'p.created_at', 'p.category_id', 'c.name as category')
            ->where('p.lang', \Core\Lang::content())
            ->search(['p.title', 'p.slug'], $filters['q'] ?? null)
            ->when($status === 'draft', fn($q) => $q->where('p.status', 'draft'))
            ->when($status === 'published', fn($q) => $q->where('p.status', 'published')->where('p.published_at', '<=', $now))
            ->when($status === 'scheduled', fn($q) => $q->where('p.status', 'published')->where('p.published_at', '>', $now))
            ->when(!empty($filters['category']), fn($q) => $q->where('p.category_id', (int) $filters['category']))
            ->orderByRaw('p.published_at IS NULL DESC')->orderByDesc('p.published_at')->orderByDesc('p.id')
            ->paginate($perPage, $page);
    }

    // Başka dile çeviri: yazının taslak kopyası (kategori ve görsel aynı). Varsa mevcut çeviri.
    public static function translate(int $id, string $lang, ?int $authorId = null): array
    {
        $post = self::find($id);
        $siblings = \App\Services\Translations::siblings('blog_posts', $post);
        if (isset($siblings[$lang])) return Post::find((int) $siblings[$lang]['id']);
        $group = \App\Services\Translations::ensureGroup('blog_posts', $post);
        $newId = Post::create([
            'lang' => $lang, 'translation_group' => $group, 'title' => $post['title'], 'slug' => Post::uniqueSlug($post['slug'], null, $lang),
            'body' => $post['body'], 'excerpt' => $post['excerpt'], 'image' => $post['image'], 'meta_description' => $post['meta_description'],
            'status' => 'draft', 'published_at' => null, 'category_id' => $post['category_id'], 'author_id' => $authorId,
        ]);
        $copy = Post::find($newId);
        \Core\Hook::fire('post.created', $copy);
        return $copy;
    }

    public static function find(int $id): array
    {
        return Post::find($id) ?? throw new HttpException(404, 'Yazı bulunamadı');
    }

    public static function create(array $data, ?int $authorId = null): array
    {
        [$values, $tags] = self::prepare($data, null);
        $values['author_id'] = $authorId;
        $id = Database::transaction(function () use ($values, $tags) {
            $id = Post::create($values);
            Tag::sync($id, $tags);
            return $id;
        });
        $post = Post::find($id);
        Hook::fire('post.created', $post);
        return $post;
    }

    public static function update(int $id, array $data): array
    {
        $post = self::find($id);
        [$values, $tags] = self::prepare($data, $post);
        Database::transaction(function () use ($post, $values, $tags) {
            Post::update($post['id'], $values);
            Tag::sync($post['id'], $tags);
        });
        Tag::prune();
        // Adres değiştiyse eskisi yenisine 301 ile yönlensin (SEO)
        if ($values['slug'] !== $post['slug'] && Post::state($post) === 'published') Hook::fire('url.moved', 'blog/' . $post['slug'], 'blog/' . $values['slug']);

        $post = Post::find($post['id']);
        Hook::fire('post.updated', $post);
        return $post;
    }

    public static function duplicate(int $id, ?int $authorId = null): array
    {
        $post = self::find($id);
        $copy = array_intersect_key($post, array_flip(['body', 'excerpt', 'image', 'meta_description', 'category_id']));
        $copy += ['title' => mb_substr($post['title'] . ' (kopya)', 0, 200), 'slug' => '', 'status' => 'draft', 'published_at' => null,
                  'tags' => array_column(Post::tags($post['id']), 'name')];
        return self::create($copy, $authorId);
    }

    public static function delete(int $id): void
    {
        $post = self::find($id);
        Database::transaction(function () use ($post) {
            Database::table('blog_post_tag')->where('post_id', $post['id'])->delete();
            Post::delete($post['id']);
        });
        Tag::prune();
        Hook::fire('post.deleted', $post);
    }

    public static function stats(): array
    {
        $now = date('Y-m-d H:i:s');
        return [
            'total'     => Post::count(['lang' => \Core\Lang::content()]),
            'published' => Post::visible()->count(),
            'scheduled' => Post::query()->where('lang', \Core\Lang::content())->where('status', 'published')->where('published_at', '>', $now)->count(),
            'draft'     => Post::count(['status' => 'draft', 'lang' => \Core\Lang::content()]),
        ];
    }

    // Doğrulanmış veriyi kayda hazırlar: HTML temizliği, adres, kategori, görsel, etiketler, yayın tarihi
    private static function prepare(array $data, ?array $post): array
    {
        $errors = [];
        $body = Html::sanitize($data['body'] ?? '');
        if ($body === '' && ($data['status'] ?? '') === 'published') $errors['body'] = 'Yayınlamak için içerik yazın.';

        $categoryId = (int) ($data['category_id'] ?? 0) ?: null;
        if ($categoryId && !Category::find($categoryId)) $errors['category_id'] = 'Kategori bulunamadı.';

        $image = trim((string) ($data['image'] ?? '')) ?: null;
        if ($image) {
            $media = Media::findByPath($image);
            if (!$media || $media['type'] !== 'image') $errors['image'] = 'Görsel medya kütüphanesinde bulunamadı.';
        }

        $tags = is_array($data['tags'] ?? null) ? $data['tags'] : explode(',', (string) ($data['tags'] ?? ''));
        $tags = array_values(array_unique(array_filter(array_map(fn($t) => mb_substr(trim(preg_replace('/\s+/', ' ', (string) $t)), 0, 40), $tags), 'strlen')));
        if (count($tags) > 20) $errors['tags'] = 'En fazla 20 etiket.';

        if ($errors) Validator::fail($errors);

        // Yayın tarihi: boşsa yayınlanırken şimdi (taslakta boş kalır); ileri tarih = zamanlanmış
        $publishedAt = !empty($data['published_at']) ? date('Y-m-d H:i:s', strtotime($data['published_at'])) : null;
        if ($data['status'] === 'published' && !$publishedAt) $publishedAt = $post['published_at'] ?? date('Y-m-d H:i:s');

        return [[
            'title'            => trim($data['title']),
            'lang'             => $post['lang'] ?? \Core\Lang::content(),
            'slug'             => Post::uniqueSlug(($data['slug'] ?? '') ?: $data['title'], $post ? (int) $post['id'] : null, $post['lang'] ?? \Core\Lang::content()),
            'body'             => $body,
            'excerpt'          => trim((string) ($data['excerpt'] ?? '')) ?: null,
            'image'            => $image,
            'meta_description' => trim((string) ($data['meta_description'] ?? '')) ?: null,
            'status'           => $data['status'],
            'published_at'     => $publishedAt,
            'category_id'      => $categoryId,
        ], array_slice($tags, 0, 20)];
    }
}
