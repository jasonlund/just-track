<?php

namespace App\Http\Controllers;

use App\Services\FanArtTVService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageStreamController extends Controller
{
    public function __invoke(Request $request, string $path)
    {
        if (Storage::disk('art')->exists($path)) {
            return $this->streamImage($path);
        }

        $fanartUrl = FanArtTVService::getAssetBaseUrl().$path;

        if (! Http::head($fanartUrl)->successful()) {
            abort(404);
        }

        $response = Http::get($fanartUrl);

        if (! $response->successful()) {
            abort(404);
        }

        Storage::disk('art')->put($path, $response->body());

        return $this->streamImage($path);
    }

    private function streamImage(string $path)
    {
        return response()->file(
            Storage::disk('art')->path($path),
            [
                'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
            ]
        );
    }
}
