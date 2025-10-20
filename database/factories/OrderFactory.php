<?php

namespace Database\Factories;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\Order\Infra\Persistence\Eloquent\Models\OrderModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = OrderModel::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $ticketPrice = fake()->randomFloat(2, 50, 500);
        $discount = 0;
        $totalAmount = ($quantity * $ticketPrice) - $discount;

        return [
            'event_id' => EventModel::factory(),
            'participant_id' => UserModel::factory(),
            'quantity' => $quantity,
            'ticket_price' => $ticketPrice,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'status' => 'confirmed',
        ];
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

    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $totalAmount = ($quantity * $attributes['ticket_price']) - $attributes['discount'];

            return [
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
            ];
        });
    }

    public function withDiscount(float $discount): static
    {
        return $this->state(function (array $attributes) use ($discount) {
            $totalAmount = ($attributes['quantity'] * $attributes['ticket_price']) - $discount;

            return [
                'discount' => $discount,
                'total_amount' => $totalAmount,
            ];
        });
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
