<div>
    @foreach ($this->episodes as $key => $date)
        <p wire:key="{{ $key }}">
            @if (\Str::startsWith($key, 'd'))
                {{ \Carbon\Carbon::parse(substr($key, 1))->format('l') }}
            @elseif (\Str::startsWith($key, 'w'))
                Week of {{ \Carbon\Carbon::parse(substr($key, 1))->format('M jS') }}
            @else
                {{ \Carbon\Carbon::parse(substr($key, 1))->format('M') }}
            @endif
        </p>
        @foreach ($date as $episode)
            <livewire:components.episode.episode-card :episode="$episode" :key="$episode->id" />
        @endforeach
    @endforeach
</div>
