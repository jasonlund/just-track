<?php

use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it('has many shows', function () {
    // Arrange
    $user = User::factory()
        ->has(Show::factory()->count(3))
        ->create();

    // Act & Assert
    expect($user->shows)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Show::class)

        ->and($user->shows->count())
        ->toBe(3);

});
