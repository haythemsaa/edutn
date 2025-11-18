<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class InvoiceItem extends Model
{
    protected $fillable = [
    'invoice_id',
    'description',
    'quantity',
    'unit_price',
    'amount'
];

    protected $casts = [
    'quantity' => 'integer',
    'unit_price' => 'decimal:2',
    'amount' => 'decimal:2'
];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
