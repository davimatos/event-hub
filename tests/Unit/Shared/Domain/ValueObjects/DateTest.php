<?php

namespace Tests\Unit\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Date;
use Tests\TestCase;

class DateTest extends TestCase
{
    public function test_create_date_successfully()
    {
        $date = new Date('2025-10-20');

        $this->assertInstanceOf(Date::class, $date);
    }

    public function test_date_with_invalid_format_throws_exception()
    {
        try {
            new Date('20/10/2025');
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('date', $context);
            $this->assertEquals('Data inválida.', $context['date']);
        }
    }

    public function test_date_with_invalid_date_throws_exception()
    {
        try {
            new Date('30/02/2025');
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('date', $context);
            $this->assertEquals('Data inválida.', $context['date']);
        }

        try {
            new Date('99/10/2025');
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('date', $context);
            $this->assertEquals('Data inválida.', $context['date']);
        }
    }

    public function test_date_to_string_returns_correct_format()
    {
        $dateString = '2025-10-20 12:00:00';

        $date = new Date($dateString);

        $this->assertEquals($dateString, (string) $date);
    }

    public function test_date_format_returns_custom_format()
    {
        $dateString = '2025-10-20';

        $date = new Date($dateString);

        $this->assertEquals($dateString.' 00:00:00', (string) $date);
    }

    public function test_date_with_trimmed_spaces()
    {
        $dateString = ' 2025-10-20 12:00:00';

        $date = new Date($dateString);

        $this->assertEquals('2025-10-20 12:00:00', (string) $date);
    }
}
