---
paths:
  - 'app/Providers/AppServiceProvider.php,resources/views/pages/painel/**'
---

# Painel

## Painel: middleware persistente + editGuests com chave associativa
`AppServiceProvider::boot()` chama `Livewire::addPersistentMiddleware([EnsureAdminPin::class])` — sem isso o endpoint `/livewire/update` rodaria ações do painel (deleteFamily, saveFamily...) sem sessão. É a autorização real dessas ações; não há dono por registro (um casal só), então `review.py` acusar `find-without-authorize` em `deleteFamily` é falso-positivo. Em `pages::painel.guests`, `$editGuests` é `array<string,array{id,name}>` com chave aleatória (Str::random) por linha — NÃO lista indexada: `wire:model="editGuests.{key}.name"` + `wire:key="edit-{key}"` + `removeGuestRow` faz `unset($this->editGuests[$key])`. Índice numérico + reindex quebrava o binding no morph (input perdia valor ao remover linha do meio). Delete de família: `<flux:modal name="delete-family-{id}">` por linha; a ação chama `Flux::modal(...)->close()` antes de deletar.
