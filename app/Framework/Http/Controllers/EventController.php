<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\Event\CreateEventRequest;
use App\Modules\Event\Application\UseCases\CreateEventUseCase;
use App\Modules\Event\Application\UseCases\GetEventByIdUseCase;
use App\Modules\Event\Application\UseCases\ListAllEventsUseCase;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly CreateEventUseCase $createEventUseCase,
        private readonly ListAllEventsUseCase $listAllEventsUseCase,
        private readonly GetEventByIdUseCase $getEventByIdUseCase
    ) {}

    /**
     * @OA\Post(
     *     path="/events",
     *     tags={"Events"},
     *     summary="Criar evento",
     *     description="Cria um novo evento. Requer autenticação de Organizador",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","date","ticket_price","capacity"},
     *             @OA\Property(property="title", type="string", example="Show de Rock"),
     *             @OA\Property(property="description", type="string", example="Um grande show de rock com bandas locais"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="ticket_price", type="number", format="float", example=50.00),
     *             @OA\Property(property="capacity", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Evento criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="organizer", ref="#/components/schemas/User"),
     *             @OA\Property(property="title", type="string", example="Show de Rock"),
     *             @OA\Property(property="description", type="string", example="Um grande show de rock com bandas locais"),
     *             @OA\Property(property="date", type="string", example="2025-12-31"),
     *             @OA\Property(property="ticket_price", type="number", example=50.00),
     *             @OA\Property(property="capacity", type="integer", example=100),
     *             @OA\Property(property="remaining_tickets", type="integer", example=100),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Não autorizado - apenas Organizadores podem criar eventos"),
     *     @OA\Response(response=422, description="Dados de validação inválidos")
     * )
     */
    public function create(CreateEventRequest $request): JsonResponse
    {
        $result = $this->createEventUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/events",
     *     tags={"Events"},
     *     summary="Listar todos os eventos",
     *     description="Retorna uma lista de todos os eventos disponíveis",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de eventos",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ulid"),
     *                     @OA\Property(property="organizer", ref="#/components/schemas/User"),
     *                     @OA\Property(property="title", type="string", example="Show de Rock"),
     *                     @OA\Property(property="description", type="string", example="Um grande show de rock"),
     *                     @OA\Property(property="date", type="string", example="2025-12-31 20:00:00"),
     *                     @OA\Property(property="ticket_price", type="number", example=50.00),
     *                     @OA\Property(property="capacity", type="integer", example=100),
     *                     @OA\Property(property="remaining_tickets", type="integer", example=85),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $result = $this->listAllEventsUseCase->execute();

        return response()->json(['items' => $result], 200);
    }

    /**
     * @OA\Get(
     *     path="/events/{id}",
     *     tags={"Events"},
     *     summary="Buscar evento por ID",
     *     description="Retorna os detalhes de um evento específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do evento",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do evento",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="ulid"),
     *             @OA\Property(property="organizer", ref="#/components/schemas/User"),
     *             @OA\Property(property="title", type="string", example="Show de Rock"),
     *             @OA\Property(property="description", type="string", example="Um grande show de rock"),
     *             @OA\Property(property="date", type="string", example="2025-12-31 20:00:00"),
     *             @OA\Property(property="ticket_price", type="number", example=50.00),
     *             @OA\Property(property="capacity", type="integer", example=100),
     *             @OA\Property(property="remaining_tickets", type="integer", example=85),
     *             @OA\Property(property="created_at", type="string", example="2025-10-20 12:00:00"),
     *             @OA\Property(property="updated_at", type="string", example="2025-10-20 12:00:00")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Evento não encontrado")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $result = $this->getEventByIdUseCase->execute($id);

        return response()->json($result, 200);
    }
}
