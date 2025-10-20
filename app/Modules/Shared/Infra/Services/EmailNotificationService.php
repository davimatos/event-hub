<?php

namespace App\Modules\Shared\Infra\Services;

use App\Modules\Shared\Application\Services\NotificationServiceInterface;
use App\Modules\Shared\Domain\Adapters\LogAdapterInterface;
use App\Modules\Shared\Infra\Exceptions\FailSendNotificationException;

class EmailNotificationService implements NotificationServiceInterface
{
    private const EMAIL_NOTIFICATION_API_URL = 'https://util.devi.tools/api/v1/notify';

    public function __construct(
        private LogAdapterInterface $logAdapter
    ) {}

    public function send(string $recipient, string $message): bool
    {
        $response = $this->makeRequest();

        $this->logAdapter->log(
            'info',
            $response === true ?
                'Email enviado com sucesso!' :
                'Falha no envio do email',
            'notifications',
            ['response' => $response]
        );

        return $response;
    }

    private function makeRequest(): bool
    {
        $curl = curl_init(self::EMAIL_NOTIFICATION_API_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new FailSendNotificationException;
        }

        return $httpCode === 204;
    }
}
