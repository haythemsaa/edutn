<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpAnswer extends Model
{
    protected $fillable = [
        'help_request_id', 'student_id', 'answer', 'attachments',
        'is_accepted', 'helpful_count',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_accepted' => 'boolean',
        'helpful_count' => 'integer',
    ];

    public function helpRequest(): BelongsTo { return $this->belongsTo(HelpRequest::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
