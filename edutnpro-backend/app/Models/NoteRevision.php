<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteRevision extends Model
{
    protected $fillable = ['note_id', 'student_id', 'version', 'content', 'changes_summary'];

    protected $casts = ['version' => 'integer'];

    public function note(): BelongsTo { return $this->belongsTo(CollaborativeNote::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
