<?php

namespace Tests\Unit\Order\Domain\Entities;

use App\Modules\Order\Domain\Entities\CreditCard;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use Tests\TestCase;

class CreditCardTest extends TestCase
{
    public function test_create_credit_card_successfully()
    {
        $creditCard = new CreditCard(
            number: '1234567812345678',
            holderName: 'John Doe',
            expirationDate: '12/30',
            cvv: '123',
        );

        $this->assertInstanceOf(CreditCard::class, $creditCard);
    }

    public function test_card_number_must_have_16_digits()
    {
        try {
            new CreditCard(
                number: '12345678123456',
                holderName: 'John Doe',
                expirationDate: '12/30',
                cvv: '123',
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('card_number', $context);
            $this->assertEquals('O número do cartão deve ter 16 dígitos.', $context['card_number']);
        }
    }

    public function test_cvv_must_have_3_digits()
    {
        try {
            new CreditCard(
                number: '1234567812345612',
                holderName: 'John Doe',
                expirationDate: '12/30',
                cvv: '12',
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('card_cvv', $context);
            $this->assertEquals('O CVV deve ter 3 dígitos.', $context['card_cvv']);
        }
    }

    public function test_expiration_date_must_be_in_correct_format()
    {
        try {
            new CreditCard(
                number: '1234567812345612',
                holderName: 'John Doe',
                expirationDate: '99/99',
                cvv: '123',
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('card_expiration_date', $context);
            $this->assertEquals('A data de validade deve estar no formato MM/YY.', $context['card_expiration_date']);
        }
    }

    public function test_expiration_date_cannot_be_in_the_past()
    {
        try {
            new CreditCard(
                number: '1234567812345612',
                holderName: 'John Doe',
                expirationDate: '05/21',
                cvv: '123',
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('card_expiration_date', $context);
            $this->assertEquals('A data de validade do cartão está expirada.', $context['card_expiration_date']);
        }
    }

    public function test_expiration_date_current_month_and_year_is_valid()
    {
        $creditCard = new CreditCard(
            number: '1234567812345678',
            holderName: 'John Doe',
            expirationDate: date('m/y'),
            cvv: '123',
        );

        $this->assertInstanceOf(CreditCard::class, $creditCard);
    }
}

