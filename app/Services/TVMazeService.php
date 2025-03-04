<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class TVMazeService
{
    private $baseUri = 'https://api.tvmaze.com/';

    public function search(string $term): array
    {
        return $this->get('search/shows', [
            'q' => $term,
        ]);
    }

    public function shows(int $page = 0): array|bool
    {
        try {
            return $this->get('shows', [
                'page' =>  $page
            ]);
        }catch(RequestException $exception) {
            // If we get a 404, the page doesn't exist.
            // Return false to exit the loop calling this.
            if($exception->getCode() === 404) return false;

            throw $exception;
        }

    }

    public function show(int $id)
    {
        return $this->get('shows/'.$id, [
            'embed' => 'seasons'
        ]);
    }

    public function episodesBySeason(int $show_id, Collection $seasons)
    {
        return $this->pool($seasons->mapWithKeys(function ($i, $k) use ($show_id) {
            return [$k => 'tv/'.$show_id.'/season/'.$i];
        })->toArray());
    }

    public function get(string $uri, array $params = []): array
    {
        $response = Http::withQueryParameters($params)
            ->withUserAgent('just-track-agent')
            ->get($this->baseUri.$uri);

        $response->throwUnlessStatus(200);

        return $response->json();
    }
}
