<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name_ar', 'name_fr', 'code', 'description',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }
}