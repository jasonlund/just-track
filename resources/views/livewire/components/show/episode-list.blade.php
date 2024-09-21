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
        @foreach($this->episodes as $season)
            <tr>
                <td colspan="{{ $this->show->attached ? 5 : 4 }}">
                    <strong>{{ $season->first()->season->name }}</strong>
                </td>
            </tr>
            @foreach($season as $episode)
                <tr wire:key="episode-{{ $episode->id }}">
                    <td>
                        {{ $episode['number'] }}
                    </td>
                    <td>
                        {{ $episode['name'] }}
                    </td>
                    <td>
                        {{ $episode['aired'] }}
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
