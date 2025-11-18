<?php

namespace App\Framework\Http\Requests\Order;

use App\Modules\Order\Application\Dtos\CreateOrderInputDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'event_id' => ['string', 'required'],
            'quantity' => ['numeric', 'required'],
            'card_number' => ['string', 'required'],
            'card_holder_name' => ['string', 'required'],
            'card_expiration_date' => ['string', 'required'],
            'card_cvv' => ['string', 'required'],
            'discount_coupon' => ['string', 'nullable'],
        ];
    }

    public function toDto(): CreateOrderInputDto
    {
        return new CreateOrderInputDto(
            $this->input('event_id'),
            $this->input('quantity'),
            $this->input('card_number'),
            $this->input('card_holder_name'),
            $this->input('card_expiration_date'),
            $this->input('card_cvv'),
            $this->input('discount_coupon'),
        );
    }
}
