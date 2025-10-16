<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\Auth\LoginRequest;
use App\Modules\Auth\Domain\UseCases\LoginUseCase;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUseCase->execute($request->toDto());

        return response()->json($result);
    }
}
