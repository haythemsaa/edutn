<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'student_id', 'school_id', 'certificate_type', 'certificate_number',
        'title', 'title_ar', 'description', 'description_ar', 'issued_date',
        'issued_by', 'academic_year', 'template', 'file_path', 'digital_signature',
        'metadata', 'status', 'valid_until', 'revocation_reason', 'downloaded_at',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'valid_until' => 'date',
        'metadata' => 'array',
        'downloaded_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}