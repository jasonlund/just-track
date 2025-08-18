<div class="flex items-center gap-2 text-stone-700 dark:text-stone-300">
    <span>{{ $episode->season->show->name }}</span>
    <span class="text-stone-500">•</span>
    <span class="font-mono">{{ $this->episodeNumber }}</span>
    <span class="text-stone-500">•</span>
    <span>{{ $episode->name }}</span>
    <span class="text-stone-500">•</span>
    <span>{{ $episode->air_timestamp?->format('M j, Y') ?? 'TBA' }}</span>
    @if ($episode->runtime)
        <span class="text-stone-500">•</span>
        <span>{{ $episode->runtime }} min</span>
    @endif

    @if ($showWatchButton && $this->showIsAttached)
        <flux:button wire:click="toggleWatched" size="sm" variant="ghost" class="ml-auto">
            {{ $this->isWatched ? 'Mark as Unwatched' : 'Mark as Watched' }}
        </flux:button>
    @endif
</div>
