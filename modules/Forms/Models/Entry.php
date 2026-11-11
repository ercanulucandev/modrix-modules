<?php
namespace App\Modules\Forms\Models;

use Core\Model;

class Entry extends Model
{
    protected static string $table = 'form_entries';
    protected static bool $timestamps = false;

    public static function unreadCount(?int $formId = null): int
    {
        return static::query()->where('is_read', 0)->when($formId, fn($q) => $q->where('form_id', $formId))->count();
    }
}
