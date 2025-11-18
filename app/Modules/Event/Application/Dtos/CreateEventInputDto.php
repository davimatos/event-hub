<?php

namespace App\Modules\Event\Application\Dtos;

readonly class CreateEventInputDto
{
    public function __construct(
        public string $title,
        public string $description,
        public string $date,
        public float $ticketPrice,
        public int $capacity,
    ) {}
}
