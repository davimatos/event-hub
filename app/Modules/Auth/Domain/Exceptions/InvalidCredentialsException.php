<?php

namespace App\Modules\Auth\Domain\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class InvalidCredentialsException extends Exception implements ShouldntReport
{
    protected $message = 'Credenciais inválidas.';
    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct($this->message, $this->statusCode);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], $this->statusCode);
    }
}
