<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class ReadingStat extends Model
{
    protected $fillable = [
    'student_id',
    'books_read',
    'books_borrowed',
    'total_pages',
    'favorite_category',
    'reading_streak',
    'month',
    'year'
];

    protected $casts = [
    'books_read' => 'integer',
    'books_borrowed' => 'integer',
    'total_pages' => 'integer',
    'reading_streak' => 'integer',
    'month' => 'integer',
    'year' => 'integer'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
