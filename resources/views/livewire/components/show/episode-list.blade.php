<div>
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
        @foreach($show->episodes->sortBy('season_id')->groupBy('season_id') as $season => $items)
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
