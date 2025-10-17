<?php

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class ResourceNotFoundException extends Exception implements ShouldntReport
{
    const DEFAULT_STATUS_CODE = 404;

    public array $errors;

    public function __construct(array $errors = [], string $message = 'Os dados fornecidos são inválidos.', int $code = self::DEFAULT_STATUS_CODE)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->code ?? self::DEFAULT_STATUS_CODE);
    }
}
