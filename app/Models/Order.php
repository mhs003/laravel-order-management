<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        parent::boot();

        static::saved(function ($order) {
            if ($order->items()->exists()) {
                $order->recalculateTotal();
            }
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');

        $this->updateQuietly([
            'total_amount' => $total
        ]);
    }

    public function scopeStatus(Builder $builder, OrderStatus $status): Builder
    {
        return $builder->where('status', $status);
    }

    public function scopeForUser(Builder $builder, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $builder->where('user_id', $userId);
    }

    public function scopeWithCommonRelations(Builder $query): Builder
    {
        return $query->with(['user:id,name,email', 'items']);
    }

    public function scopeRecent(Builder $builder, int $days = 30): Builder
    {
        return $builder->where('created_at', '>=', now()->subDays($days));
    }

    public function belongsToUser(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $this->user_id === $userId;
    }

    public function isTerminal(): bool
    {
        return \in_array($this->status, [OrderStatus::CANCELLED, OrderStatus::COMPLETED], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }
}
