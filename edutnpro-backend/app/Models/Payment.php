<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Payment extends Model
{
    protected $fillable = [
    'school_id',
    'invoice_id',
    'student_id',
    'payment_number',
    'payment_date',
    'amount',
    'payment_method',
    'transaction_id',
    'reference',
    'notes',
    'status',
    'receipt_path',
    'created_by'
];

    protected $casts = [
    'payment_date' => 'date',
    'amount' => 'decimal:2'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
