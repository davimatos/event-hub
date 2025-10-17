<?php

namespace App\Core\Http\Requests\Order;

use App\Modules\Order\Domain\Dtos\CreateOrderInputDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'event_id' => ['string', 'required'],
            'quantity' => ['numeric', 'required'],
        ];
    }

    public function toDto(): CreateOrderInputDto
    {
        return new CreateOrderInputDto(
            $this->input('event_id'),
            $this->input('quantity'),
        );
    }
}
