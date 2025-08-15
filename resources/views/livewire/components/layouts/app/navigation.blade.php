<div>
    <div style="width: 100%; display: flex; justify-content: space-between">
        <nav>
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            <a href="{{ route('dashboard.upcoming') }}" wire:navigate>Upcoming</a>
            <a href="{{ route('dashboard.previously-aired') }}" wire:navigate>Previously Aired</a>
            <a href="{{ route('search') }}" wire:navigate>Search</a>
            <a href="{{ route('show.index') }}" wire:navigate>Shows</a>
        </nav>
        <div>
            <button wire:click="logout">
                Logout
            </button>
        </div>
    </div>

</div>
