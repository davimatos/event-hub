<?php

namespace App\Modules\Shared\Application\Services;

interface NotificationServiceInterface
{
    public function send(string $recipient, string $message): bool;
}
