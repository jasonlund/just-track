<?php

use App\Models\User;

it('redirects guests to login page', function () {
    // Arrange & Act & Assert
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('redirects authenticated users to dashboard', function () {
    // Arrange
    $user = User::factory()->create();
    
    // Act & Assert
    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});