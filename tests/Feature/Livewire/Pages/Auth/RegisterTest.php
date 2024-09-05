<?php

use App\Livewire\Pages\Auth\Register;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    get(route('register'))
        ->assertOk()
        ->assertSeeLivewire(Register::class);
});

it("uses the guest layout", function() {
    get(route('register'))
        ->assertSeeHtml('<h1>Guest Layout!</h1>');
});

it("links to the login page", function() {
    get(route('register'))
        ->assertSee('Login To an Existing Account')
        ->assertSee(route('login'));
});

it("can register a new user", function() {
    $component = Livewire::test(Register::class)
        ->assertPropertyWired('form.name')
        ->assertPropertyWired('form.email')
        ->assertPropertyWired('form.password')
        ->assertPropertyWired('form.password_confirmation')

        ->assertSeeHtml('<button type="submit">Register</button>')

        ->assertMethodWiredToForm('register')

        ->set('form.name', 'Test User')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password')
        ->set('form.password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
