<?php

namespace App\Modules\Order\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class OrderPaymentFailException extends ApplicationException
{
    protected $message = 'Falha no pagamento da compra.';

    protected int $statusCode = 402;

    public function __construct(array $context = [])
    {
        parent::__construct($this->message, $this->statusCode, $context);
    }
}
