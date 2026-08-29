---
paths:
  - 'resources/views/components/*rsvp*,resources/views/welcome.blade.php'
---

# Views

## RSVP: componente renderiza o próprio flux:modal + reset on open
O componente Livewire `rsvp` (SFC em resources/views/components/) renderiza ele mesmo o `<flux:modal name="rsvp">` — welcome.blade só monta `<livewire:rsvp />` uma vez; os botões de nav/hero abrem via `<flux:modal.trigger name="rsvp">`. Máquina de estados em `$step` (search/choose/confirm/done/notfound), props `#[Locked]` (step, familyId, foundFamilyIds). `loadFamily()` só aceita id que estava no último resultado de busca (anti-enumeração) — `abort(403)` senão. Deadline: `#[Computed] rsvpOpen()` compara `now()` com `config('wedding.rsvp_deadline')` (endOfDay); passou = confirm vira read-only e `submit()` dá 403. Reset entre aberturas: `x-on:modal-show.document` no root chama `$wire.startOver()` quando `$event.detail.name==='rsvp'` e `$wire.step!=='search'` (modal-close não dispara confiável pelo botão de fechar do Flux).
