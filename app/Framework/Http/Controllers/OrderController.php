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

    public function create(CreateOrderRequest $request): JsonResponse
    {
        $result = $this->createOrderUseCase->execute($request->toDto());

        return response()->json($result, 201);
    }

    public function index(): JsonResponse
    {
        $result = $this->listAllOrdersUseCase->execute();

        return response()->json($result, 200);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->getOrderByIdUseCase->execute($id);

        return response()->json($result, 200);
    }
}
