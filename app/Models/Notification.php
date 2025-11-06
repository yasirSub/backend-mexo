<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'admin_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the seller that owns the notification
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the admin that owns the notification
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
