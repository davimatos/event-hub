<?php

namespace Database\Factories;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = EventModel::class;

    public function definition(): array
    {
        return [
            'organizer_id' => UserModel::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date' => fake()->dateTimeBetween('now', '+6 months'),
            'ticket_price' => fake()->randomFloat(2, 50, 500),
            'capacity' => fake()->numberBetween(50, 500),
            'remaining_tickets' => function (array $attributes) {
                return $attributes['capacity'];
            },
        ];
    }

    public function withOrganizer(string $organizerId): static
    {
        return $this->state(fn (array $attributes) => [
            'organizer_id' => $organizerId,
        ]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
            'remaining_tickets' => $capacity,
        ]);
    }

    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'remaining_tickets' => 0,
        ]);
    }

    public function withRemainingTickets(int $remaining): static
    {
        return $this->state(fn (array $attributes) => [
            'remaining_tickets' => $remaining,
        ]);
    }
}

