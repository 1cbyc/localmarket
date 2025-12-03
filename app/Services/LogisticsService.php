<?php

namespace App\Services;

use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LogisticsService
{
    /**
     * Calculate delivery fee based on distance between vendor and customer.
     *
     * @param float $vendorLat Vendor latitude
     * @param float $vendorLng Vendor longitude
     * @param float $customerLat Customer latitude
     * @param float $customerLng Customer longitude
     * @param float $baseFee Base delivery fee in currency units
     * @param float $perKmRate Rate per kilometer
     * @return float Calculated delivery fee
     */
    public function calculateDeliveryFee(
        float $vendorLat,
        float $vendorLng,
        float $customerLat,
        float $customerLng,
        float $baseFee = null,
        float $perKmRate = null
    ): float {
        $baseFee = $baseFee ?? (float) config('marketplace.delivery.base_fee', 5.0);
        $perKmRate = $perKmRate ?? (float) config('marketplace.delivery.per_km_rate', 2.0);

        $distance = $this->calculateDistance($vendorLat, $vendorLng, $customerLat, $customerLng);

        return round($baseFee + ($distance * $perKmRate), 2);
    }

    /**
     * Find nearby riders within specified radius.
     *
     * @param float $lat Latitude of the pickup location
     * @param float $lng Longitude of the pickup location
     * @param int $radiusKm Search radius in kilometers
     * @return Collection Collection of available riders
     */
    public function findNearbyRiders(float $lat, float $lng, int $radiusKm = null): Collection
    {
        $radiusKm = $radiusKm ?? (int) config('marketplace.rider_search_radius', 10);
        $earthRadius = 6371; // Earth's radius in kilometers

        return User::where('role', 'rider')
            ->where('is_online', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->selectRaw('users.*, ? * acos(
                cos(radians(?)) *
                cos(radians(lat)) *
                cos(radians(lng) - radians(?)) +
                sin(radians(?)) *
                sin(radians(lat))
            ) AS distance', [$earthRadius, $lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance', 'asc')
            ->get();
    }

    /**
     * Assign the nearest available rider to a sub-order.
     *
     * @param SubOrder $subOrder The sub-order to assign a rider to
     * @param int $radiusKm Maximum radius to search for riders
     * @return User|null The assigned rider or null if none found
     */
    public function assignNearestRider(SubOrder $subOrder, int $radiusKm = null): ?User
    {
        $shop = $subOrder->shop;

        if (!$shop || !$shop->lat || !$shop->lng) {
            return null;
        }

        $radiusKm = $radiusKm ?? (int) config('marketplace.rider_search_radius', 10);

        $nearbyRiders = $this->findNearbyRiders(
            (float) $shop->lat,
            (float) $shop->lng,
            $radiusKm
        );

        foreach ($nearbyRiders as $rider) {
            if ($subOrder->assignRider($rider)) {
                return $rider;
            }
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param float $lat1 Latitude of first point
     * @param float $lng1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lng2 Longitude of second point
     * @return float Distance in kilometers
     */
    protected function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

