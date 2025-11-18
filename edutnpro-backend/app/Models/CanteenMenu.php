<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class CanteenMenu extends Model
{
    protected $fillable = [
    'school_id',
    'menu_date',
    'meal_type',
    'main_dish',
    'main_dish_ar',
    'side_dish',
    'dessert',
    'beverage',
    'allergens',
    'calories',
    'photo',
    'price',
    'is_available'
];

    protected $casts = [
    'menu_date' => 'date',
    'allergens' => 'array',
    'calories' => 'integer',
    'price' => 'decimal:2',
    'is_available' => 'boolean'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function reservations()
    {
        return $this->hasMany(MealReservation::class, 'menu_id');
    }
}
