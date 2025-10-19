<?php

namespace App\Modules\Order\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class TicketsPerEventLimitExceededException extends ApplicationException
{
    protected $message = 'Os dados fornecidos são inválidos.';

    protected int $statusCode = 403;

    public function __construct(string $contextMessage = 'O seu limite de tickets para esse evento foi atingido.')
    {
        parent::__construct($this->message, $this->statusCode, ['quantity' => $contextMessage]);
    }
}
