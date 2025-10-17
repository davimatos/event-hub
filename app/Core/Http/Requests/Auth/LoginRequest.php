<?php

namespace App\Core\Http\Requests\Auth;

use App\Modules\Auth\Domain\Dtos\LoginInputDto;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['string', 'required'],
            'password' => ['string', 'required'],
        ];
    }

    public function toDto(): LoginInputDto
    {
        return new LoginInputDto(
            $this->input('email'),
            $this->input('password')
        );
    }
}
