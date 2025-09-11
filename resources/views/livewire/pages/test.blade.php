<div class="grid gap-6 w-full sm:max-w-xl mx-auto">
    @php 
        $cardIndex = 0; 
        $episodeIndex = 0;
    @endphp
    @foreach($logos as $type => $logo)
        <livewire:components.episode.episode-card 
            :key="'card-' . $cardIndex"
            :episode="$episodes[$episodeIndex] ?? $episodes->first()"
            :background="$backgrounds[$cardIndex] ?? null"
            :logo="$logo"
            :logo-type="$type"
            :show-watch-button="true"
        />
        @php 
            $cardIndex++;
            $episodeIndex++;
            if ($episodeIndex >= $episodes->count()) {
                $episodeIndex = 0;
            }
        @endphp
    @endforeach
</div>