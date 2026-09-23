<?php

use App\Models\Family;
use App\Models\Guest;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public string $step = 'search';

    #[Validate('required|string|min:2')]
    public string $query = '';

    /** @var array<int> */
    #[Locked]
    public array $foundFamilyIds = [];

    #[Locked]
    public ?int $familyId = null;

    /** @var array<int, bool> */
    public array $attendance = [];

    #[Validate('nullable|string|max:1000')]
    public string $familyMessage = '';

    #[Computed]
    public function rsvpOpen(): bool
    {
        $deadline = config('wedding.rsvp_deadline');

        return blank($deadline) || now()->lte(Carbon::parse($deadline)->endOfDay());
    }

    #[Computed]
    public function family(): ?Family
    {
        return $this->familyId
            ? Family::with('guests')->find($this->familyId)
            : null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Family>
     */
    #[Computed]
    public function matches(): \Illuminate\Support\Collection
    {
        $term = Guest::normalize($this->query);

        return Family::whereIn('id', $this->foundFamilyIds)
            ->with(['guests' => fn ($q) => $q->where('name_normalized', 'like', '%'.$term.'%')])
            ->orderBy('label')
            ->get();
    }

    public function search(): void
    {
        $this->validateOnly('query');

        $term = Guest::normalize($this->query);

        $families = Family::whereHas('guests', fn ($q) => $q->where('name_normalized', 'like', '%'.$term.'%'))
            ->with('guests')
            ->orderBy('label')
            ->get();

        $this->foundFamilyIds = $families->pluck('id')->all();

        if ($families->isEmpty()) {
            $this->step = 'notfound';

            return;
        }

        if ($families->count() === 1) {
            $this->loadFamily($families->first()->id);

            return;
        }

        $this->step = 'choose';
    }

    public function choose(int $familyId): void
    {
        $this->loadFamily($familyId);
    }

    protected function loadFamily(int $familyId): void
    {
        abort_unless(in_array($familyId, $this->foundFamilyIds, true), 403);

        $family = Family::with('guests')->findOrFail($familyId);

        $this->familyId = $family->id;
        $this->attendance = $family->guests->mapWithKeys(
            fn (Guest $guest): array => [$guest->id => (bool) $guest->is_attending],
        )->all();
        $this->familyMessage = (string) $family->message;
        $this->step = 'confirm';

        unset($this->family);
    }

    public function submit(): void
    {
        abort_unless($this->rsvpOpen(), 403);
        abort_unless(in_array($this->familyId, $this->foundFamilyIds, true), 403);

        $this->validateOnly('familyMessage');

        $family = Family::with('guests')->findOrFail($this->familyId);

        foreach ($family->guests as $guest) {
            $guest->update([
                'is_attending' => (bool) ($this->attendance[$guest->id] ?? false),
                'responded_at' => now(),
            ]);
        }

        $family->update(['message' => filled($this->familyMessage) ? $this->familyMessage : null]);

        unset($this->family);
        $this->step = 'done';
    }

    public function startOver(): void
    {
        $this->reset('step', 'query', 'foundFamilyIds', 'familyId', 'attendance', 'familyMessage');
        $this->resetValidation();
        unset($this->family, $this->matches);
    }
};
?>

<div
    x-data
    x-on:modal-show.document="$event.detail?.name === 'rsvp' && $wire.step !== 'search' && $wire.startOver()"
