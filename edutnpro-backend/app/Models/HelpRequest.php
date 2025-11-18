<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpRequest extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'subject_id',
        'title',
        'question',
        'attachments',
        'urgency',
        'status',
        'answered_by',
        'answered_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'answered_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function answeredByStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'answered_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(HelpAnswer::class);
    }

    public function markAsAnswered(HelpAnswer $answer): void
    {
        $this->update([
            'status' => 'answered',
            'answered_by' => $answer->student_id,
            'answered_at' => now(),
        ]);

        $answer->update(['is_accepted' => true]);

        // Award XP to helper
        if ($answer->student->achievement) {
            $answer->student->achievement->addXP(
                15,
                XpTransaction::TYPE_EARNED,
                'peer_help',
                $answer,
                "Aide fournie à un camarade"
            );
        }
    }
}
