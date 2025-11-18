<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class EmergencyContact extends Model
{
    protected $fillable = [
    'student_id',
    'name',
    'relationship',
    'phone',
    'priority'
];

    protected $casts = [
    'priority' => 'integer'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
