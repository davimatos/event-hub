<?php

namespace App\Modules\Shared\Infra\Adapters;

use App\Modules\Shared\Domain\Adapters\LogAdapterInterface;
use Illuminate\Support\Facades\Log;

class MonologLogAdapter implements LogAdapterInterface
{
    public function log(string $level, string $message, string $category = 'general', array $context = []): void
    {
        Log::log($level, $this->formatMessage($message, $category, $context));
    }

    private function formatMessage(string $message, string $category, array $context = []): string
    {
        $formattedMessage = sprintf('[%s] %s', strtoupper($category), $message);

        if (! empty($context)) {
            $formattedMessage .= ' '.json_encode($context);
        }

        return $formattedMessage;
    }
}
