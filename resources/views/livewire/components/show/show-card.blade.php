<div>
    <h1>{{ $show->name }}</h1>

    <ul>
        <li>{{ Str::title($show->type) }}</li>
        <li>Started: {{ $show->premiered?->format('Y') ?? 'N/A' }}</li>
        <li>Status: {{ Str::title($show->status) }}@if($show->ended !== null) ({{ $show->ended->format('Y') }})@endif</li>
        <li>{!! $show->summary !!}</li>
    </ul>
</div>
