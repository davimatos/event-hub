<?php

namespace App\Modules\Order\Domain\Repositories;

use App\Modules\Order\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function create(Order $order): Order;

    public function getById(string $id): ?Order;

    public function getAll(): array;

    public function getAllByOrganizerOrParticipant(string $id): array;

    public function getCountSoldTicketsByParticipant(string $eventId, string $participantId): int;
}
