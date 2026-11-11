<?php
namespace App\Modules\Forms\Models;

use Core\Model;

class Form extends Model
{
    protected static string $table = 'forms';
    protected static array $fillable = ['name', 'slug', 'fields', 'settings'];

    public static function findBySlug(string $slug): ?array
    {
        return self::decode(static::query()->where('slug', $slug)->first());
    }

    public static function get(int $id): ?array
    {
        return self::decode(static::find($id));
    }

    // fields / settings JSON'dan diziye
    public static function decode(?array $form): ?array
    {
        if (!$form) return null;
        $form['fields'] = json_decode((string) $form['fields'], true) ?: [];
        $form['settings'] = (json_decode((string) $form['settings'], true) ?: []) + [
            'email_to' => '', 'subject' => 'Yeni form mesajı', 'success_message' => 'Teşekkürler! Mesajınız bize ulaştı.', 'button_text' => 'Gönder', 'store' => true,
        ];
        return $form;
    }

    // Seçim listeleri için: [id => ad]
    public static function options(): array
    {
        return static::query()->orderBy('name')->pluck('name', 'id');
    }
}
