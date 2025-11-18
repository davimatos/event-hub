<?php

namespace App\Modules\Order\Application\UseCases;

use App\Modules\Order\Application\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Shared\Application\Exceptions\ResourceNotFoundException;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;

readonly class GetOrderByIdUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(string $id): OrderOutputDto
    {
        $order = $this->orderRepository->getById($id);

        if ($order === null) {
            throw new ResourceNotFoundException;
        }

        $authUser = $this->authenticator->getAuthUser();

        if ($order->participant->id !== $authUser->id && $order->event->organizer->id !== $authUser->id) {
            throw new ResourceNotFoundException;
        }

        return OrderOutputDto::fromEntity($order);
    }
}
