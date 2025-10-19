<?php

namespace App\Modules\Event\Application\Exceptions;

use App\Modules\Shared\Application\Exceptions\Contract\ApplicationException;

class EventCapacityExceededException extends ApplicationException
{
    protected $message = 'Os tickets para este evento esgotaram.';

    protected int $statusCode = 409;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode);
    }
}
