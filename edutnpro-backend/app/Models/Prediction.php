<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Prediction extends Model
{
    protected $fillable = [
    'student_id',
    'prediction_type',
    'probability',
    'confidence_score',
    'factors',
    'recommendations',
    'risk_level',
    'prediction_date',
    'target_date',
    'was_accurate'
];

    protected $casts = [
    'probability' => 'decimal:2',
    'confidence_score' => 'decimal:2',
    'recommendations' => 'array',
    'prediction_date' => 'date',
    'target_date' => 'date',
    'was_accurate' => 'boolean'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
