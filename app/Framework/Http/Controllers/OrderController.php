<?php

namespace App\Framework\Http\Controllers;

use App\Framework\Http\Requests\Order\CreateOrderRequest;
use App\Modules\Order\Application\UseCases\CreateOrderUseCase;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderUseCase $createOrderUseCase,
    ) {}

    public function create(CreateOrderRequest $request): JsonResponse
    {
        $result = $this->createOrderUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }
}
