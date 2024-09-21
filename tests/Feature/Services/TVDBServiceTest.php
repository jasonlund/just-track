<?php

use App\Services\TVDBService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses()->group('tvdb_service');

const PAGINATION_URI = 'https://api4.thetvdb.com/v4/foo';
const PAGINATION_DATA_URI = 'https://api4.thetvdb.com/v4/foobar';
const PAGINATION_NESTED_DATA_URI = 'https://api4.thetvdb.com/v4/foobaz';

beforeEach(function () {
    Http::preventStrayRequests();

    Http::fake([
        PAGINATION_URI => Http::response(json_encode([
            'data' => [],
            'links' => [
                'next' => PAGINATION_URI.'?page=1',
            ],
        ])),

        PAGINATION_URI.'?page=1' => Http::response(json_encode([
            'data' => [],
            'links' => [
                'next' => PAGINATION_URI.'?page=2',
            ],
        ])),

        PAGINATION_URI.'?page=2' => Http::response(json_encode([
            'data' => [],
            'links' => [
                'next' => null,
            ],
        ])),

        PAGINATION_DATA_URI => Http::response(json_encode([
            'data' => [
                ['foo' => 'bar'],
            ],
            'links' => [
                'next' => PAGINATION_DATA_URI.'?page=1',
            ],
        ])),

        PAGINATION_DATA_URI.'?page=1' => Http::response(json_encode([
            'data' => [
                ['bar' => 'baz'],
            ],
            'links' => [
                'next' => null,
            ],
        ])),

        PAGINATION_NESTED_DATA_URI => Http::response(json_encode([
            'data' => [
                'baz' => ['foo'],
                'foo' => [
                    ['foo' => 'bar'],
                ],
            ],
            'links' => [
                'next' => PAGINATION_NESTED_DATA_URI.'?page=1',
            ],
        ])),

        PAGINATION_NESTED_DATA_URI.'?page=1' => Http::response(json_encode([
            'data' => [
                'baz' => ['foo'],
                'foo' => [
                    ['bar' => 'baz'],
                ],
            ],
            'links' => [
                'next' => null,
            ],
        ])),

    ]);
});

it('can refresh token', function () {
    expect(Cache::has('tvdb-api-token'))
        ->toBeFalse();

    new TVDBService;

    expect(Cache::has('tvdb-api-token'))
        ->toBeTrue()

        ->and(Cache::get('tvdb-api-token'))
        ->toBeArray()
        ->toMatchArray([
            'token' => 'foobar',
            'expires_at' => now()->addDays(29)->timestamp,
        ]);
});

it('will refresh an expired token', function () {
    Carbon::setTestNow('2024-08-01 18:18:18');

    $token = [
        'token' => 'foobar',
        'expires_at' => now()->addDays(29)->timestamp,
    ];

    new TVDBService;

    expect(Cache::get('tvdb-api-token'))
        ->toMatchArray($token);

    Carbon::setTestNow('2024-08-28 18:18:18');

    new TVDBService;

    expect(Cache::get('tvdb-api-token'))
        ->toMatchArray($token);

    Carbon::setTestNow('2024-08-30 18:18:18');

    new TVDBService;

    expect(Cache::get('tvdb-api-token'))
        ->not->toMatchArray($token)
        ->toMatchArray([
            'token' => 'foobar',
            'expires_at' => now()->addDays(29)->timestamp,
        ]);
});

it('will paginate a request', function () {
    $service = new TVDBService;

    $service->get(PAGINATION_URI);

    Http::assertSentCount(4);   // This includes refreshing the token.

    Http::assertNotSent(function (Request $request) {
        return $request->url() === PAGINATION_URI.'?page=3';
    });
});

it('will merge paginated data', function () {
    $service = new TVDBService;

    expect($service->get(PAGINATION_DATA_URI))
        ->toMatchArray([
            ['foo' => 'bar'],
            ['bar' => 'baz'],
        ]);
});

it('will merge nested paginated data', function () {
    $service = new TVDBService;

    expect($service->get(PAGINATION_NESTED_DATA_URI, [], 'foo'))
        ->toMatchArray([
            'baz' => ['foo'],
            'foo' => [
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ],
        ]);
});

it('can optionally disable pagination', function () {
    $service = new TVDBService;

    $service->get(PAGINATION_URI, [], null, false);

    Http::assertSentCount(2);   // This includes refreshing the token.
});
