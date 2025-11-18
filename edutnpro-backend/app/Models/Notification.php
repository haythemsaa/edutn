<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Notification extends Model
{
    protected $fillable = [
    'user_id',
    'type',
    'title',
    'title_ar',
    'message',
    'message_ar',
    'data',
    'channel',
    'priority',
    'is_read',
    'read_at',
    'sent_at'
];

    protected $casts = [
    'data' => 'array',
    'is_read' => 'boolean',
    'read_at' => 'datetime',
    'sent_at' => 'datetime'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
