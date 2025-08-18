@props([
    'profile' => false,
])

@if($profile)
    <flux:profile
        avatar:name="{{ auth()->user()->name ?? 'User' }}"
        avatar:color="orange"
        {{ $attributes->merge(['class' => '']) }}
    />
@else
    <flux:avatar
        name="{{ auth()->user()->name ?? 'User' }}"
        color="orange"
        {{ $attributes->merge(['class' => '']) }}
    />
@endif
