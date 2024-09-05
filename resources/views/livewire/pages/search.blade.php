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
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $result)
                    <tr :key="$result->tvdb_id">
                        <td>{{ $result['name'] }}</td>
                        <td>{{ $result['year'] ?? '' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @elseif($query === '')
            Please search for a show above
        @else
            Your search returned no results
        @endif
    </p>

</div>
