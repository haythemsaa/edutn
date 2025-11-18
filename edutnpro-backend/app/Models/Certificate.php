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

    public function isValid(): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        if ($this->valid_until && $this->valid_until->lt(now())) {
            return false;
        }

        return true;
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function getVerificationUrl(): string
    {
        return route('certificates.verify', [
            'certificate' => $this->id,
            'code' => $this->certificate_number,
        ]);
    }

    public function generateCertificateNumber(string $type): string
    {
        $prefix = match ($type) {
            'enrollment' => 'ENR',
            'completion' => 'CMP',
            'achievement' => 'ACH',
            'attendance' => 'ATT',
            'conduct' => 'CND',
            'transcript' => 'TRN',
            default => 'CRT',
        };

        return $prefix . '-' . date('Y') . '-' . str_pad(
            Certificate::where('certificate_type', $type)->count() + 1,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}