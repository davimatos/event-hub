<?php

namespace App\Modules\User\Domain\Exceptions;

use App\Core\Exceptions\ValidationException;

class UnauthorizedUserException extends ValidationException
{
    protected $message = 'Você não tem permissão para realizar essa ação.';
    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct(errors: ['email' => $this->message], code: $this->statusCode);
    }
}
