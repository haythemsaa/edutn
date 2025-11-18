<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'message_type',
        'attachments',
        'reply_to_message_id',
        'is_system_message',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
        'read_count',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_system_message' => 'boolean',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }
}