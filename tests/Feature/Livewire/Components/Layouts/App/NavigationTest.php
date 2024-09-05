<?php

use App\Livewire\Components\Layouts\App\Navigation;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    Livewire::test(Navigation::class)
        ->assertOk();
});

it("can log out the authenticated user", function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test(Navigation::class)
        ->assertMethodWired('logout')
        ->assertSee('Logout');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirectToRoute('login');

    $this->assertGuest();
});
