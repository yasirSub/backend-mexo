<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $fillable = [
        'seller_id',
        'pickup_enabled',
        'min_order_amount',
        'shipping_policy',
        'support_email',
        'contact_phone',
        'opening_hours',
        'auto_accept_orders',
        'delivery_radius_km',
    ];

    protected $casts = [
        'pickup_enabled' => 'boolean',
        'auto_accept_orders' => 'boolean',
        'opening_hours' => 'array',
        'min_order_amount' => 'decimal:2',
        'delivery_radius_km' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
