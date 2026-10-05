---
paths:
  - 'resources/views/pages/painel/**'
---

# Pages Painel

## flux:input class vai pro wrapper w-full
`class` em `<flux:input>` sem label é mesclado no wrapper `w-full relative block`. Em linha flex, `w-44` não vence o `w-full`, e `flex-1` (basis 0) ao lado de um irmão `w-full` encolhe até sumir (bug: nome do convidado invisível na edição). Use `min-w-0 flex-1` em todos os irmãos, ou `class:input` para estilizar o próprio <input>.
