<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Fetches the ID correctly whether route('user') is a User model or a scalar ID
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:255'],
            
            'username' => [
                'required', 
                'string', 
                'lowercase',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            
            'password' => [
                'nullable', // Optional on update
                'string', 
                'confirmed',
                Password::default(),
            ],
        ];
    }
}
