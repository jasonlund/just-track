<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImageService
{
    private FanArtTVService $fanArtTVService;

    public function __construct(FanArtTVService $fanArtTVService)
    {
        $this->fanArtTVService = $fanArtTVService;
    }

    /**
     * Fetch and store images for a show from FanArt.tv
     */
    public function fetchAndStoreShowImages(Show $show): int
    {
        // Skip if show doesn't have a TVDB ID
        if (! $show->tvdb_id) {
            return 0;
        }

        // Fetch images from FanArt.tv
        $fanartData = $this->fanArtTVService->getShowImages($show->tvdb_id);

        if (! $fanartData) {
            return 0;
        }

        $imagesStored = 0;

        // Prefetch all seasons for the show to avoid N+1 queries
        $seasons = $show->seasons;

        // Process all images
        collect($fanartData)
            ->reject(fn ($images, $typeValue) => ! is_array($images) || ! ImageType::tryFrom($typeValue)
            )
            ->each(function ($images, $typeValue) use ($show, $seasons, &$imagesStored) {
                $imageType = ImageType::tryFrom($typeValue);

                collect($images)
                    ->reject(function ($imageData) use ($imageType) {
                        // Skip season images without specific season number
                        return $imageType->isSeasonType() &&
                               (($imageData['season'] ?? null) === null || ($imageData['season'] ?? null) === 'all');
                    })
                    ->each(function ($imageData) use ($show, $seasons, $imageType, &$imagesStored) {
                        // Determine target: show or specific season
                        $target = $show;
                        if ($imageType->isSeasonType()) {
                            $target = $seasons->firstWhere('number', $imageData['season']);
                            if (! $target) {
                                return;
                            }
                        }

                        try {
                            // Extract relative path from FanArtTV URL
                            // Example: https://assets.fanart.tv/fanart/tv/78804/tvposter/doctor-who-12345.jpg
                            // We want: tv/78804/tvposter/doctor-who-12345.jpg
                            $relativePath = $imageData['url'];
                            $baseUrl = FanArtTVService::getAssetBaseUrl();
                            if (str_starts_with($imageData['url'], $baseUrl)) {
                                $relativePath = substr($imageData['url'], strlen($baseUrl));
                            }

                            // Convert empty string language to null
                            $language = $imageData['lang'] ?? null;
                            if ($language === '') {
                                $language = null;
                            }
                            
                            $this->storeImage(
                                $target,
                                $imageType,
                                $imageData['id'],
                                $relativePath,
                                $language,
                                (int) ($imageData['likes'] ?? 0)
                            );
                            $imagesStored++;
                        } catch (\Exception $e) {
                            $context = [
                                'type' => $imageType->value,
                                'url' => $imageData['url'],
                                'error' => $e->getMessage(),
                            ];

                            if ($target instanceof Season) {
                                $context['season_id'] = $target->id;
                                $context['season_number'] = $imageData['season'] ?? null;
                                $context['show_id'] = $target->show_id;
                                Log::error('Failed to store season image', $context);
                            } else {
                                $context['show_id'] = $target->id;
                                Log::error('Failed to store show image', $context);
                            }
                        }
                    });
            });

        return $imagesStored;
    }

    /**
     * Store or update an image
     */
    private function storeImage($imageable, ImageType $type, string $externalId, string $path, ?string $language, int $likes): void
    {
        DB::beginTransaction();
        try {
            Image::updateOrCreate(
                [
                    'imageable_type' => get_class($imageable),
                    'imageable_id' => $imageable->id,
                    'path' => $path,
                ],
                [
                    'type' => $type->value,
                    'external_id' => $externalId,
                    'language' => $language,
                    'likes' => $likes,
                ]
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
