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
                json_decode(file_get_contents(base_path('tests/Fixtures/Http/TVDB/search-doc.json')), 1),
            'https://api4.thetvdb.com/v4/series/78804/episodes/official' =>
                json_decode(file_get_contents(base_path('tests/Fixtures/Http/TVDB/series-78804.json')), 1)
        ]);
    }
}
