<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class DietaryRestriction extends Model
{
    protected $fillable = [
    'student_id',
    'type',
    'restriction',
    'description',
    'severity',
    'special_instructions',
    'is_active'
];

    protected $casts = [
    'is_active' => 'boolean'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
