<?php

namespace App\Core\Http\Requests\User;

use App\Modules\User\Domain\Dtos\CreateUserInputDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'required'],
            'email' => ['string', 'required'],
            'type' => ['string', 'sometimes'],
            'password' => ['string', 'required'],
            'password_confirmation' => ['string', 'required'],
        ];
    }

    public function toDto(): CreateUserInputDto
    {
        return new CreateUserInputDto(
            $this->input('name'),
            $this->input('email'),
            $this->input('type'),
            $this->input('password'),
            $this->input('password_confirmation'),
        );
    }
}
