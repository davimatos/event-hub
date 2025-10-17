<?php

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

class UnauthorizedException extends Exception implements ShouldntReport
{
    const DEFAULT_STATUS_CODE = 403;

    public function __construct($message = 'Unauthorized.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], self::DEFAULT_STATUS_CODE);
    }
}
