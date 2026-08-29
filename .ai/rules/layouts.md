---
paths:
  - 'resources/views/pages/**,resources/views/layouts/**'
---

# Layouts

## Login do painel: flux:otp + layouts::guest + Flux::toast
A tela `pages::pin-login` usa `#[Layout('layouts::guest')]` (layout bare centralizado com `@persist('toast')`), `<flux:otp wire:model="pin" length="4" submit="auto">` pro código, e reporta erro (PIN errado / lockout) via `Flux::toast(variant: 'danger')` — não via erro de validação inline; só `digits:4` continua como erro Livewire. Throttle vem de `config('wedding.login.max_attempts'|'decay_seconds')` injetado com `#[Config]` no método `submit()`. Padrão espelha os projetos river-* (auth-code), mas SEM tabela access_tokens: aqui é 1 código fixo em `config('wedding.admin_pin')`. `layouts::painel` também tem o toast group pro resto do painel.
