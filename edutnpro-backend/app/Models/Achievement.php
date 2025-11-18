<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Achievement extends Model
{
    protected $fillable = [
    'student_id',
    'type',
    'title',
    'title_ar',
    'description',
    'points_earned',
    'data',
    'achieved_at'
];

    protected $casts = [
    'points_earned' => 'integer',
    'data' => 'array',
    'achieved_at' => 'datetime'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
