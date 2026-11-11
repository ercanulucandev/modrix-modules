<?php
namespace App\Modules\Forms\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Forms\Models\Entry;
use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Services\FormService;
use Core\Database;
use Core\View;

// Admin > Formlar: liste, form oluşturucu (sayfa iskeleti), gelen mesajlar, CSV dışa aktarma
class FormController extends AdminController
{
    public function index(): View
    {
        $forms = Database::table('forms as f')
            ->select('f.id', 'f.name', 'f.slug', 'f.updated_at')
            ->selectRaw('(SELECT COUNT(*) FROM form_entries e WHERE e.form_id = f.id) AS entries')
            ->selectRaw('(SELECT COUNT(*) FROM form_entries e WHERE e.form_id = f.id AND e.is_read = 0) AS unread')
            ->orderBy('f.name')->get();
        return $this->view('Forms::admin/index', ['title' => 'Formlar', 'forms' => $forms]);
    }

    // Yeni form: hazır iki alanla oluşturulur, oluşturucuya gidilir
    public function create(): void
    {
        $id = Form::create([
            'name'     => 'Yeni form',
            'slug'     => FormService::uniqueSlug('yeni-form'),
            'fields'   => json_encode([
                ['name' => 'ad', 'label' => 'Adınız', 'type' => 'text', 'required' => true, 'placeholder' => '', 'options' => [], 'width' => 'half', 'help' => ''],
                ['name' => 'eposta', 'label' => 'E-posta', 'type' => 'email', 'required' => true, 'placeholder' => '', 'options' => [], 'width' => 'half', 'help' => ''],
            ], JSON_UNESCAPED_UNICODE),
            'settings' => json_encode(FormService::cleanSettings([]), JSON_UNESCAPED_UNICODE),
        ]);
        $this->redirect("admin/forms/$id/edit");
    }

    public function edit(string $id): View
    {
        $form = Form::get((int) $id) ?? $this->abort(404);
        return $this->view('Forms::admin/edit', ['title' => $form['name'], 'form' => $form, 'types' => FormService::TYPES]);
    }

    public function destroy(string $id): void
    {
        $form = Form::get((int) $id) ?? $this->abort(404);
        Form::delete($form['id']);   // mesajlar FOREIGN KEY ile silinir
        $this->redirect('admin/forms', "\"{$form['name']}\" ve mesajları silindi.");
    }

    // Gelen mesajlar (sayfalı). ?filter=unread
    public function entries(string $id): View
    {
        $form = Form::get((int) $id) ?? $this->abort(404);
        $unreadOnly = Request::get('filter') === 'unread';
        $page = Entry::query()->where('form_id', $form['id'])
            ->when($unreadOnly, fn($q) => $q->where('is_read', 0))
            ->orderByDesc('id')->paginate(20);
        foreach ($page['data'] as &$entry) $entry['data'] = json_decode((string) $entry['data'], true) ?: [];
        unset($entry);

        return $this->view('Forms::admin/entries', [
            'title'      => $form['name'] . ' · mesajlar',
            'form'       => $form,
            'entries'    => $page,
            'unreadOnly' => $unreadOnly,
            'unread'     => Entry::unreadCount($form['id']),
        ]);
    }

    // Okundu / okunmadı (JS açılınca okundu yapar)
    public function markRead(string $id, string $entryId): string
    {
        $read = Request::post('read', '1') ? 1 : 0;
        Entry::query()->where('form_id', (int) $id)->where('id', (int) $entryId)->update(['is_read' => $read]);
        return $this->json(['ok' => true, 'unread' => Entry::unreadCount((int) $id)]);
    }

    public function markAllRead(string $id): void
    {
        Entry::query()->where('form_id', (int) $id)->where('is_read', 0)->update(['is_read' => 1]);
        $this->redirect("admin/forms/$id/entries", 'Tüm mesajlar okundu olarak işaretlendi.');
    }

    public function deleteEntry(string $id, string $entryId): void
    {
        Entry::query()->where('form_id', (int) $id)->where('id', (int) $entryId)->delete();
        $this->back('Mesaj silindi.');
    }

    // Excel'de Türkçe karakterler bozulmasın diye UTF-8 BOM ve ; ayırıcı
    public function export(string $id): never
    {
        $form = Form::get((int) $id) ?? $this->abort(404);
        $columns = array_column($form['fields'], 'label', 'name');

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $form['slug'] . '-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: private, no-store');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tarih', ...array_values($columns), 'Sayfa', 'IP'], ';');
        Entry::query()->where('form_id', $form['id'])->orderByDesc('id')->chunk(500, function (array $rows) use ($out, $columns) {
            foreach ($rows as $row) {
                $data = json_decode((string) $row['data'], true) ?: [];
                // Formül enjeksiyonuna karşı (=, +, -, @ ile başlayan hücreler)
                $cell = fn($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : (string) $v;
                fputcsv($out, [$row['created_at'], ...array_map(fn($k) => $cell($data[$k]['value'] ?? ''), array_keys($columns)), $row['page_url'], $row['ip']], ';');
            }
        });
        fclose($out);
        exit;
    }
}
