<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Message extends Model
{
    protected $fillable = [
    'sender_id',
    'recipient_id',
    'recipient_type',
    'subject',
    'body',
    'attachments',
    'read_at',
    'replied_at'
];

    protected $casts = [
    'attachments' => 'array',
    'read_at' => 'datetime',
    'replied_at' => 'datetime'
];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
