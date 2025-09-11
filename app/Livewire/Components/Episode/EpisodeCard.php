<?php

namespace App\Livewire\Components\Episode;

use App\Models\Episode;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EpisodeCard extends Component
{
    #[Locked]
    public Episode $episode;

    #[Locked]
    public bool $showWatchButton = true;
    
    #[Locked]
    public ?object $background = null;
    
    #[Locked]
    public ?object $logo = null;
    
    #[Locked]
    public ?string $logoType = null;

    public function render()
    {
        return view('livewire.components.episode.episode-card');
    }

    #[Computed]
    public function episodeNumber(): string
    {
        return sprintf('S%02dE%02d', $this->episode->season->number, $this->episode->number);
    }

    #[Computed]
    public function isWatched(): bool
    {
        return Auth::user()->episodes->contains($this->episode->id);
    }

    #[Computed]
    public function showIsAttached(): bool
    {
        return Auth::user()->shows->contains($this->episode->season->show_id);
    }

    public function toggleWatched(): void
    {
        if (! $this->showIsAttached) {
            return;
        }

        if ($this->isWatched) {
            Auth::user()->episodes()->detach($this->episode);
        } else {
            Auth::user()->episodes()->attach($this->episode, [
                'created_at' => now(),
            ]);
        }

        // Refresh the user's episodes relationship and clear computed property cache
        Auth::user()->load('episodes');
        unset($this->isWatched);

        // Dispatch event to notify other components
        $this->dispatch('episode-watched-toggled', episodeId: $this->episode->id);
    }
}
