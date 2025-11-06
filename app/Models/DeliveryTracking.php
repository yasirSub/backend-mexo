<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    protected $table = 'delivery_tracking';

    protected $fillable = [
        'order_id',
        'status',
        'location',
        'description'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}