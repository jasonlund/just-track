<div>
    <h3>{{ $this->show['name'] }}</h3>

    <ul>
        <li>Premiered: {{ $this->show['premiered'] }}</li>
        <li>Ended: {{ $this->show['ended'] }}</li>
        <li>{!! $this->show['summary'] !!}</li>
    </ul>

    <livewire:components.show.episode-list :show="$this->show" />
</div>
