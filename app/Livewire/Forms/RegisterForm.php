<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Validation\Rules;

class RegisterForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate]
    public string $email = '';

    #[Validate]
    public string $password = '';
    public string $password_confirmation = '';

    public function rules() {
        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
