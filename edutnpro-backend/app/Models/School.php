<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name_ar',
        'name_fr',
        'code',
        'logo',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'ministry_approval_number',
        'school_type',
        'education_level',
        'capacity',
        'director_name',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    // Relations
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
