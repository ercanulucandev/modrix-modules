<?php
namespace App\Modules\Blog\Controllers;

use App\Models\User;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use Core\Controller;
use Core\Database;
use Core\Html;
use Core\Router;
use Core\Seo;
use Core\Setting;
use Core\View;

// Sitedeki blog: liste, kategori, etiket, yazı, RSS. Sadece yayında ve yayın tarihi gelmiş yazılar.
class PostController extends Controller
{
    public function index(): View
    {
        return $this->listing(null, Setting::get('blog_title'), Setting::get('blog_description'));
    }

    public function category(string $slug): View
    {
        $category = Category::first(['slug' => $slug]) ?? $this->abort(404, 'Kategori bulunamadı');
        return $this->listing(fn($q) => $q->where('p.category_id', $category['id']), $category['name'], $category['description'], $category);
    }

    public function tag(string $slug): View
    {
        $tag = Database::table('blog_tags')->where('slug', $slug)->first() ?? $this->abort(404, 'Etiket bulunamadı');
        return $this->listing(
            fn($q) => $q->whereRaw('p.id IN (SELECT post_id FROM blog_post_tag WHERE tag_id = ?)', [$tag['id']]),
            '#' . $tag['name'], null, null, $tag
        );
    }

    public function show(string $slug): View
    {
        $post = Post::findBySlug($slug);
        if (!$post && !\Core\Lang::isDefault() && ($fallback = Post::findBySlug($slug, \Core\Lang::default())) && Post::state($fallback) === 'published') {
            header('Location: ' . \Core\Lang::url('blog/' . $fallback['slug'], \Core\Lang::default()), true, 302);   // henüz çevrilmemiş
            exit;
        }
        $isDraft = $post && Post::state($post) !== 'published';
        // Taslak ve zamanlanmış yazılar sadece giriş yapmış admin'e (önizleme)
        if (!$post || ($isDraft && !User::admin())) $this->abort(404, 'Yazı bulunamadı');
        if (!$isDraft) Post::incrementViews($post['id']);
        if (\Core\Lang::enabled()) {
            $map = [];
            $siblings = \App\Services\Translations::siblings('blog_posts', $post);
            foreach (\Core\Lang::all() as $code => $_) {
                $s = $siblings[$code] ?? null;
                $map[$code] = $s && Post::state($s) === 'published' ? 'blog/' . $s['slug'] : 'blog';
            }
            \Core\Lang::setAlternates($map);
        }
        $category = $post['category_id'] ? Category::find((int) $post['category_id']) : null;
        $cover = Post::cover($post);
        $description = $post['meta_description'] ?: ($post['excerpt'] ?: Html::text($post['body'], 160));

        // Arama motorları için makale bilgisi
        Seo::add(array_filter([
            '@type'          => 'BlogPosting',
            'headline'       => $post['title'],
            'description'    => $description,
            'datePublished'  => date('c', strtotime($post['published_at'] ?? $post['created_at'])),
            'dateModified'   => date('c', strtotime($post['updated_at'] ?? $post['created_at'])),
            'image'          => $cover ? Seo::absolute($cover) : null,
            'articleSection' => $category['name'] ?? null,
            'keywords'       => implode(', ', array_column(Post::tags($post['id']), 'name')) ?: null,
            'url'            => Seo::absolute(Router::url('blog.show', ['slug' => $post['slug']])),
            'author'         => ['@id' => Seo::absolute('/') . '#owner'],
            'publisher'      => ['@id' => Seo::absolute('/') . '#owner'],
        ]));

        // İlgili yazılar: aynı kategoriden son 3 (yoksa son yazılar)
        $related = Post::visible()->where('id', '!=', $post['id'])
            ->when($post['category_id'], fn($q) => $q->where('category_id', $post['category_id']))
            ->orderByDesc('published_at')->limit(3)->get();

        return $this->view('Blog::show', [
            'title'           => $post['title'],
            'metaDescription' => $description,
            'metaImage'       => $cover,
            'post'            => $post,
            'category'        => $category,
            'tags'            => Post::tags($post['id']),
            'related'         => $related,
            'isDraft'         => $isDraft,
        ]);
    }

    // RSS 2.0: son 20 yazı (/blog/feed.xml)
    public function feed(): never
    {
        $site = (string) Setting::get('site_name', 'Site');
        header('Content-Type: application/rss+xml; charset=utf-8');
        $x = fn($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<rss version="2.0"><channel>';
        echo '<title>' . $x(Setting::get('blog_title') . ' · ' . $site) . '</title><link>' . $x(Seo::absolute(Router::url('blog.index'))) . '</link>';
        echo '<description>' . $x(Setting::get('blog_description') ?: Setting::get('site_description')) . '</description><language>tr-TR</language>';
        foreach (Post::published(20) as $p) {
            $url = Seo::absolute(Router::url('blog.show', ['slug' => $p['slug']]));
            echo '<item><title>' . $x($p['title']) . '</title><link>' . $x($url) . '</link><guid>' . $x($url) . '</guid>'
               . '<pubDate>' . date(DATE_RSS, strtotime($p['published_at'])) . '</pubDate>'
               . '<description>' . $x($p['excerpt'] ?: Html::text($p['body'], 300)) . '</description></item>';
        }
        echo '</channel></rss>';
        exit;
    }

    private function listing(?callable $filter, ?string $title, ?string $description, ?array $category = null, ?array $tag = null): View
    {
        $perPage = max(1, min(50, (int) Setting::get('blog_per_page')));
        $posts = Database::table('blog_posts as p')
            ->leftJoin('blog_categories as c', 'c.id', '=', 'p.category_id')
            ->select('p.*', 'c.name as category_name', 'c.slug as category_slug')
            ->where('p.status', 'published')->where('p.published_at', '<=', date('Y-m-d H:i:s'))
            ->where('p.lang', \Core\Lang::current())
            ->when($filter !== null, fn($q) => $filter($q))
            ->orderByDesc('p.published_at')
            ->paginate($perPage);

        return $this->view('Blog::index', [
            'title'           => $title,
            'metaDescription' => $description,
            'posts'           => $posts,
            'categories'      => Category::ordered(),
            'category'        => $category,
            'tag'             => $tag,
        ]);
    }
}
