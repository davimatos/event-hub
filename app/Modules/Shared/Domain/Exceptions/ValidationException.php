<?php

namespace App\Modules\Shared\Domain\Exceptions;

use App\Modules\Shared\Domain\Exceptions\Contract\DomainException;

class ValidationException extends DomainException
{
    protected $message = 'Os dados fornecidos são inválidos.';

    protected int $statusCode = 422;

    public function __construct(array $context = [])
    {
        parent::__construct($this->message, $this->statusCode, $context);
    }
}
