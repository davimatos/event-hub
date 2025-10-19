<?php

namespace App\Modules\Shared\Domain\Adapters;

interface LogAdapterInterface
{
    public function log(string $level, string $message, string $category = 'general', array $context = []): void;
}
