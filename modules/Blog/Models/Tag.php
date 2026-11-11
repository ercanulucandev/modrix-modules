<?php
namespace App\Modules\Blog\Models;

use Core\Database;
use Core\Model;

class Tag extends Model
{
    protected static string $table = 'blog_tags';
    protected static array $fillable = ['name', 'slug'];
    protected static bool $timestamps = false;

    // Yazının etiketlerini isim listesine göre ayarlar (olmayan etiket oluşturulur)
    public static function sync(int $postId, array $names): void
    {
        $ids = [];
        foreach ($names as $name) {
            $slug = Post::slugify($name);
            if ($slug === '') continue;
            $row = static::first(['slug' => $slug]);
            $ids[] = $row ? (int) $row['id'] : static::create(['name' => $name, 'slug' => $slug]);
        }
        Database::table('blog_post_tag')->where('post_id', $postId)->delete();
        foreach (array_unique($ids) as $id) Database::table('blog_post_tag')->insert(['post_id' => $postId, 'tag_id' => $id]);
    }

    // Hiçbir yazıda kullanılmayan etiketleri siler
    public static function prune(): void
    {
        Database::query('DELETE FROM blog_tags WHERE id NOT IN (SELECT tag_id FROM blog_post_tag)');
    }

    // [['id', 'name', 'slug', 'posts'], ...]
    public static function withCounts(): array
    {
        return Database::all('SELECT t.id, t.name, t.slug, COUNT(pt.post_id) AS posts FROM blog_tags t LEFT JOIN blog_post_tag pt ON pt.tag_id = t.id GROUP BY t.id, t.name, t.slug ORDER BY posts DESC, t.name');
    }
}
