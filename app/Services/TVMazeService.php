<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

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
                'page' => $page,
            ]);
        } catch (RequestException $exception) {
            // If we get a 404, the page doesn't exist.
            // Return false to exit the loop calling this.
            if ($exception->getCode() === 404) {
                return false;
            }

            throw $exception;
        }

    }

    public function show(int $id)
    {
        return $this->get('shows/'.$id, [
            'embed' => 'seasons',
        ]);
    }

    public function episodes(int $id)
    {
        return $this->get('shows/'.$id.'/episodes', [
            'specials' => '1',
        ]);
    }

    public function scheduleFull(string $tempFile): bool
    {
        return $this->downloadToFile('schedule/full', $tempFile);
    }

    public function downloadToFile(string $uri, string $tempFile, int $timeout = 300): bool
    {
        $response = Http::withOptions([
            'sink' => $tempFile,
            'timeout' => $timeout,
        ])
            ->get($this->baseUri.$uri);

        return $response->successful();
    }

    public function get(string $uri, array $params = []): array
    {
        $response = Http::withQueryParameters($params)
            ->get($this->baseUri.$uri);

        $response->throwUnlessStatus(200);

        return $response->json();
    }
}
