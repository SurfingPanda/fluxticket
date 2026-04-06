<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNotification extends Model
{
    protected $table = 'ticket_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'ticket_id',
        'ticket_number',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
