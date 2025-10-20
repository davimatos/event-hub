<?php

namespace Tests\Unit\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\ValidationException;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_create_money_successfully()
    {
        $money = new Money(39.99);

        $this->assertInstanceOf(Money::class, $money);
    }

    public function test_money_cannot_be_negative()
    {
        try {
            new Money(-39.99);
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('*', $context);
            $this->assertEquals('O valor monetário não pode ser negativo.', $context['*']);
        }
    }

    public function test_money_to_string_returns_correct_format()
    {
        $moneyValue = 39.99;

        $money = new Money($moneyValue);

        $this->assertEquals((string) $moneyValue, (string) $money);
    }

    public function test_money_value_returns_correct_float()
    {
        $moneyValue = 39.99;

        $money = new Money($moneyValue);

        $this->assertEquals($moneyValue, $money->value());
    }

    public function test_money_with_zero_value()
    {
        $money = new Money(0);

        $this->assertEquals(0, $money->value());
    }
}
