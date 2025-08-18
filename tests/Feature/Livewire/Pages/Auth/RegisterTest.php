<?php

use App\Livewire\Pages\Auth\Register;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    get(route('register'))
        ->assertOk()
        ->assertSeeLivewire(Register::class);
});

it('links to the login page', function () {
    get(route('register'))
        ->assertSee('Already have an account?')
        ->assertSee('Sign in')
        ->assertSee(route('login'));
});

it('can register a new user', function () {
    $component = Livewire::test(Register::class)
        ->assertPropertyWired('form.name')
        ->assertPropertyWired('form.email')
        ->assertPropertyWired('form.password')
        ->assertPropertyWired('form.password_confirmation')

        ->assertSee('Create account')

        ->assertMethodWiredToForm('register')

        ->set('form.name', 'Test User')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
