<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class LibraryLoan extends Model
{
    protected $fillable = [
    'book_id',
    'student_id',
    'loan_date',
    'due_date',
    'return_date',
    'status',
    'fine_amount',
    'fine_paid',
    'notes'
];

    protected $casts = [
    'loan_date' => 'date',
    'due_date' => 'date',
    'return_date' => 'date',
    'fine_amount' => 'decimal:2',
    'fine_paid' => 'boolean'
];

    public function book()
    {
        return $this->belongsTo(LibraryBook::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
