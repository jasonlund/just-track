<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TVDBService
{
    private $token;

    private $baseUri = 'https://api4.thetvdb.com/v4/';

    public function __construct()
    {
        // Retrieve and refresh our token if necessary
        $this->refreshToken();
    }

    public function search(?string $term, $limit = 20): array
    {
        return $this->get('search', [
            'q' => $term,
            'type' => 'series',
            'limit' => $limit,
        ], null, false);
    }

    public function episodes(string $series): array
    {
        return $this->get("series/{$series}/episodes/official", [], 'episodes');
    }

    public function get(string $uri, array $params = [], ?string $dataKey = null, $paginate = true): array
    {
        $data = [];
        $next = null;

        do {
            $response = Http::withToken($this->token)
                // If we have a next url, use that instead of our uri.
                ->get($next ?? ($this->baseUri.$uri.'?'.http_build_query($params)));

            $response->throwUnlessStatus(200);

            $response = $response->json();

            // Sometimes our paginated values are inside the data.
            // If we have nested data and this is our first page, we want to store our parent values too.
            if ($dataKey !== null && $next === null) {
                $data = $response['data'];
            } elseif ($dataKey !== null) {
                // If we have nested and this is not the first, we want to merge on the data key.
                $data[$dataKey] = array_merge($data[$dataKey], $response['data'][$dataKey]);
            } else {
                // Otherwise simply merge the data.
                $data = array_merge($data, $response['data']);
            }

            // If we have a next url, continue on.
            $next = $response['links']['next'] ?? null;
        } while ($paginate && $next !== null);

        return $data;
    }

    private function refreshToken(): void
    {
        // If we have a token, and it has not expired, store it.
        if ($token = Cache::get('tvdb-api-token')) {
            if ($token['expires_at'] > now()->timestamp) {
                $this->token = $token['token'];

                return;
            }
        }

        // Otherwise "login" to the API and retrieve a token given our API Key.
        $token = Http::post($this->baseUri.'login', ['apikey' => config('services.tvdb.api_key')]);
        if (! $token->ok()) {
            throw new \Exception('Could not refresh TVDB API Token');
        }
        $this->token = $token->collect('data.token')->first();

        // Store the token in cache to retreice later.
        Cache::put('tvdb-api-token', [
            'token' => $this->token,
            'expires_at' => now()->addDays(29)->timestamp,         // Expires in 30 days.
        ]);
    }
}
