<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'class_id',
        'registration_number',
        'first_name',
        'last_name',
        'first_name_ar',
        'last_name_ar',
        'date_of_birth',
        'gender',
        'place_of_birth',
        'nationality',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'photo',
        'medical_notes',
        'status',
        'enrollment_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'student_parent')
            ->withPivot('relationship', 'is_primary_contact', 'can_pick_up', 'can_authorize_medical')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    // Gamification Relations
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'student_badges')
            ->withPivot('earned_at', 'progress', 'metadata')
            ->withTimestamps();
    }

    public function achievement(): HasOne
    {
        return $this->hasOne(StudentAchievement::class);
    }

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class, 'student_challenges')
            ->withPivot('progress', 'completed_at', 'xp_earned')
            ->withTimestamps();
    }

    public function points(): HasOne
    {
        return $this->hasOne(StudentPoint::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    // Library Relations
    public function libraryLoans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function readingStats(): HasMany
    {
        return $this->hasMany(ReadingStat::class);
    }

    // Transport Relations
    public function transportation(): HasOne
    {
        return $this->hasOne(StudentTransportation::class);
    }

    // Canteen Relations
    public function mealReservations(): HasMany
    {
        return $this->hasMany(MealReservation::class);
    }

    public function dietaryRestrictions(): HasMany
    {
        return $this->hasMany(DietaryRestriction::class);
    }

    // Analytics Relations
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullNameArAttribute(): string
    {
        return "{$this->first_name_ar} {$this->last_name_ar}";
    }
}
