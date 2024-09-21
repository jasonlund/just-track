<div>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Air Date</th>
            <th>Runtime</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->episodes as $season)
            <tr>
                <td colspan="5">
                    <strong>{{ $season->first()->season->name }}</strong>
                </td>
            </tr>
            @foreach($season as $episode)
                <tr>
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
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
