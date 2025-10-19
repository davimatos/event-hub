<?php

namespace App\Modules\Order\Infra\Http\Exceptions;

use App\Modules\Shared\Infra\Exceptions\Contract\InfrastructureException;

class PaymentGatewayException extends InfrastructureException
{
    protected $message = 'Problema ao conectar com a operadora de pagamento.';

    protected int $statusCode = 503;

    public function __construct(array $context = [])
    {
        parent::__construct($this->message, $this->statusCode, $context);
    }
}
