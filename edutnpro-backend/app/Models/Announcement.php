<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Announcement extends Model
{
    protected $fillable = [
    'school_id',
    'title_ar',
    'title_fr',
    'content_ar',
    'content_fr',
    'target',
    'target_id',
    'attachments',
    'published_at',
    'expires_at',
    'created_by'
];

    protected $casts = [
    'attachments' => 'array',
    'published_at' => 'datetime',
    'expires_at' => 'datetime'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
