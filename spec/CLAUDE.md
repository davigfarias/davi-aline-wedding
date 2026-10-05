# Convite de casamento — Davi & Aline

Landing page que substitui o convite impresso (A4, feito no Canva). O design
não é livre: **ele é a reprodução fiel do convite de papel**. Todas as cores,
fontes, ornamentos e imagens deste projeto foram extraídos do PDF original.
Antes de mudar qualquer coisa visual, leia a seção "Regras de design".

- Evento: sábado, 20 de março de 2027, às 17:00h
- Site irmão (lista de presentes / RSVP): `casamentodavialine.laravel.cloud`
- Idioma: pt-BR. Toda a interface e os comentários do código em português.

---

## Como rodar

Não há build. É HTML, CSS e um módulo ES.

```bash
python3 -m http.server 8000
# abra http://localhost:8000
```

**Abrir o `index.html` direto pelo `file://` não funciona por inteiro**: a fonte
com `crossorigin` e o `<script type="module">` são bloqueados por CORS. Sempre
sirva por HTTP ao testar.

---

## Estrutura

```
convite-davi-aline/
├── index.html                     página única, semântica, sem framework
├── assets/
│   ├── css/style.css              tokens + layout (mobile-first)
│   ├── js/revelar.js              módulo ES: revela seções ao rolar
│   ├── fonts/                     woff2 + woff dos dois display faces
│   └── img/                       recortes do PDF original
└── CLAUDE.md                      este arquivo
```

Seções do `index.html`, nesta ordem: **capa → versículo → informações → RSVP →
QR Code → rodapé**. Não existe seção de cerimônia; ela foi removida
deliberadamente pelos noivos, junto com o endereço da igreja. Não a recrie por
conta própria.

---

## Regras de design

### 1. A paleta e a tipografia são fechadas

Todos os valores vivem em `:root`, em `assets/css/style.css`. Use os tokens,
nunca um hex solto no meio do CSS.

| Token | Valor | De onde veio |
|---|---|---|
| `--verde-nome` | `#0b3c24` | cor dos nomes "Davi & Aline" no impresso |
| `--verde-suave` | `#3e6250` | o número "20" da data |
| `--salvia` | `#a0b18e` | fundo da versão verde do verso impresso |
| `--creme` | `#f4f1e6` | texto sobre o verde |
| `--casca` | `#f7f8f4` | fundo das faixas claras |
| `--tinta` / `--tinta-suave` | `#141413` / `#4a4a45` | corpo de texto |

Quatro famílias, cada uma com um papel fixo:

- **Cormorant Garamond** (`--fonte-corpo`) — todo o texto corrido.
- **Cormorant SC** (`--fonte-versal`) — versalete: o versículo, rótulos,
  "SÁBADO | ÀS 17:00H" e os botões.
- **Simonetta** (`--fonte-titulo`) — títulos de seção, títulos dos cartões,
  "MARÇO", "2027" e o "20".
- **Symphony Pro** + **Anastasia Script** (`--fonte-nomes` / `--fonte-e`) —
  **exclusivamente** para "Davi", "Aline" e o "&".

As três primeiras vêm do Google Fonts. As duas últimas estão em
`assets/fonts/` e **são subconjuntos extraídos do PDF**: contêm apenas os
glifos `D a v i A l n e` e `&`. Não as use em nenhum outro texto — qualquer
outra letra simplesmente não existe no arquivo e cai no fallback.

Nunca troque por Inter, Roboto, Arial ou qualquer sans-serif.

### 2. As imagens não se repintam nem se recortam

Estão em `assets/img/`, todas tiradas do PDF:

- `folhagem-{sup,inf}-{esq,dir}.webp` — as quatro quinas da guirlanda de
  eucalipto da capa. **As bordas de corte já têm um gradiente de alpha** para a
  emenda não aparecer; se precisar recortar de novo, refaça o esmaecimento ou a
  linha reta do corte fica visível.
- `flores-tracadas.webp` — as flores em traço do verso, usadas como textura de
  fundo na seção de informações, a `opacity: .2`. Era 37% no impresso; na tela
  20% mantém o texto legível. Não passe de 25%.
- `guirlanda-rodape.webp` — a guirlanda da base, no rodapé.
- `monograma.png` — o brasão D&A, já vetorizado com canal alpha na cor
  `#022e15`. Não aplique `filter` nele.
- `qrcode.svg` — QR vetorial redesenhado módulo a módulo a partir do impresso.
  Aponta para `casamentodavialine.laravel.cloud`. **Se mudar, gere um QR novo
  e confira que ele lê**; não reescale o SVG para um valor que não seja
  múltiplo de 37,5px (29 módulos + quiet zone), senão serrilha.

Escala da folhagem: a guirlanda é desenhada no tamanho que tinha no papel A4.
Aumentar muito faz as folhas dominarem o hero — já aconteceu e foi revertido.

