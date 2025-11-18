<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class MealReservation extends Model
{
    protected $fillable = [
    'student_id',
    'menu_id',
    'reservation_date',
    'status',
    'amount_paid',
    'paid',
    'consumed_at'
];

    protected $casts = [
    'reservation_date' => 'date',
    'amount_paid' => 'decimal:2',
    'paid' => 'boolean',
    'consumed_at' => 'datetime'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function menu()
    {
        return $this->belongsTo(CanteenMenu::class);
    }
}
