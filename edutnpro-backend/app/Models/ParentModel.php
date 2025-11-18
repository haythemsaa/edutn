<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class ParentModel extends Model
{
    protected $table = 'parents';

    protected $fillable = [
    'cin',
    'first_name_ar',
    'last_name_ar',
    'first_name_fr',
    'last_name_fr',
    'date_of_birth',
    'phone_mobile',
    'phone_work',
    'email',
    'profession',
    'employer',
    'work_address',
    'monthly_income',
    'address',
    'city',
    'postal_code'
];

    protected $casts = [
    'date_of_birth' => 'date',
    'monthly_income' => 'decimal:2'
];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_parent')->withPivot('relationship', 'is_primary_contact', 'can_pick_up', 'can_authorize_medical')->withTimestamps();
    }
}
