<?php

namespace App\Modules\Order\Infra\Persistence\Memory\Repositories;

use App\Modules\Order\Domain\Entities\DiscountCoupon;
use App\Modules\Order\Domain\Repositories\DiscountCouponRepositoryInterface;

class MemoryDiscountCouponRepository implements DiscountCouponRepositoryInterface
{
    const AVAILABLE_COUPONS = [
        'BLACKFRIDAY' => 0.50,
        '10OFF' => 0.20,
        'PROMO30' => 0.30,
    ];

    public function getDiscountPercent(DiscountCoupon $discountCoupon): float
    {
        return self::AVAILABLE_COUPONS[$discountCoupon->code] ?? 0.0;
    }
}
