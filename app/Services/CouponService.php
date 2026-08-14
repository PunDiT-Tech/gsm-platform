<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function resolve(?string $code, ?Customer $customer, ?Service $service = null): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::active()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon code is invalid or expired.']);
        }

        if ($service && $coupon->service_id && $coupon->service_id !== $service->id) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon cannot be applied to this service.']);
        }

        if ($coupon->isExhausted()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        if ($coupon->per_customer_limit > 0) {
            $used = $coupon->customerUsageCount($customer?->id);

            if ($used >= $coupon->per_customer_limit) {
                throw ValidationException::withMessages(['coupon_code' => 'This coupon has already been used for your account.']);
            }
        }

        return $coupon;
    }

    public function applyDiscount(float $price, ?Coupon $coupon): float
    {
        if (! $coupon || $price <= 0) {
            return $price;
        }

        $discount = $coupon->type === 'PERCENT'
            ? $price * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        $discount = min($discount, $price);

        return max(0, round($price - $discount, 2));
    }

    public function recordUsage(Order $order, ?Coupon $coupon, float $basePrice = 0): void
    {
        if (! $coupon) {
            return;
        }

        $final = $this->applyDiscount($basePrice, $coupon);

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'discount_amount' => round($basePrice - $final, 2),
        ]);

        $coupon->increment('used_count');
    }
}
