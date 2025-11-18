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

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    // Library Relations
    public function libraryBooks(): HasMany
    {
        return $this->hasMany(LibraryBook::class);
    }

    // Transport Relations
    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    // Canteen Relations
    public function canteenMenus(): HasMany
    {
        return $this->hasMany(CanteenMenu::class);
    }

    // Analytics Relations
    public function analyticsSnapshots(): HasMany
    {
        return $this->hasMany(AnalyticsSnapshot::class);
    }

    public function leaderboards(): HasMany
    {
        return $this->hasMany(Leaderboard::class);
    }
}
