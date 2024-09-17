<div>
    <h1>Your Shows</h1>
    <p>
        @if(count($this->shows))
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
                </thead>
                <tbody>
                @foreach($this->shows as $show)
                    <tr :key="$result->external_id">
                        <td><a href="{{ route('show.show', $show) }}" wire:navigate>{{ $show['name'] }}</a></td>
                        <td>{{ $show['year'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            no shows!
        @endif
    </p>
</div>
