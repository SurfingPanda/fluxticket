<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'subject_label', 'old_values', 'new_values', 'ip_address', 'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $description, $subject = null, array $old = [], array $new = []): void
    {
        static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject?->id,
            'subject_label' => $subject?->ticket_number ?? $subject?->name ?? null,
            'old_values'    => $old ?: null,
            'new_values'    => $new ?: null,
            'ip_address'    => request()->ip(),
            'description'   => $description,
        ]);
    }
}
