<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\Event\CreateEventRequest;
use App\Modules\Event\Domain\UseCases\CreateEventUseCase;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    function __construct(
        private readonly CreateEventUseCase $createEventUseCase
    ) {}

    public function create(CreateEventRequest $request): JsonResponse
    {
        $result = $this->createEventUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }
}
