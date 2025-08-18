<div class="space-y-4">
    @if ($hdtvLogo = $show->mostPopularImage(\App\Enums\ImageType::HD_TV_LOGO))
        <img
            src="{{ route('images.stream', $hdtvLogo->path) }}"
            alt="{{ $show->name }}"
            class="max-h-24 object-contain"
        />
    @else
        <flux:heading size="xl">{{ $show->name }}</flux:heading>
    @endif

    <div class="space-y-2 text-stone-700 dark:text-stone-300">
        <div>
            <flux:text>{{ Str::title($show->type) }}</flux:text>
        </div>
        <div>
            <flux:text>Started: {{ $show->premiered?->format('Y') ?? 'N/A' }}</flux:text>
        </div>
        <div>
            <flux:text>
                Status: {{ Str::title($show->status) }}
                @if ($show->ended !== null)
                    ({{ $show->ended->format('Y') }})
                @endif
            </flux:text>
        </div>
        <div class="prose dark:prose-invert max-w-none">
            {!! $show->summary !!}
        </div>
    </div>
</div>
