<?php

use App\Models\Family;
use App\Models\Guest;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Convidados')]
class extends Component
{
    public string $newLabel = '';

    public string $newNames = '';

    public ?int $editingId = null;

    public string $editLabel = '';

    /** @var array<string, array{id: int|null, name: string, phone: string}> */
    public array $editGuests = [];

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Family>
     */
    #[Computed]
    public function families(): \Illuminate\Database\Eloquent\Collection
    {
        return Family::with('guests')->orderBy('label')->get();
    }

    /**
     * @param  string  $block
     * @return list<string>
     */
    protected function parseNames(string $block): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $block) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function addFamily(): void
    {
        $this->validate([
            'newLabel' => ['required', 'string', 'max:255'],
            'newNames' => ['required', 'string'],
        ]);

        $names = $this->parseNames($this->newNames);

        if ($names === []) {
            $this->addError('newNames', 'Adicione ao menos um nome.');

            return;
        }

        $family = Family::create(['label' => $this->newLabel]);

        foreach ($names as $name) {
            $family->guests()->create(['name' => $name]);
        }

        $this->reset('newLabel', 'newNames');
        unset($this->families);

        Flux::toast(text: "Família \"{$family->label}\" adicionada.", variant: 'success');
    }

    public function startEdit(int $familyId): void
    {
        $family = Family::with('guests')->findOrFail($familyId);

        $this->editingId = $family->id;
        $this->editLabel = $family->label;
        $this->editGuests = $family->guests
            ->mapWithKeys(fn ($guest): array => [
                (string) $guest->id => ['id' => $guest->id, 'name' => $guest->name, 'phone' => (string) $guest->phone],
            ])
            ->all();
    }

    public function addGuestRow(): void
    {
        $this->editGuests[Str::random(8)] = ['id' => null, 'name' => '', 'phone' => ''];
    }

    public function removeGuestRow(string $key): void
    {
        unset($this->editGuests[$key]);
    }

    public function saveFamily(): void
    {
        abort_if($this->editingId === null, 404);

        $this->validate([
            'editLabel' => ['required', 'string', 'max:255'],
            'editGuests.*.name' => ['nullable', 'string', 'max:255'],
            'editGuests.*.phone' => ['nullable', 'string', 'regex:/^(?:\D*\d){10,13}\D*$/'],
        ], [
            'editGuests.*.phone.regex' => 'Telefone inválido. Use DDD + número, ex.: (61) 98407-6120.',
        ]);

        $family = Family::with('guests')->findOrFail($this->editingId);

        $rows = collect($this->editGuests)
            ->map(fn (array $row): array => [...$row, 'name' => trim($row['name'])])
            ->filter(fn (array $row): bool => $row['name'] !== '');

        if ($rows->isEmpty()) {
            $this->addError('editGuests', 'A família precisa de ao menos uma pessoa.');

            return;
        }

        $family->update(['label' => $this->editLabel]);

        $keptIds = $rows->pluck('id')->filter()->values()->all();
        $family->guests()->whereNotIn('id', $keptIds)->delete();

        foreach ($rows as $row) {
            $attributes = ['name' => $row['name'], 'phone' => $row['phone']];

            if ($row['id']) {
                $family->guests->find($row['id'])?->update($attributes);
            } else {
                $family->guests()->create($attributes);
            }
        }

        $this->cancelEdit();
        unset($this->families);

        Flux::toast(text: 'Família atualizada.', variant: 'success');
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editLabel', 'editGuests');
        $this->resetValidation();
    }

    public function markInviteSent(int $guestId): void
    {
        Guest::findOrFail($guestId)->update(['invite_sent_at' => now()]);

        unset($this->families);
    }

    public function deleteFamily(int $familyId): void
    {
        Family::findOrFail($familyId)->delete();

        if ($this->editingId === $familyId) {
            $this->cancelEdit();
        }

        unset($this->families);

        Flux::modal("delete-family-{$familyId}")->close();
        Flux::toast(text: 'Família removida.', variant: 'success');
    }
};
?>

