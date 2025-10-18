<?php

namespace App\Modules\Order\Domain\Exceptions;

use App\Core\Exceptions\ValidationException;

class TicketsPerEventLimitExceededException extends ValidationException
{
    protected $message = 'O seu limite de tickets para esse evento foi atingido.';

    protected int $statusCode = 403;

    public function __construct(string $message = null)
    {
        parent::__construct(errors: ['quantity' => $message ?? $this->message], code: $this->statusCode);
    }
}
