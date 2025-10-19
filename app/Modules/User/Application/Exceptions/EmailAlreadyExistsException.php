<?php

namespace App\Modules\User\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class EmailAlreadyExistsException extends ApplicationException
{
    protected $message = 'Os dados fornecidos são inválidos.';

    protected int $statusCode = 409;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode, ['email' => 'O email informado já está sendo usado.']);
    }
}
