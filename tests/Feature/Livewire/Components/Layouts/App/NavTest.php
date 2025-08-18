<?php

use App\Livewire\Components\Layouts\App\Nav;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Nav::class)
        ->assertOk();
});

it('displays all navigation items', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act & Assert
    Livewire::test(Nav::class)
        ->assertSee('Dashboard')
        ->assertSee('Upcoming')
        ->assertSee('Previously Aired')
        ->assertSee('Search')
        ->assertSee('Shows');
});

it('renders navigation items with wire:navigate attribute', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act & Assert - Check that navigation items have wire:navigate for SPA navigation
    $component = Livewire::test(Nav::class);

    // Check that wire:navigate is present for SPA navigation
    $component->assertSeeHtml('wire:navigate');

    // Check that both desktop and mobile navs exist
    $component->assertSeeHtml('data-flux-navbar')  // Desktop nav
        ->assertSeeHtml('data-flux-navlist');  // Mobile nav

    // Verify all navigation routes are present
    $component->assertSeeHtml(route('dashboard'))
        ->assertSeeHtml(route('dashboard.upcoming'))
        ->assertSeeHtml(route('dashboard.previously-aired'))
        ->assertSeeHtml(route('search'))
        ->assertSeeHtml(route('show.index'));
});

it('displays navigation items with correct routes', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act & Assert
    Livewire::test(Nav::class)
        ->assertSeeHtml('href="'.route('dashboard').'"')
        ->assertSeeHtml('href="'.route('dashboard.upcoming').'"')
        ->assertSeeHtml('href="'.route('dashboard.previously-aired').'"')
        ->assertSeeHtml('href="'.route('search').'"')
        ->assertSeeHtml('href="'.route('show.index').'"');
});

it('displays user menu with profile and logout options', function () {
    // Arrange
    $user = User::factory()->create(['name' => 'John Doe']);
    $this->actingAs($user);

    // Act & Assert
    Livewire::test(Nav::class)
        ->assertSee('John Doe')
        ->assertSee('Profile')
        ->assertSee('Logout')
        ->assertMethodWired('logout');
});

it('can log out the authenticated user', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    // Act
    $component = Livewire::test(Nav::class)
        ->assertMethodWired('logout')
        ->assertSee('Logout');

    $component->call('logout');

    // Assert
    $component
        ->assertHasNoErrors()
        ->assertRedirectToRoute('login');

    $this->assertGuest();
});
