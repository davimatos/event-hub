<?php

namespace App\Framework\Http\Requests\User;

use App\Modules\User\Application\Dtos\CreateUserInputDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'required'],
            'email' => ['string', 'required'],
            'type' => ['string', 'sometimes'],
            'password' => ['string', 'required', 'confirmed'],
        ];
    }

    public function toDto(): CreateUserInputDto
    {
        return new CreateUserInputDto(
            $this->input('name'),
            $this->input('email'),
            $this->input('type'),
            $this->input('password')
        );
    }
}
