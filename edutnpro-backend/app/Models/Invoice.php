<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Invoice extends Model
{
    protected $fillable = [
    'school_id',
    'student_id',
    'invoice_number',
    'invoice_date',
    'due_date',
    'amount',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'status',
    'description',
    'notes'
];

    protected $casts = [
    'invoice_date' => 'date',
    'due_date' => 'date',
    'amount' => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'tax_amount' => 'decimal:2',
    'total_amount' => 'decimal:2'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
