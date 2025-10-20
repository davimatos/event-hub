<?php

namespace App\Modules\Shared\Infra\Exceptions;

use App\Modules\Shared\Infra\Exceptions\Contract\InfrastructureException;

class FailSendNotificationException extends InfrastructureException
{
    protected $message = 'Falha ao enviar notificação.';

    protected int $statusCode = 503;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode);
    }
}
