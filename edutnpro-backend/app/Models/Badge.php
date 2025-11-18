<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Badge extends Model
{
    protected $fillable = [
    'name',
    'name_ar',
    'description',
    'description_ar',
    'icon',
    'color',
    'category',
    'criteria',
    'points',
    'rarity',
    'is_active'
];

    protected $casts = [
    'criteria' => 'array',
    'is_active' => 'boolean',
    'points' => 'integer'
];

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_badges')->withPivot('earned_at', 'progress')->withTimestamps();
    }
}
