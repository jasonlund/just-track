<div>
    @if($show == null)
        @php
            // If show is null, that means the TVDB service couldn't find anything.
            // This is the only way I could find to redirect.
            // I wasn't able to in mount() or render().
            $this->redirect('/404');
        @endphp
    @else

   <h3>{{ $show['name'] }}</h3>

    <ul>
        <li>{{ $show['name'] }}</li>
        <li>{{ $show['first_air_date'] }}</li>
        <li>{{ $show['origin_country'] }}</li>
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

    @endif

</div>
