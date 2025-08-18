<div>
    <span>{{ $episode->season->show->name }}</span>
    -
    <span class="font-mono">{{ $this->episodeNumber }}</span>
    -
    <span>{{ $episode->name }}</span>
    -
    <span>{{ $episode->air_timestamp?->format('M j, Y') ?? 'TBA' }}</span>
    @if ($episode->runtime)
        -
        <span>{{ $episode->runtime }} min</span>
    @endif

    @if ($showWatchButton && $this->showIsAttached)
        <button wire:click="toggleWatched">
            {{ $this->isWatched ? 'Mark as Unwatched' : 'Mark as Watched' }}
        </button>
    @endif
</div>
