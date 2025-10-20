<?php

namespace App\Modules\Order\Domain\Repositories;

use App\Modules\Order\Domain\Entities\DiscountCoupon;

interface DiscountCouponRepositoryInterface
{
    public function getDiscountPercent(DiscountCoupon $discountCoupon): float;
}
