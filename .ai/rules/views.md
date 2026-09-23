---
paths:
  - 'resources/views/components/*rsvp*,resources/views/welcome.blade.php'
---

# Views

## RSVP: componente renderiza o próprio flux:modal + reset on open
O componente Livewire `rsvp` (SFC em resources/views/components/) renderiza ele mesmo o `<flux:modal name="rsvp">` — welcome.blade só monta `<livewire:rsvp />` uma vez; os botões de nav/hero abrem via `<flux:modal.trigger name="rsvp">`. Máquina de estados em `$step` (search/choose/confirm/done/notfound), props `#[Locked]` (step, guestId, foundGuestIds). `loadGuest()` só aceita id que estava no último resultado de busca (anti-enumeração) — `abort(403)` senão. Deadline: `#[Computed] rsvpOpen()` compara `now()` com `config('wedding.rsvp_deadline')` (endOfDay); passou = confirm vira read-only e `submit()` dá 403. Reset entre aberturas: `x-on:modal-show.document` no root chama `$wire.startOver()` quando `$event.detail.name==='rsvp'` e `$wire.step!=='search'` (modal-close não dispara confiável pelo botão de fechar do Flux).

## RSVP é por convidado, não por família (discrição)
Busca/escolha/confirmação giram em torno de um `Guest` individual (`$guestId`, `$foundGuestIds`), nunca lista os outros membros da `Family`. Motivo: pedido explícito da noiva pra não expor quem mais foi convidado na mesma família — antes mostrava a família toda no passo de confirmação. `familyMessage` continua salvo em `Family::message` (compartilhado, sobrescreve entre membros), mas cada `Guest` confirma `is_attending`/`responded_at` só de si mesmo. Passo "choose" mostra nome do convidado batido + `guest->family->label` como subtítulo (só pra ajudar a pessoa se identificar entre homônimos de famílias diferentes), nunca a lista de outros membros.
