<?php

namespace Database\Factories;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\TicketModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = TicketModel::class;

    public function definition(): array
    {
        return [
            'order_id' => OrderModel::factory(),
            'event_id' => EventModel::factory(),
            'participant_id' => UserModel::factory(),
            'used_at' => null,
        ];
    }

    public function withOrder(string $orderId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
        ]);
    }

    public function withEvent(string $eventId): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $eventId,
        ]);
    }

    public function withParticipant(string $participantId): static
    {
        return $this->state(fn (array $attributes) => [
            'participant_id' => $participantId,
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
        ]);
    }

    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => null,
        ]);
    }
}
