<?php

namespace App\Modules\Order\Infra\Http\Services;

use App\Modules\Event\Domain\ValueObjects\Money;
use App\Modules\Order\Application\Services\PaymentGatewayServiceInterface;
use App\Modules\Order\Infra\Http\Exceptions\PaymentGatewayException;
use App\Modules\Order\Infra\Http\Exceptions\UnauthorizedPaymentException;

class FakePaymentGatewayService implements PaymentGatewayServiceInterface
{
    private const AUTHORIZATION_API_URL = 'https://util.devi.tools/api/v2/authorize';

    private const TIMEOUT_IN_SECONDS = 30;

    public function authorize(Money $amount): string
    {
        $response = $this->makeRequest();
        $data = $this->parseResponse($response);

        if ($this->isAuthorized($data) === false) {
            throw new UnauthorizedPaymentException;
        }

        return $this->generatePaymentCode();
    }

    private function makeRequest(): string
    {
        $curl = curl_init(self::AUTHORIZATION_API_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT_IN_SECONDS);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            throw new PaymentGatewayException;
        }

        return $response;
    }

    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaymentGatewayException;
        }

        if (! isset($data['status']) || ! isset($data['data'])) {
            throw new PaymentGatewayException;
        }

        return $data;
    }

    private function isAuthorized(array $data): bool
    {
        return $data['status'] === 'success'
            && isset($data['data']['authorization'])
            && $data['data']['authorization'] === true;
    }

    private function generatePaymentCode(): string
    {
        return md5(uniqid(rand(), true));
    }
}