### 3. Os ornamentos de fio e losango

O fio horizontal com losangos nas pontas é o ornamento da data no convite
impresso, redesenhado em SVG inline (`.data__fio` e `.ornamento`). Ele usa
`currentColor`, então herda a cor do contexto — por isso funciona tanto no
verde quanto no creme. Reaproveite-o para separar seções, não invente outro
divisor.

### 4. Os textos são os do convite

Toda frase da página está no convite de papel, palavra por palavra, inclusive
os dois-pontos em "DRESS CODE:", "RECEPÇÃO:" e "PRESENTES:" e a grafia
`20|02|2027`. **Não reescreva, não corrija, não "melhore" o texto.** Se achar
que falta vírgula, pergunte antes.

As únicas frases que não existem no impresso são rótulos de botão:
"Confirmar presença", "Mapas e localizações", "Lista de casamento",
"Ver no mapa", "Lista de presentes" e "Chamar no WhatsApp".

### 5. Acessibilidade — não regrida

- Links e botões são `<a href>` de verdade, com 48px de altura mínima.
- `aria-label` nos links que só têm imagem (QR Code).
- A folhagem, a textura floral e os fios são `aria-hidden` / `alt=""`.
- Existe um `.skip-link` no topo.
- `prefers-reduced-motion` desliga a animação de entrada e o scroll suave.
- Contraste: texto `--tinta-suave` sobre branco e sobre `--salvia` passa em
  AA. Se escurecer um fundo, revalide.

Nunca ponha `onclick` em `div` ou `span`.

### 6. Responsividade

Mobile-first, sem breakpoint mágico. A tipografia é fluida via `clamp()`:
o primeiro valor vale em 360px, o último em 1440px. Os cartões usam
`grid-template-columns: repeat(auto-fit, minmax(17.5rem, 1fr))` — eles se
reorganizam sozinhos, não adicione media query para isso.

Só há duas media queries de layout:
- `max-width: 40rem` — a data empilha (dia em cima, mês e ano lado a lado) e
  os botões da capa viram coluna.
- `prefers-reduced-motion` e `print`.

Testado em 360, 390, 768, 1024, 1440 e 1920px, sem scroll horizontal em
nenhuma. **Se mexer no hero, confira as seis.**

---

## Estilo de código

- **Nada de procedural solto.** O `revelar.js` é composto de funções puras e
  pequenas; mantenha esse padrão. Se o projeto crescer, prefira um módulo com
  classe ou um conjunto de funções compostas — nunca um script linear com
  variáveis globais mutáveis.
- CSS: tokens em `:root`, classes no padrão bloco/elemento (`cartao`,
  `cartao__titulo`). Sem `!important` fora do bloco de `prefers-reduced-motion`.
- HTML: um `<section>` por bloco, com `aria-label` ou `aria-labelledby`.
  Comentários de seção em caixa, como já estão.

---

## Portar para Laravel

O site irmão roda em Laravel Cloud, então o caminho natural é virar uma view:

```bash
# imagens, fontes, css e js
cp -r assets public/assets/convite

# a página
# index.html -> resources/views/convite.blade.php
```

Na view, troque os caminhos relativos por helpers:

```blade
<link rel="stylesheet" href="{{ asset('assets/convite/css/style.css') }}">
<img src="{{ asset('assets/convite/img/monograma.png') }}" alt="Monograma de Davi e Aline">
```

E no `routes/web.php`:

```php
Route::view('/convite', 'convite')->name('convite');
```

Se for extrair os dados (data, telefone, links) para configuração, ponha num
objeto de valor — por exemplo `app/Casamento/DadosDoConvite.php`, imutável,
com `readonly`, injetado na view por um View Composer ou um Action. Não
espalhe `env()` pela Blade.

---

## Antes de dar por pronto

1. Servir por HTTP e abrir em 360, 768 e 1440px.
2. Conferir que as duas fontes locais carregaram (DevTools → Network → Font)
   e que "Davi & Aline" não caiu no fallback.
3. Clicar nos cinco links externos: WhatsApp, os dois Google Maps, a lista e
   o QR Code.
4. Ler o QR com o celular e confirmar que abre o site certo.
5. Rodar o Lighthouse: a meta é 100 em acessibilidade.
6. Testar com `prefers-reduced-motion: reduce` ativo — tudo deve aparecer.

---

## O que foi decidido e não deve voltar atrás

- A seção da cerimônia (endereço da igreja + pedido de pontualidade) foi
  **removida a pedido dos noivos**. O botão "Mapas e localizações" por isso vai
  direto ao Google Maps da igreja, e não a uma âncora interna.
- O convite tem duas versões de verso no PDF (uma verde-sálvia com o
  placeholder `[INSIRA SITE AQUI]`, outra branca com o endereço real). A
  branca é a válida.
- O verde-sálvia sobreviveu como fundo da faixa de RSVP.
