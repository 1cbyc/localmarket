<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'total_amount',
        'subtotal',
        'total_delivery_fee',
        'total_commission',
        'payment_status',
        'order_status',
        'payment_intent_id',
        'customer_address',
        'customer_lat',
        'customer_lng',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total_delivery_fee' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'customer_lat' => 'decimal:8',
            'customer_lng' => 'decimal:8',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasManyThrough(OrderItem::class, SubOrder::class);
    }

    public function shops(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Shop::class, SubOrder::class, 'order_id', 'id', 'id', 'shop_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->order_status === 'completed';
    }

    public function allSubOrdersDelivered(): bool
    {
        return $this->subOrders()
            ->where('delivery_status', '!=', 'delivered')
            ->doesntExist();
    }

    protected static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$timestamp}-{$random}";
    }
}

