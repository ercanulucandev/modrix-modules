<?php
namespace App\Modules\Blog\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Tag;
use App\Modules\Blog\Resources\PostResource;
use App\Modules\Blog\Services\PostService;
use Core\Validator;

// Admin JSON API'si: /admin/api/blog/...  (admin oturumu + X-CSRF-TOKEN)
//
//   GET    /posts                 liste ?q=&status=published|scheduled|draft&category=&page=
//   GET    /options               kategoriler, etiketler (öneri), durumlar
//   GET    /posts/{id}            tek yazı (içerik, etiketler)
//   POST   /posts                 oluştur            PUT /posts/{id}   güncelle
//   POST   /posts/{id}/duplicate  taslak kopya       DELETE /posts/{id} sil
//   POST   /categories            hızlı kategori ekleme (düzenleyiciden) { name }
class PostApiController extends AdminController
{
    public function index(): array
    {
        $page = PostService::paginate([
            'q'        => Request::get('q'),
            'status'   => Request::get('status'),
            'category' => Request::get('category'),
        ], 20);

        return [
            'data' => array_map([PostResource::class, 'summary'], $page['data']),
            'meta' => array_diff_key($page, ['data' => true]) + ['stats' => PostService::stats()],
        ];
    }

    public function options(): array
    {
        return ['data' => [
            'categories' => array_map(fn($c) => ['id' => (int) $c['id'], 'name' => $c['name']], Category::ordered()),
            'tags'       => array_column(Tag::withCounts(), 'name'),
            'statuses'   => Post::STATUSES,
        ]];
    }

    public function show(string $id): array
    {
        return ['data' => PostResource::make(PostService::find((int) $id))];
    }

    public function store(): string
    {
        $post = PostService::create($this->validated(), (int) $this->admin()['id']);
        return $this->json(['data' => PostResource::make($post), 'message' => 'Yazı oluşturuldu.'], 201);
    }

    public function update(string $id): array
    {
        $post = PostService::update((int) $id, $this->validated());
        return ['data' => PostResource::make($post), 'message' => 'Yazı kaydedildi.'];
    }

    public function duplicate(string $id): string
    {
        $post = PostService::duplicate((int) $id, (int) $this->admin()['id']);
        return $this->json(['data' => PostResource::make($post), 'message' => 'Taslak kopya oluşturuldu.'], 201);
    }

    public function destroy(string $id): array
    {
        PostService::delete((int) $id);
        return ['message' => 'Yazı silindi.'];
    }

    public function storeCategory(): string
    {
        $name = trim((string) Request::post('name', ''));
        if ($name === '' || mb_strlen($name) > 80) Validator::fail(['name' => 'Kategori adı 1-80 karakter olmalı.']);
        $id = Category::create(['name' => $name, 'slug' => Category::uniqueSlug($name)]);
        return $this->json(['data' => ['id' => $id, 'name' => $name]], 201);
    }

    private function validated(): array
    {
        $data = $this->validate(PostService::rules(), PostService::labels());
        $data['tags'] = Request::post('tags', []);
        return $data;
    }
}
