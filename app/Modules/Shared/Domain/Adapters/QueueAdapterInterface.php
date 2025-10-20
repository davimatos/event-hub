<?php

namespace App\Modules\Shared\Domain\Adapters;

interface QueueAdapterInterface
{
    public function dispatch(string $jobClass, array $data = [], ?string $queue = 'default'): void;
}
