<?php

namespace App\Modules\Shared\Domain\Dtos;

readonly class CollectionOutputDto
{
    public function __construct(
        public array $items
    ) {}

    public static function fromEntities(array $entities, string $dtoClass): self
    {
        return new self(
            items: array_map(
                fn ($entity) => $dtoClass::fromEntity($entity),
                $entities
            )
        );
    }
}
