<div>
    <flux:heading size="xl" class="mb-8">Episodes to Watch</flux:heading>

    @if ($this->episodes->isEmpty())
        <div class="py-12 text-center">
            <p class="text-stone-600 dark:text-stone-400">No episodes to watch. You're all caught up!</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach ($this->episodes as $episode)
                <livewire:components.episode.episode-card :episode="$episode" :key="'episode-' . $episode->id" />
            @endforeach
        </div>
    @endif
</div>
