<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'teacher_id', 'document_type', 'title', 'title_ar',
        'description', 'description_ar', 'file_path', 'file_type', 'file_size',
        'uploaded_by', 'is_official', 'requires_signature', 'document_number',
        'issue_date', 'expiry_date', 'status', 'metadata', 'tags', 'download_count',
        'last_downloaded_at',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'requires_signature' => 'boolean',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'metadata' => 'array',
        'tags' => 'array',
        'last_downloaded_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}