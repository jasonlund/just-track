<div>
    <p>
        <input type="text" wire:model.live.debounce.150ms="query" />
    </p>


    <p>
        @if(count($this->results))
            <table>
                <thead>
                <tr>
                    <th>Results</th>
                </tr>
                </thead>
                <tbody>
                @foreach($this->results as $result)
                    <tr :key="$result->tvdb_id">
                        <td>{{ $result['name'] }}</td>
                        <td>{{ $result['year'] ?? '' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @elseif($this->query === '')
            Please search for a show above
        @else
            Your search returned no results
        @endif
    </p>

</div>
