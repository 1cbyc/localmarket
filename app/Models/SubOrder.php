<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'shop_id',
        'rider_id',
        'subtotal',
        'delivery_fee',
        'commission_amount',
        'vendor_amount',
        'delivery_status',
        'picked_up_at',
        'delivered_at',
        'delivery_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'vendor_amount' => 'decimal:2',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markAsPickedUp(): bool
    {
        if ($this->delivery_status !== 'picked_up') {
            $this->update([
                'delivery_status' => 'picked_up',
                'picked_up_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    public function markAsDelivered(): bool
    {
        if ($this->delivery_status !== 'delivered') {
            $this->update([
                'delivery_status' => 'delivered',
                'delivered_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    public function assignRider(User $rider): bool
    {
        if (!$rider->isRider() || !$rider->is_online) {
            return false;
        }

        $this->update([
            'rider_id' => $rider->id,
            'delivery_status' => 'picked_up',
        ]);

        return true;
    }

    public function isPending(): bool
    {
        return $this->delivery_status === 'pending';
    }

    public function isFindingDriver(): bool
    {
        return $this->delivery_status === 'finding_driver';
    }

    public function isPickedUp(): bool
    {
        return $this->delivery_status === 'picked_up';
    }

    public function isDelivered(): bool
    {
        return $this->delivery_status === 'delivered';
    }
}

