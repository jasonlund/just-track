<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Http::fake([
            'https://api4.thetvdb.com/v4/login' =>
                Http::response(json_encode([
                    'data' => [
                        'token' => 'foobar'
                    ]
                ])),
            'https://api4.thetvdb.com/v4/search?q=foobar&type=series&limit=20' =>
                Http::response(json_encode([
                    'data' => []
                ])),
            'https://api4.thetvdb.com/v4/search?q=Doc&type=series&limit=20' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TVDB/search-doc.json'))),
            'https://api4.thetvdb.com/v4/series/78804/episodes/official' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TVDB/series-78804.json'))),
            'https://api4.thetvdb.com/v4/series/12345/episodes/official' =>
                Http::response(json_encode([
                    'data' => []
                ]), 404),
        ]);
    }
}
