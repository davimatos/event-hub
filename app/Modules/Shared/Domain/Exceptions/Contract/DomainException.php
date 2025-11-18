<?php

namespace App\Modules\Shared\Domain\Exceptions\Contract;

use App\Modules\Shared\Infra\Exceptions\Contract\JsonReportException;

abstract class DomainException extends JsonReportException
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
