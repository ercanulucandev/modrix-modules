<?php
// Blog API'si: /api/posts?page=2  ->  { data, total, page, per_page, pages, from, to }  (sadece yayında ve tarihi gelmiş yazılar)
use App\Modules\Blog\Models\Post;

Router::get('/posts', fn() => Post::visible()->select('id', 'title', 'slug', 'excerpt', 'published_at', 'category_id')->orderByDesc('published_at')->paginate(20));
