<?php
namespace App\Modules\Pages\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Resources\PageResource;
use App\Modules\Pages\Services\PageService;
use Core\Setting;

// Admin JSON API'si: /admin/api/pages  (admin oturumu + X-CSRF-TOKEN)
//
//   GET    /                 ağaç sırasında tüm sayfalar ?q=&status=        -> { data: [...], meta: { stats, front_page } }
//   GET    /options          düzenleyici seçenekleri (üst sayfalar, şablonlar, durumlar)
//   GET    /{id}             tek sayfa (içerikle)
//   POST   /                 oluştur                                        -> 201 { data, message }
//   PUT    /{id}             güncelle                                       -> { data, message }
//   POST   /{id}/duplicate   taslak kopya                                   -> 201 { data, message }
//   DELETE /{id}             sil (alt sayfalar bir üste bağlanır)           -> { message }
class PageApiController extends AdminController
{
    public function index(): array
    {
        $status = isset(Page::STATUSES[(string) Request::get('status')]) ? Request::get('status') : null;
        $rows = Page::tree($status);

        // Aramada ağaç düzleşir
        if ($q = trim((string) Request::get('q', ''))) {
            $needle = mb_strtolower($q);
            $rows = array_values(array_filter($rows, fn($p) => str_contains(mb_strtolower($p['title'] . ' ' . $p['path']), $needle)));
            foreach ($rows as &$row) $row['depth'] = 0;
            unset($row);
        }

        return [
            'data' => array_map([PageResource::class, 'summary'], $rows),
            'meta' => ['stats' => PageService::stats(), 'front_page' => (int) Setting::get('pages_front_page') ?: null],
        ];
    }

    public function options(): array
    {
        return ['data' => [
            'pages'     => array_map(fn($p) => ['id' => (int) $p['id'], 'title' => $p['title'], 'depth' => $p['depth']], Page::tree()),
            'templates' => PageService::templates(),
            'statuses'  => Page::STATUSES,
        ]];
    }

    public function show(string $id): array
    {
        return ['data' => PageResource::make(PageService::find((int) $id))];
    }

    public function store(): string
    {
        $page = PageService::create($this->validated(), (int) $this->admin()['id']);
        return $this->json(['data' => PageResource::make($page), 'message' => 'Sayfa oluşturuldu.'], 201);
    }

    public function update(string $id): array
    {
        $page = PageService::update((int) $id, $this->validated());
        return ['data' => PageResource::make($page), 'message' => 'Sayfa kaydedildi.'];
    }

    public function duplicate(string $id): string
    {
        $page = PageService::duplicate((int) $id, (int) $this->admin()['id']);
        return $this->json(['data' => PageResource::make($page), 'message' => 'Taslak kopya oluşturuldu.'], 201);
    }

    public function destroy(string $id): array
    {
        PageService::delete((int) $id);
        return ['message' => 'Sayfa silindi.'];
    }

    private function validated(): array
    {
        return $this->validate(PageService::rules(), PageService::labels());
    }
}
