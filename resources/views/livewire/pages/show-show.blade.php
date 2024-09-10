<div>
    <h3>{{ $show['name'] }}</h3>

    <ul>
        <li>{{ $show['name'] }}</li>
        <li>{{ $show['year'] }}</li>
        <li>{{ $show['original_country'] }}</li>
        <li>{{ $show['overview'] }}</li>
    </ul>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Abs. #</th>
            <th>Name</th>
            <th>Air Date</th>
            <th>Runtime</th>
        </tr>
        </thead>
        <tbody>
        @foreach($episodes->sortBy('season')->groupBy('season') as $season => $items)
            <tr>
                <td colspan="5">
                    <strong>Season {{ $season }}</strong>
                </td>
            </tr>
            @foreach($items->sortBy('number') as $item)
                <tr>
                    <td>
                        {{ $item['number'] }}
                    </td>
                    <td>
                        {{ $item['absolute_number'] }}
                    </td>
                    <td>
                        {{ $item['name'] }}
                    </td>
                    <td>
                        {{ $item['aired'] }}
                    </td>
                    <td>
                        {{ $item['runtime'] }}
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

</div>
