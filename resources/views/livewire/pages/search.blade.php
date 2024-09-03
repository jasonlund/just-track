<div>
    <p>
        <input type="text" wire:model.live.debounce.150ms="query" />
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
