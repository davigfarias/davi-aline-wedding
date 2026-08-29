<?php

use App\Models\Family;
use App\Models\Guest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Painel')]
class extends Component
{
    public bool $showAll = false;

    /**
     * @return array{attending: int, declined: int, pending: int, total: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'attending' => Guest::where('is_attending', true)->count(),
            'declined' => Guest::where('is_attending', false)->count(),
            'pending' => Guest::whereNull('is_attending')->count(),
            'total' => Guest::count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Family>
     */
    #[Computed]
    public function families(): \Illuminate\Database\Eloquent\Collection
    {
        return Family::query()
            ->with('guests')
            ->withMax('guests as last_response', 'responded_at')
            ->when(! $this->showAll, fn ($q) => $q->whereHas('guests', fn ($g) => $g->whereNotNull('responded_at')))
            ->orderByRaw('last_response is null')
            ->orderByDesc('last_response')
            ->orderBy('label')
            ->get();
    }
};
?>

<div class="flex flex-col gap-8 lg:flex-row">
    {{-- ===================== BIG NUMBER ===================== --}}
    <aside class="lg:sticky lg:top-24 lg:w-64 lg:shrink-0 lg:self-start">
        <div class="rounded-xl border border-outline-variant/50 bg-surface-container-low p-6 text-center">
            <p class="font-display text-6xl text-primary">{{ $this->stats['attending'] }}</p>
            <p class="mt-1 text-xs uppercase tracking-widest text-on-surface-variant">confirmados</p>
            <dl class="mt-4 space-y-1 border-t border-outline-variant/40 pt-4 text-sm text-on-surface-variant">
                <div class="flex justify-between"><dt>não vão</dt><dd>{{ $this->stats['declined'] }}</dd></div>
                <div class="flex justify-between"><dt>pendentes</dt><dd>{{ $this->stats['pending'] }}</dd></div>
                <div class="flex justify-between"><dt>total</dt><dd>{{ $this->stats['total'] }}</dd></div>
            </dl>
        </div>
    </aside>

    {{-- ===================== CARDS ===================== --}}
    <div class="flex-1">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h1 class="font-display text-2xl text-primary sm:text-3xl">Confirmações</h1>
            <flux:switch wire:model.live="showAll" label="Mostrar todos" align="left" />
        </div>

        @forelse ($this->families as $family)
            <div wire:key="fam-{{ $family->id }}" class="mb-4 rounded-xl border border-outline-variant/50 bg-surface p-5">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="font-medium text-on-surface">{{ $family->label }}</h2>
                    @if ($family->last_response)
                        <span class="text-xs text-on-surface-variant/70">
                            {{ \Illuminate\Support\Carbon::parse($family->last_response)->diffForHumans() }}
                        </span>
                    @endif
                </div>

                <ul class="mt-3 space-y-1.5">
                    @foreach ($family->guests as $guest)
                        <li wire:key="g-{{ $guest->id }}" class="flex items-center gap-2 text-sm">
                            @if ($guest->is_attending === true)
                                <flux:icon.check class="size-4 shrink-0 text-primary" />
                            @elseif ($guest->is_attending === false)
                                <flux:icon.x-mark class="size-4 shrink-0 text-error" />
                            @else
                                <flux:icon.clock class="size-4 shrink-0 text-on-surface-variant/60" />
                            @endif
                            <span @class(['text-on-surface-variant/60 line-through' => $guest->is_attending === false])>
                                {{ $guest->name }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                @if (filled($family->message))
                    <p class="mt-3 border-t border-outline-variant/40 pt-3 text-sm italic text-on-surface-variant">
                        “{{ $family->message }}”
                    </p>
                @endif
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-outline-variant bg-surface-container-low p-8 text-center text-on-surface-variant">
                @if ($this->showAll)
                    Nenhuma família cadastrada ainda.
                @else
                    Ninguém confirmou presença ainda.
                @endif
            </p>
        @endforelse
    </div>
</div>
