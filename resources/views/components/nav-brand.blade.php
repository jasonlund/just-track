@props(['class' => ''])

<flux:brand href="{{ route('dashboard') }}" wire:navigate {{ $attributes->merge(['class' => $class]) }}>
    <x-slot:logo>
        <flux:icon.play class="size-7" />
    </x-slot:logo>
    <x-slot:name>
        <span class="font-light">Just</span><span class="font-bold text-orange-500">Track</span>
    </x-slot:name>
</flux:brand>