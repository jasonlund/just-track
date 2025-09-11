<div class="w-full">
    <flux:card class="w-full aspect-video relative overflow-hidden rounded-none sm:rounded-lg !border-x-0 !border-y sm:!border-x sm:!border-y !border-orange-500 !bg-black">
        @if($background)
            <img src="{{ route('images.stream', $background->path) }}" alt="Background" class="absolute inset-0 w-full h-full object-cover pointer-events-none opacity-75">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-black to-stone-700 pointer-events-none"></div>
        @endif
        <div class="absolute inset-0 w-full h-full pointer-events-none bg-gradient-radial from-purple-500/10 to-black/30"></div>
        <div class="absolute inset-0 w-full h-full pointer-events-none flicker"></div>
        <div class="absolute inset-0 w-full h-full pointer-events-none scanlines"></div>
        <div class="absolute inset-0 pointer-events-none scanline">
            <div class="h-px w-full bg-white/15 opacity-50 pointer-events-none block"></div>
        </div>

        @if($logo)
            <div class="absolute top-0 left-0 aspect-video w-[20rem] sm:w-[24rem] -translate-x-1/2 -translate-y-1/2 h-auto pointer-events-none rounded-br-md blur-2xl bg-black/50"></div>
            <div class="absolute top-0 left-0 aspect-video mt-2 ml-2 w-[10rem] sm:w-[12rem] pointer-events-none flex items-center justify-start overflow-hidden{{ $logoType && str_contains($logoType, 'TV Thumb') ? ' rounded-md border border-orange-500/50' : '' }}">
                <img
                    src="{{ route('images.stream', $logo->path) }}"
                    alt="{{ $logoType ?? 'Logo' }}"
                    class="{{ $logoType && str_contains($logoType, 'TV Thumb') ? 'w-full h-full object-cover' : 'max-h-full max-w-full object-contain' }}"
                />
            </div>
        @else
            <div class="absolute top-0 left-0 w-1/3 h-1/3 ml-2 mt-2 pointer-events-none z-0 flex items-center justify-center">
                <h2 class="font-serif text-lg sm:text-xl text-white line-clamp-2 text-center">{{ $episode->season->show->name }}</h2>
            </div>
        @endif

        <div class="absolute top-0 right-0 mr-2 mt-2 pointer-events-none py-1 px-2 bg-orange-500 text-stone-50 rounded-sm">
            <span class="font-mono">{{ $this->episodeNumber }}</span>
        </div>

        <div class="absolute bottom-0 left-0 w-full px-2 py-4 bg-gradient-to-t from-black/90 to-transparent flex gap-2 items-end">
            <div class="flex-1 flex flex-col justify-end">
                <h4 class="text-white font-bold font-serif text-xl">{{ $episode->name ?? 'Untitled Episode' }}</h4>
                <p class="text-white/80 text-sm line-clamp-3">{{ $episode->summary ?? 'No description available.' }}</p>
            </div>
            @if($showWatchButton && $this->showIsAttached)
                <button 
                    wire:click="toggleWatched" 
                    class="group flex-shrink-0 w-20 h-20 flex items-center justify-center cursor-pointer ml-2"
                    aria-label="{{ $this->isWatched ? 'Mark as unwatched' : 'Mark as watched' }}"
                >
                    <span class="w-12 h-12 rounded-full {{ $this->isWatched ? 'bg-green-500 group-hover:bg-green-600' : 'bg-orange-500 group-hover:bg-orange-600' }} flex items-center justify-center transition-colors">
                        @if($this->isWatched)
                            <flux:icon.check class="w-6 h-6 text-stone-100" />
                        @else
                            <flux:icon.plus class="w-6 h-6 text-stone-100" />
                        @endif
                    </span>
                </button>
            @endif
        </div>
    </flux:card>
</div>