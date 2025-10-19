<?php

namespace App\Modules\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use DateTimeImmutable;

final class Date
{
    private DateTimeImmutable $date;

    public function __construct(string $date)
    {
        $trimmedDate = trim($date);

        try {
            $parsedDate = new DateTimeImmutable($trimmedDate);
        } catch (\Exception $e) {
            throw new ValidationException(['date' => 'Data inválida.']);
        }

        $normalizedInput = preg_replace('/[^0-9]/', '', $trimmedDate);
        $normalizedParsed = $parsedDate->format('Ymd');

        if (strlen($normalizedInput) >= 6 && $normalizedInput !== $normalizedParsed) {
            throw new ValidationException(['date' => 'Data inválida.']);
        }

        $this->date = $parsedDate;
    }

    public function __toString(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }
}
