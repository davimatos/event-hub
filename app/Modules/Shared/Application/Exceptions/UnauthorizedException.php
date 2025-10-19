<?php

namespace App\Modules\Shared\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class UnauthorizedException extends ApplicationException
{
    protected $message = 'Operação não autorizada.';

    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode);
    }
}
