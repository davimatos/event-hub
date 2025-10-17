<?php

namespace App\Core\Http\Requests\Event;

use App\Modules\Event\Domain\Dtos\CreateEventInputDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['string', 'required'],
            'description' => ['string', 'required'],
            'date' => ['string', 'required'],
            'ticket_price' => ['numeric', 'required'],
            'capacity' => ['numeric', 'required'],
        ];
    }

    public function toDto(): CreateEventInputDto {
        return new CreateEventInputDto(
            $this->input('title'),
            $this->input('description'),
            $this->input('date'),
            $this->input('ticket_price'),
            $this->input('capacity'),
        );
    }
}
