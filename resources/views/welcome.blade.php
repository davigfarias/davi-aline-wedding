<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')

        {{-- Preview ao compartilhar o link (WhatsApp, Instagram, etc.) --}}
        @php
            $shareTitle = config('app.name').' — 20 de março de 2027';
            $shareDesc = 'Confirme sua presença no casamento de '.config('app.name').'. Cerimônia em Taguatinga e recepção no Lago Sul — Brasília, DF.';
            // troca por uma imagem 1200x630 dedicada se quiser; por ora usa uma das fotos
            $shareImage = asset('img/2.jpeg');
        @endphp
        <meta name="description" content="{{ $shareDesc }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:title" content="{{ $shareTitle }}">
        <meta property="og:description" content="{{ $shareDesc }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:image" content="{{ $shareImage }}">
        <meta property="og:image:secure_url" content="{{ $shareImage }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1280">
        <meta property="og:image:height" content="853">
        <meta property="og:image:alt" content="{{ config('app.name') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $shareTitle }}">
        <meta name="twitter:description" content="{{ $shareDesc }}">
        <meta name="twitter:image" content="{{ $shareImage }}">
    </head>
    <body class="min-h-screen bg-background font-body text-on-background antialiased">
        {{-- ================= NAV ================= --}}
        <nav
            x-data="{ open: false }"
            @keyup.escape.window="open = false"
            class="fixed inset-x-0 top-0 z-50 border-b border-outline-variant/30 bg-surface/90 backdrop-blur-md"
        >
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-edge sm:h-20">
                <a href="#topo" class="font-display text-lg text-primary sm:text-xl">{{ config('app.name') }}</a>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#historia" class="text-xs uppercase tracking-widest text-on-surface-variant transition-colors hover:text-primary">Nossa História</a>
                    <a href="#evento" class="text-xs uppercase tracking-widest text-on-surface-variant transition-colors hover:text-primary">O Evento</a>
                    <a href="#local" class="text-xs uppercase tracking-widest text-on-surface-variant transition-colors hover:text-primary">Localização</a>
                    <a href="#presentes" class="text-xs uppercase tracking-widest text-on-surface-variant transition-colors hover:text-primary">Presentes</a>
                    <flux:modal.trigger name="rsvp">
                        <button class="rounded bg-primary px-5 py-2.5 text-xs font-medium uppercase tracking-widest text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container">
                            Confirmar Presença
                        </button>
                    </flux:modal.trigger>
                </div>

                <button
                    type="button"
                    class="-mr-2 p-2 text-on-surface md:hidden"
                    aria-label="Abrir menu"
                    :aria-expanded="open"
                    @click="open = ! open"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path x-show="! open" stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        <path x-show="open" x-cloak stroke-linecap="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                x-show="open"
                x-cloak
                x-transition.origin.top
                @click.outside="open = false"
                class="flex flex-col gap-1 border-t border-outline-variant/30 bg-surface px-edge py-4 md:hidden"
            >
                <a href="#historia" @click="open = false" class="py-2 text-sm uppercase tracking-widest text-on-surface-variant">Nossa História</a>
                <a href="#evento" @click="open = false" class="py-2 text-sm uppercase tracking-widest text-on-surface-variant">O Evento</a>
                <a href="#local" @click="open = false" class="py-2 text-sm uppercase tracking-widest text-on-surface-variant">Localização</a>
                <a href="#presentes" @click="open = false" class="py-2 text-sm uppercase tracking-widest text-on-surface-variant">Presentes</a>
                <flux:modal.trigger name="rsvp">
                    <button @click="open = false" class="mt-2 w-full rounded bg-primary px-5 py-3 text-sm font-medium uppercase tracking-widest text-on-primary">
                        Confirmar Presença
                    </button>
                </flux:modal.trigger>
            </div>
        </nav>

        <main id="topo" class="pt-16 sm:pt-20">
            {{-- ================= HERO ================= --}}
            <section class="relative flex h-[78vh] min-h-[30rem] items-center justify-center overflow-hidden">
                {{-- faixa de fotos rolando na horizontal; o casal adiciona as fotos depois --}}
                <div class="absolute inset-0 flex w-max animate-marquee motion-reduce:animate-none" aria-hidden="true">
                    @foreach (array_merge(range(1, 11), range(1, 11)) as $i)
                        <x-wedding-image
                            :path="'img/' . $i . '.jpeg'"
                            ratio="3 / 4"
                            rounded="rounded-none"
                            width="w-[72vw] sm:w-[42vw] lg:w-[28vw]"
                            class="h-[78vh] min-h-[30rem] shrink-0 opacity-70"
                            alt="Foto do casal"
                        />
                    @endforeach
                </div>
                <div class="absolute inset-0 bg-background/45"></div>

                <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center gap-3 px-edge text-center">
                    <h1 class="font-display text-4xl text-primary sm:text-6xl md:text-display">{{ config('app.name') }}</h1>
                    <div class="h-px w-20 bg-gold"></div>
                    <p class="font-display text-2xl text-on-surface-variant sm:text-3xl">Salvem a Data</p>
                    <p class="mt-1 text-sm uppercase tracking-[0.2em] text-on-surface sm:text-base">20 de março de 2027</p>
                    <flux:modal.trigger name="rsvp">
                        <button class="mt-4 rounded bg-primary px-8 py-3.5 text-xs font-medium uppercase tracking-widest text-on-primary shadow-sm transition-colors hover:bg-primary-container hover:text-white">
                            Confirmar Presença
                        </button>
                    </flux:modal.trigger>
                </div>
            </section>

            {{-- ================= NOSSA HISTÓRIA ================= --}}
            <section id="historia" class="mx-auto max-w-7xl px-edge py-section">
                <div class="grid grid-cols-1 items-center gap-gutter md:grid-cols-2">
                    <div class="order-2 flex flex-col gap-4 text-center md:order-1 md:text-left">
                        <h2 class="font-display text-3xl text-primary sm:text-4xl">Nossa História</h2>
                        <div class="mx-auto h-px w-20 bg-gold md:mx-0"></div>
                        {{-- TODO: texto real da história do casal --}}
                        <p class="text-on-surface-variant">
                            Nossa história começou onde também compartilhávamos um propósito: na igreja, trabalhando juntos em um projeto de acolhimento a imigrantes. Entre uma tradução e outra, fomos conversando, trocando histórias e nos conhecendo melhor. A simpatia cresceu ainda mais quando descobrimos uma coincidência que parecia saída de um livro: nascemos na mesma clínica, na mesma cidade — Campina Grande, na Paraíba — com apenas um ano de diferença. A partir dali, nossa amizade foi se fortalecendo e descobrimos que, além das mesmas origens, compartilhávamos a fé, o amor pelos livros e sonhos semelhantes para a vida e para a família. Depois de alguns meses de amizade, veio o pedido de namoro — e aquilo que começou com conversas entre uma tradução e outra se tornou a história de amor que hoje celebramos.
                        </p>
                    </div>
                    <div class="order-1 md:order-2">
                        <x-wedding-image :path="'img/1.jpeg'" ratio="4 / 3" alt="O casal" class="border border-surface-dim shadow-sm" />
                    </div>
                </div>
            </section>

            {{-- ================= O EVENTO ================= --}}
            <section id="evento" class="bg-surface-container-low py-section">
                <div class="mx-auto max-w-7xl px-edge text-center">
                    <h2 class="font-display text-3xl text-primary sm:text-4xl">O Evento</h2>
                    <div class="mx-auto mt-4 mb-12 h-px w-20 bg-gold"></div>

                    <div class="grid grid-cols-1 gap-gutter md:grid-cols-2">
                        <div class="flex flex-col items-center rounded-xl border border-surface-dim bg-surface p-8 shadow-sm">
                            <flux:icon.building-library class="mb-4 size-9 text-tertiary-container" />
                            <h3 class="font-display text-xl text-on-surface">Cerimônia</h3>
                            {{-- TODO: local e horário reais --}}
                            <p class="mt-2 text-on-surface-variant">Primeira Igreja Presbiteriana de Taguatinga</p>
                            <p class="mt-4 w-full border-t border-outline-variant/30 pt-4 text-xs uppercase tracking-widest text-primary">17:00</p>
                        </div>
                        <div class="flex flex-col items-center rounded-xl border border-surface-dim bg-surface p-8 shadow-sm">
                            <flux:icon.sparkles class="mb-4 size-9 text-tertiary-container" />
                            <h3 class="font-display text-xl text-on-surface">Recepção</h3>
                            {{-- TODO: local e horário reais --}}
                            <p class="mt-2 text-on-surface-variant">Restaurante Mangai - Lago Sul</p>
                            <p class="mt-2 text-sm text-on-surface-variant">O buffet será por adesão, no valor de R$106,90 o kilo.</p>
                            <p class="mt-4 w-full border-t border-outline-variant/30 pt-4 text-xs uppercase tracking-widest text-primary">19:30</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ================= LOCALIZAÇÃO ================= --}}
            @php
                $locais = [
                    [
                        'etapa' => 'Cerimônia',
                        'nome' => 'Primeira Igreja Presbiteriana de Taguatinga',
                        'endereco_linha' => 'QNC 06, Área Especial 18 · Taguatinga, Brasília – DF',
                        'endereco_maps' => 'Primeira Igreja Presbiteriana de Taguatinga, QNC 06 Área Especial 18, Taguatinga, Brasília - DF, 72115-550',
                    ],
                    [
                        'etapa' => 'Recepção',
                        'nome' => 'Restaurante Mangai Lago',
                        'endereco_linha' => 'SCES, Lote 2 · Asa Sul, Brasília – DF',
                        'endereco_maps' => 'Restaurante Mangai Lago, SCE Sul s/n Lote 2, Asa Sul, Brasília - DF, 70200-002',
                    ],
                ];
            @endphp
            <section id="local" class="mx-auto max-w-7xl px-edge py-section">
                <div class="mb-12 text-center">
                    <h2 class="font-display text-3xl text-primary sm:text-4xl">Localização</h2>
                    <div class="mx-auto mt-4 h-px w-20 bg-gold"></div>
                </div>

                <div class="flex flex-col gap-12">
                    @foreach ($locais as $local)
                        <div>
                            <div class="mb-4 text-center">
                                <p class="text-xs uppercase tracking-widest text-primary">{{ $local['etapa'] }}</p>
                                <p class="mt-1 font-display text-xl text-on-surface">{{ $local['nome'] }}</p>
                                <p class="mt-1 text-on-surface-variant">{{ $local['endereco_linha'] }}</p>
                                <a
                                    href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($local['endereco_maps']) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-3 inline-block text-xs font-medium uppercase tracking-widest text-primary underline decoration-gold underline-offset-4 hover:text-primary-container"
                                >
                                    Como chegar
                                </a>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-surface-dim shadow-sm">
                                <iframe
                                    title="Mapa — {{ $local['nome'] }}"
                                    src="https://www.google.com/maps?q={{ urlencode($local['endereco_maps']) }}&z=16&output=embed"
                                    class="block h-[20rem] w-full sm:h-[24rem]"
                                    style="border:0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ================= LISTA DE PRESENTES ================= --}}
            <section id="presentes" class="bg-surface-container-low py-section">
                <div class="mx-auto flex max-w-2xl flex-col items-center px-edge text-center">
                    <flux:icon.gift class="mb-6 size-9 text-tertiary-container" />
                    <h2 class="font-display text-3xl text-primary sm:text-4xl">Lista de Presentes</h2>
                    <div class="mx-auto mt-4 mb-6 h-px w-20 bg-gold"></div>
                    <p class="text-on-surface-variant">
                        Sua presença é o nosso maior presente. Mas, se deseja nos presentear, preparamos
                        uma lista com muito carinho para facilitar a escolha.
                    </p>
                    {{-- TODO: colocar o link da lista de presentes no href --}}
                    <a
                        href="https://lista.havan.com.br/Convidado/ItensListaPresente/957800"
                        target="_blank"
                        rel="noopener"
                        class="mt-8 rounded bg-primary px-8 py-3.5 text-xs font-medium uppercase tracking-widest text-on-primary shadow-sm transition-colors hover:bg-primary-container hover:text-white"
                    >
                        Ver Lista de Presentes
                    </a>
                </div>
            </section>
        </main>

        {{-- ================= FOOTER ================= --}}
        <footer class="border-t border-tertiary-container/20 bg-surface-container-low py-section">
            <div class="mx-auto flex max-w-7xl flex-col items-center gap-3 px-edge text-center">
                <p class="font-display text-2xl text-primary">{{ config('app.name') }}</p>
                <p class="text-sm text-on-surface-variant/70">Com amor, {{ config('app.name') }}</p>
            </div>
        </footer>

        {{-- ================= MODAL RSVP (o componente renderiza o próprio flux:modal name="rsvp") ================= --}}
        <livewire:rsvp />

        @fluxScripts
    </body>
</html>
