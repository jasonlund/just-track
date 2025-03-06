<div>
    <h3>{{ $this->show['name'] }}</h3>

    <ul>
        <li>{{ $this->show['name'] }}</li>
        <li>{{ $this->show['first_air_date'] }}</li>
        <li>{{ $this->show['origin_country'] }}</li>
        <li>{{ $this->show['overview'] }}</li>
    </ul>

    <livewire:components.show.episode-list :show="$this->show" />
</div>
