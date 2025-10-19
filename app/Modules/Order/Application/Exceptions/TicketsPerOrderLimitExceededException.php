<?php

namespace App\Modules\Order\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class TicketsPerOrderLimitExceededException extends ApplicationException
{
    protected $message = 'Os dados fornecidos são inválidos.';

    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode, ['quantity' => 'Só é permitido 5 ingressos por compra.']);
    }
}
