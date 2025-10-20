<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\User\CreateUserRequest;
use App\Modules\User\Application\UseCases\CreateUserUseCase;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase
    ) {}

    /**
     * @OA\Post(
     *     path="/public/users",
     *     tags={"Users"},
     *     summary="Criar usuário público (Participante)",
     *     description="Cria um novo usuário participante sem autenticação",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", example="joao@example.com"),
     *             @OA\Property(property="type", type="string", example="PARTICIPANT"),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Dados de validação inválidos")
     * )
     *
     * @OA\Post(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Criar usuário (Admin)",
     *     description="Cria um novo usuário (Participante ou Organizador). Requer autenticação de Admin para criar Organizador",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Maria Santos"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="12345678"),
     *             @OA\Property(property="type", type="string", enum={"PARTICIPANT","ORGANIZER"}, example="ORGANIZER")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="name", type="string", example="Maria Santos"),
     *             @OA\Property(property="email", type="string", example="maria@example.com"),
     *             @OA\Property(property="type", type="string", example="ORGANIZER"),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Não autorizado"),
     *     @OA\Response(response=422, description="Dados de validação inválidos")
     * )
     */
    public function create(CreateUserRequest $request): JsonResponse
    {
        $result = $this->createUserUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }
}
