<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutExceptionHandling;

uses(
    Tests\TestCase::class,
    LazilyRefreshDatabase::class
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function asUser(?User $user = null): User
{
    $user = $user ?? User::factory()->create();
    actingAs($user);

    return $user;
}

function uninitDoctorWhoShowFactory(): Factory
{
    return Show::factory([
        'external_id' => 57243,
        'original_name' => 'Doctor Who',
        'name' => null,
        'first_air_date' => null,
        'overview' => null,
        'origin_country' => null,
    ]);
}

function hcf(): void
{
    withoutExceptionHandling();
}
