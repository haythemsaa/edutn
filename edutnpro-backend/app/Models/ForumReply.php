<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReply extends Model
{
    protected $fillable = [
        'topic_id', 'student_id', 'content', 'attachments',
        'is_best_answer', 'is_moderated', 'upvotes', 'downvotes',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_best_answer' => 'boolean',
        'is_moderated' => 'boolean',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
    ];

    public function topic(): BelongsTo { return $this->belongsTo(ForumTopic::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
