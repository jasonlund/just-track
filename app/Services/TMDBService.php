<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TMDBService
{
    private $baseUri = 'https://api.themoviedb.org/3/';

    public function search(string $term, $limit = 20): array
    {
        return $this->get('search/tv', [
            'query' => $term,
        ]);
    }

    public function show(int $id)
    {
        return $this->get('tv/'.$id);
    }

    public function episodesBySeason(int $show_id, Collection $seasons)
    {
        return $this->pool($seasons->mapWithKeys(function ($i, $k) use ($show_id) {
            return [$k => 'tv/'.$show_id.'/season/'.$i];
        })->toArray());
    }

    public function get(string $uri, array $params = []): array
    {
        $response = Http::withToken(config('services.tmdb.token'))
            ->withQueryParameters($params)
            ->get($this->baseUri.$uri);

        $response->throwUnlessStatus(200);

        $response = $response->json();

        return $response['results'] ?? $response;
    }

    public function pool(array $uris): array
    {
        $responses = Http::pool(function (Pool $pool) use ($uris) {
            return collect($uris)->map(function ($uri, $key) use ($pool) {
                return $pool->as($key)
                    ->withToken(config('services.tmdb.token'))
                    ->get($this->baseUri.$uri);
            });
        });

        foreach ($responses as $k => $response) {
            $response->throwUnlessStatus(200);
            $responses[$k] = $response->json();
            $responses[$k] = $responses[$k]['results'] ?? $responses[$k];
        }

        return $responses;
    }
}
