<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'status',
        'shipping_address',
        'phone',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => 'string',
    ];

    protected $appends = ['status_badge'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'cancelled' => 'danger',
            'delivered' => 'info'
        ];
        
        return [
            'text' => ucfirst($this->status),
            'color' => $colors[$this->status] ?? 'secondary'
        ];
    }

    public static function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}