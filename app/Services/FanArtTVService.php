<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class FanArtTVService
{
    private $baseUri = 'https://webservice.fanart.tv/v3/';
    
    private static string $assetBaseUrl = 'https://assets.fanart.tv/fanart/';
    
    public static function getAssetBaseUrl(): string
    {
        return self::$assetBaseUrl;
    }

    public function getShowImages(int $tvdbId): ?array
    {
        try {
            return $this->get('tv/'.$tvdbId);
        } catch (RequestException $exception) {
            // If we get a 404, the show doesn't exist in FanArt.tv
            if ($exception->getCode() === 404) {
                return null;
            }

            throw $exception;
        }
    }

    public function get(string $uri, array $params = []): array
    {
        // Add API key to params
        $params['api_key'] = config('services.fanarttv.api_key');

        $response = Http::withQueryParameters($params)
            ->get($this->baseUri.$uri);

        $response->throwUnlessStatus(200);

        return $response->json();
    }
}
