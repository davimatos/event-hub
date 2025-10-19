<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\User\CreateUserRequest;
use App\Modules\User\Application\UseCases\CreateUserUseCase;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase
    ) {}

    public function create(CreateUserRequest $request): JsonResponse
    {
        $result = $this->createUserUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }
}
