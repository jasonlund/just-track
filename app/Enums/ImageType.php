<?php

namespace App\Enums;

enum ImageType: string
{
    // Show-level images
    case HD_TV_LOGO = 'hdtvlogo';
    case CLEAR_LOGO = 'clearlogo';
    case HD_CLEAR_ART = 'hdclearart';
    case CLEAR_ART = 'clearart';
    case TV_POSTER = 'tvposter';
    case TV_BANNER = 'tvbanner';
    case TV_THUMB = 'tvthumb';
    case SHOW_BACKGROUND = 'showbackground';
    case CHARACTER_ART = 'characterart';

    // Season-level images
    case SEASON_POSTER = 'seasonposter';
    case SEASON_BANNER = 'seasonbanner';
    case SEASON_THUMB = 'seasonthumb';

    /**
     * Get all image types that belong to shows (as enum instances)
     */
    public static function showTypes(): array
    {
        return [
            self::HD_TV_LOGO,
            self::CLEAR_LOGO,
            self::HD_CLEAR_ART,
            self::CLEAR_ART,
            self::TV_POSTER,
            self::TV_BANNER,
            self::TV_THUMB,
            self::SHOW_BACKGROUND,
            self::CHARACTER_ART,
        ];
    }

    /**
     * Get all image types that belong to seasons (as enum instances)
     */
    public static function seasonTypes(): array
    {
        return [
            self::SEASON_POSTER,
            self::SEASON_BANNER,
            self::SEASON_THUMB,
        ];
    }

    /**
     * Check if this type belongs to shows
     */
    public function isShowType(): bool
    {
        return in_array($this, self::showTypes());
    }

    /**
     * Check if this type belongs to seasons
     */
    public function isSeasonType(): bool
    {
        return in_array($this, self::seasonTypes());
    }
}
