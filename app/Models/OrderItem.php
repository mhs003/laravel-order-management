<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateSubtotal();
        });

        static::saved(function ($item) {
            $item->order->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->order->recalculateTotal();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }
}
