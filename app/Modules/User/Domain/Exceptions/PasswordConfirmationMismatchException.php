<?php

namespace App\Modules\User\Domain\Exceptions;

use App\Core\Exceptions\ValidationException;

class PasswordConfirmationMismatchException extends ValidationException
{
    protected $message = 'A senha e a confirmação de senha não são iguais.';
    protected int $statusCode = 422;

    public function __construct()
    {
        parent::__construct(errors: ['password_confirmation' => $this->message], code: $this->statusCode);
    }
}
