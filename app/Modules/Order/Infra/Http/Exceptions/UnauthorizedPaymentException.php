<?php

namespace App\Modules\Order\Infra\Http\Exceptions;

use App\Modules\Shared\Infra\Exceptions\Contract\InfrastructureException;

class UnauthorizedPaymentException extends InfrastructureException
{
    protected $message = 'O pagamento não foi autorizado pela operadora.';

    protected int $statusCode = 402;

    public function __construct(array $context = [])
    {
        parent::__construct($this->message, $this->statusCode, $context);
    }
}
