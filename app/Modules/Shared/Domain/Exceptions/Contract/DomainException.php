<?php

namespace App\Modules\Shared\Domain\Exceptions\Contract;

use Exception;

abstract class DomainException extends Exception
{
    public function __construct(
        protected $message = '',
        protected $code = 400,
        protected ?array $context = []
    ) {
        parent::__construct($message, $code);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
