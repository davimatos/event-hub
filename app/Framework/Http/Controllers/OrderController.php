<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\Order\CreateOrderRequest;
use App\Modules\Order\Application\UseCases\CreateOrderUseCase;
use App\Modules\Order\Application\UseCases\GetOrderByIdUseCase;
use App\Modules\Order\Application\UseCases\ListAllOrdersUseCase;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderUseCase $createOrderUseCase,
        private readonly ListAllOrdersUseCase $listAllOrdersUseCase,
        private readonly GetOrderByIdUseCase $getOrderByIdUseCase
    ) {}

    /**
     * @OA\Post(
     *     path="/buy-ticket",
     *     tags={"Orders"},
     *     summary="Comprar ingressos",
     *     description="Realiza a compra de ingressos para um evento",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"event_id","quantity","card_number","card_holder_name","card_expiration_date","card_cvv"},
     *             @OA\Property(property="event_id", type="string", example="ulid"),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="discount_coupon", type="string", example="BLACKFRIDAY"),
     *             @OA\Property(property="card_number", type="string", example="1234567890123456"),
     *             @OA\Property(property="card_holder_name", type="string", example="JOAO SILVA"),
     *             @OA\Property(property="card_expiration_date", type="string", example="12/25"),
     *             @OA\Property(property="card_cvv", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="event_id", type="string", example="ulid"),
     *             @OA\Property(property="participant_id", type="string", example="ulid"),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="ticket_price", type="number", example=50.00),
     *             @OA\Property(property="discount", type="number", example=5.00),
     *             @OA\Property(property="total_amount", type="number", example=95.00),
     *             @OA\Property(property="status", type="string", example="CONFIRMED"),
     *             @OA\Property(property="tickets", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ulid"),
     *                     @OA\Property(property="order_id", type="string", example="ulid"),
     *                     @OA\Property(property="event_id", type="string", example="ulid"),
     *                     @OA\Property(property="participant_id", type="string", example="ulid"),
     *                     @OA\Property(property="used_at", type="string", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Dados de validação inválidos"),
     *     @OA\Response(response=400, description="Erro no processamento do pedido")
     * )
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        $result = $this->createOrderUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Listar pedidos do usuário",
     *     description="Retorna todos os pedidos do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ulid"),
     *                     @OA\Property(property="event_id", type="string", example="ulid"),
     *                     @OA\Property(property="participant_id", type="string", example="ulid"),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="ticket_price", type="number", example=50.00),
     *                     @OA\Property(property="discount", type="number", example=5.00),
     *                     @OA\Property(property="total_amount", type="number", example=95.00),
     *                     @OA\Property(property="status", type="string", example="CONFIRMED"),
     *                     @OA\Property(property="tickets", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="string", example="ulid"),
     *                             @OA\Property(property="order_id", type="string", example="ulid"),
     *                             @OA\Property(property="event_id", type="string", example="ulid"),
     *                             @OA\Property(property="participant_id", type="string", example="ulid"),
     *                             @OA\Property(property="used_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *                             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function index(): JsonResponse
    {
        $result = $this->listAllOrdersUseCase->execute();

        return response()->json(['items' => $result], 200);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Buscar pedido por ID",
     *     description="Retorna os detalhes de um pedido específico",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do pedido",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do pedido",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="event_id", type="string", example="ulid"),
     *             @OA\Property(property="participant_id", type="string", example="ulid"),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="ticket_price", type="number", example=50.00),
     *             @OA\Property(property="discount", type="number", example=5.00),
     *             @OA\Property(property="total_amount", type="number", example=95.00),
     *             @OA\Property(property="status", type="string", example="CONFIRMED"),
     *             @OA\Property(property="tickets", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ulid"),
     *                     @OA\Property(property="order_id", type="string", example="ulid"),
     *                     @OA\Property(property="event_id", type="string", example="ulid"),
     *                     @OA\Property(property="participant_id", type="string", example="ulid"),
     *                     @OA\Property(property="used_at", type="string", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=404, description="Pedido não encontrado")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->getOrderByIdUseCase->execute($id);

        return response()->json($result, 200);
    }
}
