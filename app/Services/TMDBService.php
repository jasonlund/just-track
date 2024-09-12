<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TMDBService
{
    private $baseUri = 'https://api.themoviedb.org/3/';

    public function search(string $term, $limit = 20) : array
    {
        return $this->get('search/tv', [
            'query' => $term,
        ]);
    }

    public function show(int $id)
    {
        return $this->get('tv/' . $id);
    }

    public function get(string $uri, array $params = []) : array
    {
        $response = Http::withToken(config('services.tmdb.token'))
            ->withQueryParameters($params)
            ->get($this->baseUri . $uri);

        $response->throwUnlessStatus(200);

        $response = $response->json();

        return $response['results'] ?? $response;
    }
}
