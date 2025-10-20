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

    public function create(CreateEventRequest $request): JsonResponse
    {
        $result = $this->createEventUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }

    public function index(): JsonResponse
    {
        $result = $this->listAllEventsUseCase->execute();

        return response()->json($result, 200);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->getEventByIdUseCase->execute($id);

        return response()->json($result, 200);
    }
}
