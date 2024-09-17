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

    <livewire:components.show.episode-list :$show />

    @endif

</div>
