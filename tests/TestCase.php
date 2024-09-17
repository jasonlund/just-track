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
            // TVDB
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

            // TMDB
            'https://api.themoviedb.org/3/search/tv?query=Doc' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/search-doc.json'))),
            'https://api.themoviedb.org/3/search/tv?query=foobar' =>
                Http::response(json_encode([
                    "page" => 1,
                    "results" => [],
                    "total_pages" => 1,
                    "total_results" => 0,
                ])),

            'https://api.themoviedb.org/3/tv/57243' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/0' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-0.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/1' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-1.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/2' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-2.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/3' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-3.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/4' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-4.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/5' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-5.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/6' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-6.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/7' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-7.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/8' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-8.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/9' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-9.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/10' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-10.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/11' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-11.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/12' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-12.json'))),
            'https://api.themoviedb.org/3/tv/57243/season/13' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv-57243-season-13.json'))),

            'http://files.tmdb.org/p/exports/tv_series_ids_09_16_2024.json.gz' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv_series_ids_09_16_2024.json.gz'))),
            'http://files.tmdb.org/p/exports/tv_series_ids_09_17_2024.json.gz' =>
                Http::response(file_get_contents(base_path('tests/Fixtures/Http/TMDB/tv_series_ids_09_17_2024.json.gz')))
        ]);
    }
}
