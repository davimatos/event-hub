<?php

namespace App\Modules\Order\Domain\Repositories;

use App\Modules\Order\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function create(Order $order): Order;
}
