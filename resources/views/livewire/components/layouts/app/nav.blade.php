<flux:header container class="border-b border-stone-200 bg-stone-50 dark:border-stone-700 dark:bg-stone-900">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

    <x-nav-brand />

    <flux:navbar class="hidden font-sans lg:flex">
        @foreach ($items as $item)
            <flux:navbar.item
                href="{{ route($item['route']) }}"
                wire:navigate
                :current="request()->routeIs($item['current'] ?? $item['route'])"
            >
                {{ $item['label'] }}
            </flux:navbar.item>
        @endforeach
    </flux:navbar>

    <flux:spacer />

    {{-- Appearance toggle and profile --}}
    <flux:navbar class="me-4">
        <flux:button
            variant="ghost"
            square
            x-data
            x-on:click="
                if ($flux.appearance === 'light') {
                    $flux.appearance = 'dark'
                } else if ($flux.appearance === 'dark') {
                    $flux.appearance = 'system'
                } else {
                    $flux.appearance = 'light'
                }
            "
            aria-label="Toggle appearance mode"
        >
            <flux:icon.sun
                x-show="$flux.appearance === 'light'"
                variant="mini"
                class="text-stone-500 dark:text-white"
            />
            <flux:icon.moon
                x-show="$flux.appearance === 'dark'"
                variant="mini"
                class="text-stone-500 dark:text-white"
            />
            <flux:icon.computer-desktop
                x-show="$flux.appearance === 'system'"
                variant="mini"
                class="text-stone-500 dark:text-white"
            />
        </flux:button>

        <flux:dropdown position="top" align="start">
            <x-layouts.app.nav-avatar profile />

            <flux:menu>
                <flux:menu.item>
                    <x-slot:icon>
                        <x-layouts.app.nav-avatar class="me-2" size="xs" />
                    </x-slot>
                    {{ auth()->user()->name ?? 'User' }}
                </flux:menu.item>

                <flux:menu.separator />

                <flux:menu.item icon="user" href="#">Profile</flux:menu.item>

                <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="logout">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:navbar>

    {{-- Mobile Sidebar --}}
    <flux:sidebar
        stashable
        sticky
        class="border-r border-stone-200 bg-stone-50 lg:hidden dark:border-stone-700 dark:bg-stone-900"
    >
        <flux:sidebar.toggle icon="x-mark" />

        <x-nav-brand class="px-2" />

        <flux:navlist variant="outline">
            @foreach ($items as $item)
                <flux:navlist.item
                    icon="{{ $item['icon'] }}"
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    :current="request()->routeIs($item['current'] ?? $item['route'])"
                >
                    {{ $item['label'] }}
                </flux:navlist.item>
            @endforeach
        </flux:navlist>
    </flux:sidebar>
</flux:header>