>
    <flux:modal name="rsvp" class="w-full md:max-w-lg">
        <div class="flex flex-col gap-5">
            <div>
                <flux:heading size="lg" class="font-display text-primary">Confirmar presença</flux:heading>
                @unless ($this->rsvpOpen)
                    <flux:subheading class="mt-1 text-error">O prazo para confirmar já encerrou.</flux:subheading>
                @endunless
            </div>

            {{-- ===================== BUSCA ===================== --}}
            @if ($step === 'search')
                <form wire:submit="search" class="flex flex-col gap-4">
                    <flux:input
                        wire:model="query"
                        label="Seu nome"
                        placeholder="Digite o nome de alguém da família"
                        autofocus
                    />
                    <flux:button type="submit" variant="primary" class="w-full justify-center">Buscar</flux:button>
                </form>
            @endif

            {{-- ===================== ESCOLHA DE NÚCLEO ===================== --}}
            @if ($step === 'choose')
                <flux:text>Encontramos mais de uma família. Qual é a sua?</flux:text>
                <div class="flex flex-col gap-2">
                    @foreach ($this->matches as $match)
                        <button
                            type="button"
                            wire:key="match-{{ $match->id }}"
                            wire:click="choose({{ $match->id }})"
                            class="rounded-lg border border-outline-variant/60 p-4 text-left transition-colors hover:border-primary hover:bg-primary/5"
                        >
                            <span class="font-medium text-on-surface">{{ $match->label }}</span>
                            <span class="mt-0.5 block text-sm text-on-surface-variant">
                                {{ $match->guests->pluck('name')->join(', ') }}
                            </span>
                        </button>
                    @endforeach
                </div>
                <flux:button variant="ghost" wire:click="startOver" class="self-start">Buscar outro nome</flux:button>
            @endif

            {{-- ===================== CONFIRMAÇÃO ===================== --}}
            @if ($step === 'confirm' && $this->family)
                <flux:text>
                    <span class="font-medium text-on-surface">{{ $this->family->label }}</span> —
                    marque quem vai ao casamento.
                </flux:text>

                @if ($this->rsvpOpen)
                    <form wire:submit="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3">
                            @foreach ($this->family->guests as $guest)
                                <flux:checkbox
                                    wire:key="guest-{{ $guest->id }}"
                                    wire:model="attendance.{{ $guest->id }}"
                                    label="{{ $guest->name }}"
                                />
                            @endforeach
                        </div>

                        <flux:textarea
                            wire:model="familyMessage"
                            label="Recado (opcional)"
                            rows="3"
                            placeholder="Deixe um recado para os noivos"
                        />

                        <div class="flex gap-2">
                            <flux:button type="submit" variant="primary" class="flex-1 justify-center">Confirmar</flux:button>
                            <flux:button type="button" variant="ghost" wire:click="startOver">Voltar</flux:button>
                        </div>
                    </form>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach ($this->family->guests as $guest)
                            <div wire:key="ro-{{ $guest->id }}" class="flex items-center gap-2 text-sm">
                                @if ($guest->is_attending)
                                    <flux:icon.check class="size-4 text-primary" />
                                @elseif ($guest->is_attending === false)
                                    <flux:icon.x-mark class="size-4 text-error" />
                                @else
                                    <flux:icon.minus class="size-4 text-on-surface-variant" />
                                @endif
                                <span>{{ $guest->name }}</span>
                            </div>
                        @endforeach
                        @if (filled($this->family->message))
                            <p class="mt-2 border-t border-outline-variant/40 pt-2 text-sm text-on-surface-variant">
                                “{{ $this->family->message }}”
                            </p>
                        @endif
                    </div>
                    <flux:button variant="ghost" wire:click="startOver" class="self-start">Buscar outro nome</flux:button>
                @endif
            @endif

            {{-- ===================== CONCLUÍDO ===================== --}}
            @if ($step === 'done')
                <div class="flex flex-col items-center gap-3 py-4 text-center">
                    <flux:icon.check-badge class="size-10 text-primary" />
                    <flux:heading size="lg" class="font-display">Presença registrada!</flux:heading>
                    <flux:text>Obrigado. Você pode voltar e editar até o prazo, se precisar.</flux:text>
                    <div class="mt-2 flex gap-2">
                        @if ($this->rsvpOpen)
                            <flux:button variant="ghost" wire:click="$set('step', 'confirm')">Editar</flux:button>
                        @endif
                        <flux:modal.close>
                            <flux:button variant="primary">Fechar</flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            @endif

            {{-- ===================== NÃO ENCONTRADO ===================== --}}
            @if ($step === 'notfound')
                <div class="flex flex-col gap-3">
                    <flux:text>
                        Não encontramos esse nome na lista. Confira a grafia ou fale com os noivos:
                        <span class="font-medium text-on-surface">{{ config('wedding.contact') }}</span>
                    </flux:text>
                    <flux:button variant="primary" wire:click="startOver" class="self-start">Tentar de novo</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
