<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class NotificationPreference extends Model
{
    protected $fillable = [
    'user_id',
    'email_enabled',
    'sms_enabled',
    'push_enabled',
    'notification_types',
    'quiet_hours_start',
    'quiet_hours_end',
    'preferred_language'
];

    protected $casts = [
    'email_enabled' => 'boolean',
    'sms_enabled' => 'boolean',
    'push_enabled' => 'boolean',
    'notification_types' => 'array'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
