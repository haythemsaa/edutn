<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollaborativeNote extends Model
{
    protected $fillable = [
        'school_id',
        'subject_id',
        'creator_id',
        'title',
        'title_ar',
        'content',
        'contributors',
        'access_level',
        'class_id',
        'study_group_id',
        'version',
    ];

    protected $casts = [
        'contributors' => 'array',
        'version' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'creator_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(NoteRevision::class, 'note_id');
    }

    public function updateContent(Student $student, string $content, string $changesSummary = null): void
    {
        // Create revision
        NoteRevision::create([
            'note_id' => $this->id,
            'student_id' => $student->id,
            'version' => $this->version,
            'content' => $this->content,
            'changes_summary' => $changesSummary,
        ]);

        // Add contributor if not already in list
        $contributors = $this->contributors ?? [];
        if (!in_array($student->id, $contributors)) {
            $contributors[] = $student->id;
        }

        // Update note
        $this->update([
            'content' => $content,
            'contributors' => $contributors,
            'version' => $this->version + 1,
        ]);
    }

    public function canEdit(Student $student): bool
    {
        if ($this->creator_id === $student->id) {
            return true;
        }

        switch ($this->access_level) {
            case 'public':
                return true;
            case 'class':
                return $this->class_id === $student->class_id;
            case 'group':
                return $this->studyGroup && $this->studyGroup->isMember($student);
            case 'private':
                return false;
            default:
                return false;
        }
    }
}
