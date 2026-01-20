<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    protected $hidden = [
        'deleted_at'
    ];

    protected static function boot()
    {
        self::boot();

        static::saved(function ($order) {
            if ($order->items()->exists()) {
                $order->recalculateTotal();
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal()
    {
        $total = $this->items()->sum('subtotal');

        $this->updateQuietly([
            'total_amount' => $total
        ]);
    }

    public function scopeStatus(Builder $builder, OrderStatus $status)
    {
        return $builder->where('status', $status);
    }

    public function scopeForUser(Builder $builder, User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $builder->where('user_id', $userId);
    }

    public function scopeRecent(Builder $builder, int $days = 30)
    {
        return $builder->where('created_at', '>=', now()->subDays($days));
    }

    public function isTerminal()
    {
        return \in_array($this->status, [OrderStatus::CANCELLED, OrderStatus::COMPLETED], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }
}
