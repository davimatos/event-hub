<?php

namespace App\Modules\Order\Domain\Exceptions;

use App\Core\Exceptions\ValidationException;

class TicketsPerOrderLimitExceededException extends ValidationException
{
    protected $message = 'Só é permitido 5 ingressos por compra.';

    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct(errors: ['quantity' => $this->message], code: $this->statusCode);
    }
}
