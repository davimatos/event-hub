<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\User\CreateUserRequest;
use App\Modules\User\Domain\UseCases\CreateUserUseCase;

class UserController extends Controller
{
    function __construct(
        private CreateUserUseCase $createUserUseCase
    ) {}

    public function create(CreateUserRequest $request)
    {
        $result = $this->createUserUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }
}
