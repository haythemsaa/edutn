<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends Model
{
    protected $fillable = [
        'forum_id',
        'student_id',
        'title',
        'content',
        'tags',
        'is_pinned',
        'is_locked',
        'is_solved',
        'views_count',
        'replies_count',
        'last_activity_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_solved' => 'boolean',
        'views_count' => 'integer',
        'replies_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(SubjectForum::class, 'forum_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'topic_id');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function markAsSolved(ForumReply $reply): void
    {
        $this->update(['is_solved' => true]);
        $reply->update(['is_best_answer' => true]);

        // Award XP to the student who provided the best answer
        if ($reply->student->achievement) {
            $reply->student->achievement->addXP(
                20,
                XpTransaction::TYPE_EARNED,
                'forum_help',
                $reply,
                "Meilleure réponse au forum"
            );
        }
    }
}
