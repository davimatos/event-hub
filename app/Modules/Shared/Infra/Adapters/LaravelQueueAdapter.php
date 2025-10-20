<?php

namespace App\Modules\Shared\Infra\Adapters;

use App\Modules\Shared\Domain\Adapters\QueueAdapterInterface;

class LaravelQueueAdapter implements QueueAdapterInterface
{
    public function dispatch(string $jobClass, array $data = [], ?string $queue = null): void
    {
        $job = new $jobClass(...array_values($data));

        if ($queue !== null) {
            $job->onQueue($queue);
        }

        dispatch($job);
    }
}
