<div class="space-y-6">
    <flux:heading size="xl">Your Shows</flux:heading>

    @if (count($this->shows))
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Title</flux:table.column>
                <flux:table.column>Year</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->shows as $show)
                    <flux:table.row :key="$show->external_id">
                        <flux:table.cell>
                            <a
                                href="{{ route('show.show', $show) }}"
                                wire:navigate
                                class="text-stone-900 hover:text-orange-500 dark:text-stone-100"
                            >
                                {{ $show['name'] }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $show['first_air_date'] }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <flux:text>No shows added yet!</flux:text>
    @endif
</div>
