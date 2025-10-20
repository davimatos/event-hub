<?php

namespace App\Modules\Order\Application\Services;

use App\Modules\Order\Domain\Entities\Order;

interface NewOrderNotificationServiceInterface
{
    public function execute(Order $order): void;
}
