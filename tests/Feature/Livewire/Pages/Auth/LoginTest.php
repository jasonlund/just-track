<?php

use App\Livewire\Pages\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it('links to the registration page', function () {
    get(route('login'))
        ->assertSee('Create an account')
        ->assertSee(route('register'));
});

it('can authenticate users', function () {
    $user = User::factory()->create();

    $component = Livewire::test(Login::class)
        ->assertPropertyWired('form.email')
        ->assertPropertyWired('form.password')
        ->assertPropertyWired('form.remember')

        ->assertSee('Sign in')

        ->assertMethodWiredToForm('login')

        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirectToRoute('dashboard');

    $this->assertAuthenticated();
});

it('can not authenticate invalid credentials', function () {
    $user = User::factory()->create();

    $component = Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect()
        ->assertSee(trans('auth.failed'));

    $this->assertGuest();
});
