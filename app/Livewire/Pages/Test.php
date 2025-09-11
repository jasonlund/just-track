<?php

namespace App\Livewire\Pages;

use App\Enums\ImageType;
use App\Models\Episode;
use App\Models\Show;
use Livewire\Component;

class Test extends Component
{
    public function render()
    {
        $theWire = Show::where('name', 'The Wire')->first();
        $topChef = Show::where('name', 'Top Chef')->first();
        $severance = Show::where('name', 'Severance')->first();

        $logos = [];
        $backgrounds = [];

        // Add null entry first for the title card
        $logos['null'] = null;

        // Get The Wire logos
        if ($theWire) {
            $logos['The Wire - HD TV Logo'] = $theWire->mostPopularImage(ImageType::HD_TV_LOGO);
            $logos['The Wire - Clear Logo'] = $theWire->mostPopularImage(ImageType::CLEAR_LOGO);
            $logos['The Wire - TV Thumb'] = $theWire->mostPopularImage(ImageType::TV_THUMB);

            // Get The Wire backgrounds (get all available)
            $wireBackgrounds = $theWire->images()
                ->where('type', ImageType::SHOW_BACKGROUND->value)
                ->where(function ($query) {
                    $query->where('language', 'en')
                          ->orWhereNull('language');
                })
                ->orderBy('likes', 'desc')
                ->get();

            // Assign backgrounds to match logo order
            if ($wireBackgrounds->count() > 0) {
                // Cycle through backgrounds for each Wire logo
                $backgrounds[] = $wireBackgrounds[0 % $wireBackgrounds->count()]; // For null case
                $backgrounds[] = $wireBackgrounds[1 % $wireBackgrounds->count()]; // For Wire HD TV Logo
                $backgrounds[] = $wireBackgrounds[2 % $wireBackgrounds->count()]; // For Wire Clear Logo
                $backgrounds[] = $wireBackgrounds[3 % $wireBackgrounds->count()]; // For Wire TV Thumb
            }
        }

        // Get Top Chef logos and backgrounds
        if ($topChef) {
            $logos['Top Chef - HD TV Logo'] = $topChef->mostPopularImage(ImageType::HD_TV_LOGO);
            $logos['Top Chef - Clear Logo'] = $topChef->mostPopularImage(ImageType::CLEAR_LOGO);
            $logos['Top Chef - TV Thumb'] = $topChef->mostPopularImage(ImageType::TV_THUMB);

            // Get Top Chef backgrounds (only 1 available)
            $topChefBackgrounds = $topChef->images()
                ->where('type', ImageType::SHOW_BACKGROUND->value)
                ->where(function ($query) {
                    $query->where('language', 'en')
                          ->orWhereNull('language');
                })
                ->orderBy('likes', 'desc')
                ->get();

            // Use the single Top Chef background for all Top Chef logos
            if ($topChefBackgrounds->count() > 0) {
                $backgrounds[] = $topChefBackgrounds[0]; // For Top Chef HD TV Logo
                $backgrounds[] = $topChefBackgrounds[0]; // For Top Chef Clear Logo
                $backgrounds[] = $topChefBackgrounds[0]; // For Top Chef TV Thumb
            }
        }

        // Get Severance logos and backgrounds
        if ($severance) {
            $logos['Severance - HD TV Logo'] = $severance->mostPopularImage(ImageType::HD_TV_LOGO);
            $logos['Severance - HD Clear Art'] = $severance->mostPopularImage(ImageType::CLEAR_LOGO);
            $logos['Severance - TV Thumb'] = $severance->mostPopularImage(ImageType::TV_THUMB);

            // Get Severance backgrounds
            $severanceBackgrounds = $severance->images()
                ->where('type', ImageType::SHOW_BACKGROUND->value)
                ->where(function ($query) {
                    $query->where('language', 'en')
                          ->orWhereNull('language');
                })
                ->orderBy('likes', 'desc')
                ->get();

            // Assign backgrounds for Severance logos
            if ($severanceBackgrounds->count() > 0) {
                // Cycle through available backgrounds
                $backgrounds[] = $severanceBackgrounds[0 % $severanceBackgrounds->count()]; // For Severance HD TV Logo
                $backgrounds[] = $severanceBackgrounds[1 % $severanceBackgrounds->count()]; // For Severance HD Clear Art
                $backgrounds[] = $severanceBackgrounds[2 % $severanceBackgrounds->count()]; // For Severance TV Thumb
            }
        }

        // Get sample episodes for testing
        $episodes = [];
        if ($theWire) {
            // Get a few episodes from The Wire
            $episodes = Episode::whereHas('season', function ($query) use ($theWire) {
                $query->where('show_id', $theWire->id);
            })->take(count($logos))->get();
        }
        
        // If not enough episodes, create fake ones
        while ($episodes->count() < count($logos)) {
            $fakeEpisode = new Episode();
            $fakeEpisode->id = 999 + $episodes->count();
            $fakeEpisode->name = 'The Target';
            $fakeEpisode->number = 9;
            $fakeEpisode->summary = 'The opening of the fifth season finds McNulty working as a patrolman and Freamon manipulating a crime scene, killing an investigation.';
            $fakeEpisode->season = (object) [
                'number' => 1,
                'show' => (object) ['name' => 'The Wire'],
                'show_id' => $theWire ? $theWire->id : 1,
            ];
            $episodes->push($fakeEpisode);
        }

        return view('livewire.pages.test', [
            'logos' => $logos,
            'show' => $theWire,
            'backgrounds' => $backgrounds,
            'episodes' => $episodes,
        ]);
    }
}
