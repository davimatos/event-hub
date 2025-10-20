<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\Auth\LoginRequest;
use App\Modules\Auth\Application\UseCases\LoginUseCase;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/token",
     *     tags={"Auth"},
     *     summary="Autenticar usuário",
     *     description="Realiza login e retorna token de autenticação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcdef123456"),
     *             @OA\Property(property="type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciais inválidas"),
     *     @OA\Response(response=422, description="Dados de validação inválidos")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUseCase->execute($request->toDto());

        return response()->json($result);
    }
}
