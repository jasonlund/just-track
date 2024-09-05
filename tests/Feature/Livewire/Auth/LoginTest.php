<?php

use App\Livewire\Pages\Auth\Login;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it("uses the guest layout", function() {
    get(route('login'))
        ->assertSeeHtml('<h1>Guest Layout!</h1>');
});

it("can authenticate users", function () {
    $user = User::factory()->create();

    $component = Livewire::test(Login::class)
        ->assertPropertyWired('form.email')
        ->assertPropertyWired('form.password')
        ->assertPropertyWired('form.remember')

        ->assertSeeHtml('<button type="submit">Login</button>')

        ->assertMethodWiredToForm('login')

        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

it("can not authenticate invalid credentials", function () {
    $user = User::factory()->create();

    $component = Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});
