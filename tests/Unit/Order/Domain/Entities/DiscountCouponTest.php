<?php

namespace Tests\Unit\Order\Domain\Entities;

use App\Modules\Order\Domain\Entities\DiscountCoupon;
use App\Modules\Shared\Domain\Exceptions\ValidationException;
use Tests\TestCase;

class DiscountCouponTest extends TestCase
{
    public function test_create_discount_coupon_successfully()
    {
        $discountCoupon = new DiscountCoupon(
            code: '10OFF'
        );

        $this->assertInstanceOf(DiscountCoupon::class, $discountCoupon);
    }

    public function test_coupon_code_cannot_be_empty()
    {
        try {
            new DiscountCoupon(
                code: ''
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('discount_coupon', $context);
            $this->assertEquals('O código do cupom não pode estar vazio.', $context['discount_coupon']);
        }
    }

    public function test_coupon_code_must_have_minimum_3_characters()
    {
        try {
            new DiscountCoupon(
                code: 'AB'
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('discount_coupon', $context);
            $this->assertEquals('O código do cupom deve ter no mínimo 3 caracteres.', $context['discount_coupon']);
        }
    }

    public function test_coupon_code_must_have_maximum_50_characters()
    {
        try {
            new DiscountCoupon(
                code: 'ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ'
            );
        } catch (ValidationException $e) {
            $context = $e->getContext();
            $this->assertArrayHasKey('discount_coupon', $context);
            $this->assertEquals('O código do cupom deve ter no máximo 50 caracteres.', $context['discount_coupon']);
        }
    }
}
