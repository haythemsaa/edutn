<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceRating extends Model
{
    protected $fillable = ['resource_id', 'student_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function resource(): BelongsTo { return $this->belongsTo(SharedResource::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
