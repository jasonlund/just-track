<div>
    <h1>Your Shows</h1>
    <p>
        @if(count($shows))
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
                </thead>
                <tbody>
                @foreach($shows as $show)
                    <tr :key="$result->external_id">
                        <td>{{ $show['name'] }}</td>
                        <td>{{ $show['year'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            foobar
        @endif
    </p>
</div>
