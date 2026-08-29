<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- light-only, sem @fluxAppearance --}}
    </head>
    <body class="min-h-screen bg-background font-body text-on-surface antialiased">
        @if (session()->has('admin_authed'))
            <header class="border-b border-outline-variant/40 bg-surface">
                <nav class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-edge py-4">
                    <a href="{{ route('painel') }}" wire:navigate class="font-display text-xl text-primary">
                        {{ config('app.name') }}
                    </a>
                    <div class="flex items-center gap-1 text-sm">
                        <a
                            href="{{ route('painel') }}"
                            wire:navigate
                            @class([
                                'rounded px-3 py-1.5 font-medium uppercase tracking-wide transition-colors',
                                'bg-primary text-on-primary' => request()->routeIs('painel'),
                                'text-on-surface-variant hover:text-primary' => ! request()->routeIs('painel'),
                            ])
                        >Painel</a>
                        <a
                            href="{{ route('painel.convidados') }}"
                            wire:navigate
                            @class([
                                'rounded px-3 py-1.5 font-medium uppercase tracking-wide transition-colors',
                                'bg-primary text-on-primary' => request()->routeIs('painel.convidados'),
                                'text-on-surface-variant hover:text-primary' => ! request()->routeIs('painel.convidados'),
                            ])
                        >Convidados</a>
                        <a
                            href="{{ route('painel.sair') }}"
                            class="ml-2 rounded px-3 py-1.5 font-medium uppercase tracking-wide text-on-surface-variant transition-colors hover:text-error"
                        >Sair</a>
                    </div>
                </nav>
            </header>
        @endif

        <main class="mx-auto max-w-5xl px-edge py-8">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
