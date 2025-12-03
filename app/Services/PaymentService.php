<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SubOrder;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Commission rate as a percentage (e.g., 15.0 for 15%).
     * This should be configurable via config file or database.
     */
    protected float $commissionRate;

    public function __construct()
    {
        $this->commissionRate = (float) config('marketplace.commission_rate', 15.0);
    }

    /**
     * Split payment for an order, calculating vendor amounts and platform commission.
     *
     * @param Order $order The order to process payment split for
     * @return array Summary of the payment split
     */
    public function splitPayment(Order $order): array
    {
        DB::beginTransaction();

        try {
            $subOrders = $order->subOrders;
            $totalCommission = 0.0;
            $totalVendorAmount = 0.0;
            $totalDeliveryFee = 0.0;

            foreach ($subOrders as $subOrder) {
                $subtotal = (float) $subOrder->subtotal;
                $deliveryFee = (float) $subOrder->delivery_fee;

                // Calculate commission on subtotal only (not including delivery fee)
                $commissionAmount = $this->calculateCommission($subtotal);
                $vendorAmount = $subtotal - $commissionAmount;

                $subOrder->update([
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                ]);

                $totalCommission += $commissionAmount;
                $totalVendorAmount += $vendorAmount;
                $totalDeliveryFee += $deliveryFee;
            }

            // Update order totals
            $order->update([
                'total_commission' => $totalCommission,
                'total_delivery_fee' => $totalDeliveryFee,
            ]);

            DB::commit();

            return [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => (float) $order->total_amount,
                'subtotal' => (float) $order->subtotal,
                'total_delivery_fee' => $totalDeliveryFee,
                'total_commission' => $totalCommission,
                'total_vendor_amount' => $totalVendorAmount,
                'sub_orders' => $subOrders->map(function (SubOrder $subOrder) {
                    return [
                        'sub_order_id' => $subOrder->id,
                        'shop_id' => $subOrder->shop_id,
                        'subtotal' => (float) $subOrder->subtotal,
                        'delivery_fee' => (float) $subOrder->delivery_fee,
                        'commission_amount' => (float) $subOrder->commission_amount,
                        'vendor_amount' => (float) $subOrder->vendor_amount,
                    ];
                })->toArray(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate commission amount from subtotal.
     *
     * @param float $subtotal Order subtotal
     * @return float Commission amount
     */
    public function calculateCommission(float $subtotal): float
    {
        return round(($subtotal * $this->commissionRate) / 100, 2);
    }

    /**
     * Get the current commission rate.
     *
     * @return float Commission rate as percentage
     */
    public function getCommissionRate(): float
    {
        return $this->commissionRate;
    }

    /**
     * Set the commission rate.
     *
     * @param float $rate Commission rate as percentage
     * @return self
     */
    public function setCommissionRate(float $rate): self
    {
        $this->commissionRate = $rate;
        return $this;
    }
}

