<div>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Air Date</th>
            <th>Runtime</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->episodes as $seasonEpisodes)
            @php
                $season = $seasonEpisodes->first()->season;
            @endphp

            <tr>
                <td colspan="{{ $this->show->attached ? 5 : 4 }}">
                    <strong>{{ $season->name }}</strong>
                </td>
            </tr>
            @foreach($seasonEpisodes as $episode)
                <tr wire:key="episode-{{ $episode->id }}">
                    <td>
                        {{ $episode['number'] }}
                    </td>
                    <td>
                        {{ $episode['name'] }}
                    </td>
                    <td>
                        {{ $episode->air_timestamp ? $episode->air_timestamp->format('Y-m-d H:i:s') : 'TBA' }}
                    </td>
                    <td>
                        {{ $episode['runtime'] }}
                    </td>
                    @if($this->show->attached)
                        <td>
                            <form
                                wire:submit="sync({{ $episode['id'] }})"
                            >
                                <button>{{ $episode->attached ? 'Mark as Unwatched' : 'Mark as Watched' }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
