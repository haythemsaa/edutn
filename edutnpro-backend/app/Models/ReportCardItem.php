<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardItem extends Model
{
    protected $fillable = [
        'report_card_id', 'subject_id', 'grade', 'class_average', 'highest_grade', 'lowest_grade',
        'coefficient', 'weighted_grade', 'teacher_comment', 'teacher_comment_ar', 'appreciation',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'class_average' => 'decimal:2',
        'highest_grade' => 'decimal:2',
        'lowest_grade' => 'decimal:2',
        'weighted_grade' => 'decimal:2',
    ];

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}