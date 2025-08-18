<div class="space-y-6">
    <div class="space-y-4">
        <flux:input type="text" wire:model.live.debounce="query" placeholder="Search for a show..." />

        <flux:button wire:click="resetQuery" variant="ghost">Reset Search</flux:button>
    </div>

    <div>
        <flux:heading size="lg" class="mb-4">Results</flux:heading>

        @if (count($results))
            <ul class="space-y-2">
                @foreach ($results as $result)
                    <li wire:key="{{ $result['id'] }}" class="flex items-center justify-between">
                        <a
                            href="{{ route('show.show', $result['id']) }}"
                            wire:navigate
                            class="text-stone-900 hover:text-orange-500 dark:text-stone-100"
                        >
                            {{ $result['name'] }}
                            @if ($result['premiered'] !== null)
                                <span class="text-stone-500">({{ $result['premiered'] }})</span>
                            @endif
                        </a>

                        <flux:button
                            href="{{ route('show.show', [$result['id'], 'attach']) }}"
                            wire:navigate
                            size="sm"
                            variant="ghost"
                        >
                            Add
                        </flux:button>
                    </li>
                @endforeach
            </ul>
        @elseif ($query === '')
            <flux:text>Please search for a show above</flux:text>
        @else
            <flux:text>Your search returned no results</flux:text>
        @endif
    </div>
</div>
