<?php

namespace App\Modules\Shared\Infra\Jobs;

use App\Modules\Shared\Application\Services\NotificationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $recipient,
        private readonly string $message,
    ) {}

    public function handle(NotificationServiceInterface $notificationService): void
    {
        $notificationService->send($this->recipient, $this->message);
    }
}
