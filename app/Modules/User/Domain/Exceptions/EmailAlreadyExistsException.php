<?php

namespace App\Modules\User\Domain\Exceptions;

use App\Core\Exceptions\ValidationException;

class EmailAlreadyExistsException extends ValidationException
{
    protected $message = 'O email informado já está sendo usado.';

    protected int $statusCode = 409;

    public function __construct()
    {
        parent::__construct(errors: ['email' => $this->message], code: $this->statusCode);
    }
}
