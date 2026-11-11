<?php
namespace App\Modules\Blog\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Tag;
use Core\Database;
use Core\View;

// Admin > Yazılar > Kategoriler ve etiketler
class TaxonomyController extends AdminController
{
    public function index(): View
    {
        $counts = array_column(Database::all('SELECT category_id, COUNT(*) AS total FROM blog_posts WHERE category_id IS NOT NULL GROUP BY category_id'), 'total', 'category_id');
        return $this->view('Blog::admin/taxonomies', [
            'title'      => 'Kategoriler ve etiketler',
            'categories' => Category::ordered(),
            'counts'     => $counts,
            'tags'       => Tag::withCounts(),
            'editing'    => ($id = (int) Request::get('edit')) ? Category::find($id) : null,
        ]);
    }

    public function store(): void
    {
        $data = $this->input();
        Category::create($data + ['slug' => Category::uniqueSlug($data['slug'] ?: $data['name'])]);
        $this->redirect('admin/blog/categories', 'Kategori eklendi.');
    }

    public function update(string $id): void
    {
        $category = Category::find((int) $id) ?? $this->abort(404);
        $data = $this->input();
        $slug = Category::uniqueSlug($data['slug'] ?: $data['name'], $category['id']);
        Category::update($category['id'], ['slug' => $slug] + $data);
        if ($slug !== $category['slug']) Hook::fire('url.moved', 'blog/kategori/' . $category['slug'], 'blog/kategori/' . $slug);
        $this->redirect('admin/blog/categories', 'Kategori kaydedildi.');
    }

    // Kategori silinince yazıları kategorisiz kalır (silinmez)
    public function destroy(string $id): void
    {
        $category = Category::find((int) $id) ?? $this->abort(404);
        Database::transaction(function () use ($category) {
            Post::query()->where('category_id', $category['id'])->update(['category_id' => null]);
            Category::delete($category['id']);
        });
        $this->redirect('admin/blog/categories', "\"{$category['name']}\" silindi; yazıları kategorisiz kaldı.");
    }

    public function destroyTag(string $id): void
    {
        Database::table('blog_post_tag')->where('tag_id', (int) $id)->delete();
        Tag::delete((int) $id);
        $this->back('Etiket tüm yazılardan kaldırıldı.');
    }

    private function input(): array
    {
        $data = $this->validate(
            ['name' => 'required|max:80', 'slug' => 'nullable|max:90|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'description' => 'nullable|max:300', 'sort_order' => 'nullable|integer|between:0,9999'],
            ['name' => 'Ad', 'slug' => 'Adres', 'description' => 'Açıklama', 'sort_order' => 'Sıra']
        );
        return ['name' => trim($data['name']), 'slug' => (string) ($data['slug'] ?? ''), 'description' => $data['description'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0)];
    }
}
