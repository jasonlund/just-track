<div>
    <p>
        {{-- TODO -- when we add caching to the response, do we want throttle instead? --}}
        <input type="text" wire:model.live.debounce="query" />
    </p>

    <p>
        <button wire:click="resetQuery">Reset Search</button>
    </p>

    <h1>Results</h1>
    <p>
        @if(count($results))
            <ul>
                @foreach($results as $result)
                    <li wire:key="{{ $result['tvdb_id'] }}">
                        <a href="{{ route('show.show', $result['tvdb_id'] ) }}" wire:navigate>
                            {{ $result['name'] }} @if($result['year'] !== null) ({{ $result['year'] }}) @endif
                        </a>

                        <a href="{{ route('show.show', [$result['tvdb_id'], 'attach'] ) }}" wire:navigate>
                            Add
                        </a>
                    </li>
                @endforeach
            </ul>
        @elseif($query === '')
            Please search for a show above
        @else
            Your search returned no results
        @endif
    </p>

</div>
