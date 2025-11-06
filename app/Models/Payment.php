<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'seller_id',
        'amount',
        'transaction_id',
        'status',
        'notes',
        'seller_paid',
        'seller_paid_at'
    ];

    protected $casts = [
        'seller_paid' => 'boolean',
        'seller_paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}