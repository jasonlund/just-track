<div>
    @if ($hdtvLogo = $show->mostPopularImage(\App\Enums\ImageType::HD_TV_LOGO))
        <img src="{{ route('images.stream', $hdtvLogo->path) }}" alt="{{ $show->name }}" />
    @else
        <h1>{{ $show->name }}</h1>
    @endif

    <ul>
        <li>{{ Str::title($show->type) }}</li>
        <li>Started: {{ $show->premiered?->format("Y") ?? "N/A" }}</li>
        <li>Status: {{ Str::title($show->status) }}@if ($show->ended !== null)({{ $show->ended->format("Y") }})
        @endif</li>
        <li>{!! $show->summary !!}</li>
    </ul>
</div>
