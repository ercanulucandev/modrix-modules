<?php
namespace App\Modules\Blog\Models;

use Core\Model;

class Category extends Model
{
    protected static string $table = 'blog_categories';
    protected static array $fillable = ['name', 'slug', 'description', 'sort_order'];

    public static function ordered(): array
    {
        return static::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    // Seçim listesi: [id => ad]
    public static function options(): array
    {
        return array_column(self::ordered(), 'name', 'id');
    }

    public static function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Post::slugify($name) ?: 'kategori';
        $slug = $base;
        for ($i = 2; ($row = static::first(['slug' => $slug])) && (int) $row['id'] !== $exceptId; $i++) $slug = "$base-$i";
        return $slug;
    }
}
