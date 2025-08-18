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

use App\Enums\ImageType;
use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutExceptionHandling;

uses(
    Tests\TestCase::class,
    LazilyRefreshDatabase::class
)->in('Feature', 'Unit');

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

function doctorWhoShowFactory(array $overrides = []): Factory
{
    return Show::factory(array_merge([
        'external_id' => 210,
        'name' => 'Doctor Who',
        'type' => 'scripted',
        'language' => 'english',
        'status' => 'ended',
        'runtime' => null,
        'average_runtime' => 48,
        'premiered' => '2005-03-26',
        'ended' => '2022-10-23',
        'tvdb_id' => 78804,
        'imdb_id' => 'tt0436992',
        'image' => 'https://static.tvmaze.com/uploads/images/original_untouched/488/1220400.jpg',
        'summary' => '<p>Adventures across time and space with the time traveling alien and companions.</p>',
        'external_updated_at' => '2025-01-22T23:42:35.000000Z',
    ], $overrides));
}

function hcf(): void
{
    withoutExceptionHandling();
}

/*
|--------------------------------------------------------------------------
| Datasets
|--------------------------------------------------------------------------
|
| Datasets allow you to define sets of data that can be passed to your
| tests. This helps to avoid code duplication and makes your tests more
| expressive.
|
*/

dataset('showImageTypes', ImageType::showTypes());

dataset('seasonImageTypes', ImageType::seasonTypes());