<div class="flex flex-col gap-8">
    <div>
        <h1 class="font-display text-2xl text-primary sm:text-3xl">Convidados</h1>
        <p class="mt-1 text-sm text-on-surface-variant">
            {{ $this->families->count() }} famílias ·
            {{ $this->families->sum(fn ($f) => $f->guests->count()) }} pessoas
        </p>
    </div>

    {{-- ===================== ADICIONAR ===================== --}}
    <form wire:submit="addFamily" class="flex flex-col gap-4 rounded-xl border border-outline-variant/50 bg-surface-container-low p-5">
        <flux:heading size="lg" class="font-display text-primary">Adicionar família</flux:heading>

        <flux:input wire:model="newLabel" label="Nome da família" placeholder="Ex.: Família Farias" />
        <flux:textarea
            wire:model="newNames"
            label="Pessoas (uma por linha)"
            rows="4"
            placeholder="Dave Farias&#10;Aline Farias"
        />

        <flux:button type="submit" variant="primary" class="self-start">Adicionar</flux:button>
    </form>

    {{-- ===================== LISTA ===================== --}}
    <div class="grid items-start gap-4 md:grid-cols-2">
        @forelse ($this->families as $family)
            <div wire:key="family-{{ $family->id }}" @class(['rounded-xl border border-outline-variant/50 bg-surface p-5', 'md:col-span-2' => $editingId === $family->id])>
                @if ($editingId === $family->id)
                    {{-- ---- edição inline ---- --}}
                    <form wire:submit="saveFamily" class="flex flex-col gap-4">
                        <flux:input wire:model="editLabel" label="Nome da família" />

                        <div class="flex flex-col gap-2">
                            <flux:label>Pessoas</flux:label>
                            <div class="grid gap-2 md:grid-cols-2">
                                @foreach ($editGuests as $key => $row)
                                    <div wire:key="edit-{{ $key }}" class="flex items-start gap-2">
                                        <flux:input wire:model="editGuests.{{ $key }}.name" placeholder="Nome" aria-label="Nome" class="min-w-0 flex-1" />
                                        <flux:input wire:model="editGuests.{{ $key }}.phone" type="tel" placeholder="(61) 98407-6120" aria-label="Telefone" class="min-w-0 flex-1" />
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            icon="trash"
                                            size="sm"
                                            wire:click="removeGuestRow('{{ $key }}')"
                                            aria-label="Remover pessoa"
                                        />
                                    </div>
                                @endforeach
                            </div>
                            @error('editGuests')
                                <flux:text class="text-error">{{ $message }}</flux:text>
                            @enderror
                            <flux:button type="button" variant="ghost" icon="plus" size="sm" wire:click="addGuestRow" class="self-start">
                                Adicionar pessoa
                            </flux:button>
                        </div>

                        <div class="flex gap-2">
                            <flux:button type="submit" variant="primary">Salvar</flux:button>
                            <flux:button type="button" variant="ghost" wire:click="cancelEdit">Cancelar</flux:button>
                        </div>
                    </form>
                @else
                    {{-- ---- visualização ---- --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-medium text-on-surface">{{ $family->label }}</h2>
                            <ul class="mt-2 flex flex-col gap-1">
                                @foreach ($family->guests as $guest)
                                    <li wire:key="guest-{{ $guest->id }}" class="flex flex-wrap items-center gap-2 text-sm text-on-surface-variant">
                                        {{ $guest->name }}
                                        @if ($guest->phone)
                                            <flux:button
                                                size="xs"
                                                variant="ghost"
                                                icon="paper-airplane"
                                                href="{{ $guest->whatsappInviteUrl() }}"
                                                target="_blank"
                                                wire:click="markInviteSent({{ $guest->id }})"
                                            >
                                                {{ $guest->invite_sent_at ? 'Reenviar convite' : 'Enviar convite' }}
                                            </flux:button>
                                        @endif
                                        @if ($guest->invite_sent_at)
                                            <span class="text-xs text-primary">enviado em {{ $guest->invite_sent_at->format('d/m H:i') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="startEdit({{ $family->id }})" aria-label="Editar" />
                            <flux:modal.trigger name="delete-family-{{ $family->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" aria-label="Excluir" />
                            </flux:modal.trigger>
                        </div>
                    </div>
                @endif

                <flux:modal name="delete-family-{{ $family->id }}" class="w-full md:max-w-md" wire:key="del-modal-{{ $family->id }}">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" class="font-display">Excluir "{{ $family->label }}"?</flux:heading>
                        <flux:text>
                            Serão apagados
                            <span class="font-medium text-on-surface">{{ $family->guests->count() }} pessoa(s)</span>
                            @php($responded = $family->guests->whereNotNull('responded_at')->count())
                            @if ($responded > 0)
                                e <span class="font-medium text-error">{{ $responded }} confirmação(ões) já registrada(s)</span>
                            @endif.
                            Esta ação não pode ser desfeita.
                        </flux:text>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">Cancelar</flux:button>
                            </flux:modal.close>
                            <flux:button variant="danger" wire:click="deleteFamily({{ $family->id }})">Excluir</flux:button>
                        </div>
                    </div>
                </flux:modal>
            </div>
        @empty
            <p class="rounded-xl border border-dashed md:col-span-2 border-outline-variant bg-surface-container-low p-8 text-center text-on-surface-variant">
                Nenhuma família cadastrada. Adicione a primeira acima.
            </p>
        @endforelse
    </div>
</div>
