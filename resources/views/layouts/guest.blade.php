<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $title ?? 'Page Title' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=work-sans:100,200,300,400,500,600,700,800,900,100i,200i,300i,400i,500i,600i,700i,800i,900i&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.bunny.net/css?family=cormorant-garamond:300,300i,400,400i,500,500i,600,600i,700,700i&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.bunny.net/css?family=ibm-plex-mono:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i&display=swap"
            rel="stylesheet"
        />

        <!-- Vite Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Flux Appearance (Dark Mode) -->
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-stone-900">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <div class="flex items-center justify-center py-12">
                <div class="w-full max-w-sm space-y-6 px-6">
                    <div class="flex justify-center">
                        <a href="/" wire:navigate class="inline-flex items-center gap-2">
                            <flux:icon.play class="size-8 text-orange-500" />
                            <div class="text-3xl font-serif">
                                <span class="font-light">Just</span><span class="font-bold text-orange-500">Track</span>
                            </div>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>

            <div class="hidden bg-gradient-to-bl from-stone-800 via-stone-900 to-stone-800 lg:block"></div>
        </div>

        @fluxScripts
    </body>
</html>
