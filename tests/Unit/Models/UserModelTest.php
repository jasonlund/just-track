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

    // Act
    $shows = $user->shows;

    // Assert
    expect($shows)
        ->toBeInstanceOf(Collection::class)
        ->and($shows->first())->toBeInstanceOf(Show::class)
        ->and($shows->count())->toBe(3);
});
