<?php

namespace App\Modules\Order\Application\UseCases;

use App\Modules\Order\Domain\Dtos\OrderOutputDto;
use App\Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use App\Modules\Shared\Domain\Adapters\AuthenticatorAdapterInterface;
use App\Modules\Shared\Domain\Dtos\CollectionOutputDto;

readonly class ListAllOrdersUseCase
{
    public function __construct(
        private AuthenticatorAdapterInterface $authenticator,
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(): CollectionOutputDto
    {
        $authUser = $this->authenticator->getAuthUser();

        $orders = $this->orderRepository->getAllByOrganizerOrParticipant($authUser->id);

        return CollectionOutputDto::fromEntities($orders, OrderOutputDto::class);
    }
}
