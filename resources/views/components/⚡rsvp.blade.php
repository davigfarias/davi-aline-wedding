<?php

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
    public array $foundGuestIds = [];

    #[Locked]
    public ?int $guestId = null;

    public bool $isAttending = false;

    #[Validate('nullable|string|max:1000')]
    public string $familyMessage = '';

    #[Computed]
    public function rsvpOpen(): bool
    {
        $deadline = config('wedding.rsvp_deadline');

        return blank($deadline) || now()->lte(Carbon::parse($deadline)->endOfDay());
    }

    #[Computed]
    public function guest(): ?Guest
    {
        return $this->guestId
            ? Guest::with('family')->find($this->guestId)
            : null;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Guest>
     */
    #[Computed]
    public function matches(): \Illuminate\Support\Collection
    {
        return Guest::whereIn('id', $this->foundGuestIds)
            ->with('family')
            ->orderBy('name')
            ->get();
    }

    public function search(): void
    {
        $this->validateOnly('query');

        $term = Guest::normalize($this->query);

        $guests = Guest::where('name_normalized', 'like', '%'.$term.'%')
            ->orderBy('name')
            ->get();

        $this->foundGuestIds = $guests->pluck('id')->all();

        if ($guests->isEmpty()) {
            $this->step = 'notfound';

            return;
        }

        if ($guests->count() === 1) {
            $this->loadGuest($guests->first()->id);

            return;
        }

        $this->step = 'choose';
    }

    public function choose(int $guestId): void
    {
        $this->loadGuest($guestId);
    }

    protected function loadGuest(int $guestId): void
    {
        abort_unless(in_array($guestId, $this->foundGuestIds, true), 403);

        $guest = Guest::with('family')->findOrFail($guestId);

        $this->guestId = $guest->id;
        $this->isAttending = (bool) $guest->is_attending;
        $this->familyMessage = (string) $guest->family->message;
        $this->step = 'confirm';

        unset($this->guest);
    }

    public function submit(): void
    {
        abort_unless($this->rsvpOpen(), 403);
        abort_unless(in_array($this->guestId, $this->foundGuestIds, true), 403);

        $this->validateOnly('familyMessage');

        $guest = Guest::with('family')->findOrFail($this->guestId);

        $guest->update([
            'is_attending' => $this->isAttending,
            'responded_at' => now(),
        ]);

        $guest->family->update(['message' => filled($this->familyMessage) ? $this->familyMessage : null]);

        unset($this->guest);
        $this->step = 'done';
    }

    public function startOver(): void
    {
        $this->reset('step', 'query', 'foundGuestIds', 'guestId', 'isAttending', 'familyMessage');
        $this->resetValidation();
        unset($this->guest, $this->matches);
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
                        placeholder="Digite seu nome"
                        autofocus
                    />
                    <flux:button type="submit" variant="primary" class="w-full justify-center">Buscar</flux:button>
                </form>
            @endif

            {{-- ===================== ESCOLHA DE PESSOA ===================== --}}
            @if ($step === 'choose')
                <flux:text>Encontramos mais de uma pessoa com esse nome. Qual é você?</flux:text>
                <div class="flex flex-col gap-2">
                    @foreach ($this->matches as $match)
                        <button
                            type="button"
                            wire:key="match-{{ $match->id }}"
                            wire:click="choose({{ $match->id }})"
                            class="rounded-lg border border-outline-variant/60 p-4 text-left transition-colors hover:border-primary hover:bg-primary/5"
                        >
                            <span class="font-medium text-on-surface">{{ $match->name }}</span>
                            <span class="mt-0.5 block text-sm text-on-surface-variant">
                                {{ $match->family->label }}
                            </span>
                        </button>
                    @endforeach
                </div>
                <flux:button variant="ghost" wire:click="startOver" class="self-start">Buscar outro nome</flux:button>
            @endif

            {{-- ===================== CONFIRMAÇÃO ===================== --}}
            @if ($step === 'confirm' && $this->guest)
                <flux:text>
                    <span class="font-medium text-on-surface">{{ $this->guest->name }}</span> —
                    confirme se você vai ao casamento.
                </flux:text>

                @if ($this->rsvpOpen)
                    <form wire:submit="submit" class="flex flex-col gap-4">
                        <flux:checkbox
                            wire:model="isAttending"
                            label="Vou ao casamento"
                        />

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
                        <div class="flex items-center gap-2 text-sm">
                            @if ($this->guest->is_attending)
                                <flux:icon.check class="size-4 text-primary" />
                            @elseif ($this->guest->is_attending === false)
                                <flux:icon.x-mark class="size-4 text-error" />
                            @else
                                <flux:icon.minus class="size-4 text-on-surface-variant" />
                            @endif
                            <span>{{ $this->guest->name }}</span>
                        </div>
                        @if (filled($this->guest->family->message))
                            <p class="mt-2 border-t border-outline-variant/40 pt-2 text-sm text-on-surface-variant">
                                “{{ $this->guest->family->message }}”
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
