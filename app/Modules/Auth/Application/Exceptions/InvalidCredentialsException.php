<?php

namespace App\Modules\Auth\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class InvalidCredentialsException extends ApplicationException
{
    protected $message = 'Credenciais inválidas.';

    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode);
    }
}
