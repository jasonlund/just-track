<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>#</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Air Date</flux:table.column>
            <flux:table.column>Runtime</flux:table.column>
            @if ($this->show->attached)
                <flux:table.column></flux:table.column>
            @endif
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->episodes as $seasonEpisodes)
                @php
                    $season = $seasonEpisodes->first()->season;
                @endphp

                <flux:table.row>
                    <flux:table.cell colspan="{{ $this->show->attached ? 5 : 4 }}" class="font-semibold">
                        {{ $season->name }}
                    </flux:table.cell>
                </flux:table.row>

                @foreach ($seasonEpisodes as $episode)
                    <flux:table.row wire:key="episode-{{ $episode->id }}">
                        <flux:table.cell>
                            {{ $episode['number'] }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $episode['name'] }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $episode->air_timestamp ? $episode->air_timestamp->format('Y-m-d H:i:s') : 'TBA' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $episode['runtime'] }}
                        </flux:table.cell>
                        @if ($this->show->attached)
                            <flux:table.cell>
                                <flux:button wire:click="sync({{ $episode['id'] }})" size="sm" variant="ghost">
                                    {{ $episode->attached ? 'Mark as Unwatched' : 'Mark as Watched' }}
                                </flux:button>
                            </flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
