<?php

namespace App\Modules\Shared\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class ResourceNotFoundException extends ApplicationException
{
    protected $message = 'Recurso não encontrado.';

    protected int $statusCode = 404;

    public function __construct(array $context = [])
    {
        parent::__construct($this->message, $this->statusCode, $context);
    }
}
