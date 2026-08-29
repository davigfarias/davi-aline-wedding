---
paths:
  - 'app/Http/Middleware/EnsureAdminPin.php,resources/views/pages/**,routes/web.php'
---

# Pages

## Painel auth = PIN fixo, sem Fortify e sem User
O painel dos noivos (/painel, /painel/convidados) é protegido pelo middleware alias `admin.pin` (EnsureAdminPin), que só checa a flag de sessão `admin_authed`. Não há login de usuário: a página `pages::pin-login` (rota `/entrar`) valida o PIN de 4 dígitos contra `config('wedding.admin_pin')` (env ADMIN_PIN) com rate limit de 5/min por IP, e grava a flag na sessão. `/sair` limpa a flag. Fortify continua no composer mas com rotas desligadas (`Fortify::ignoreRoutes()` no FortifyServiceProvider, `config/fortify.php` features=[] e views=false). Não reintroduza rotas de auth/registro. Componentes Livewire são SFC no namespace `pages::`; layout global `layouts::painel` via config/livewire.php.
