---
paths:
  - 'app/Http/Middleware/EnsureConviteAccess.php,app/Models/Guest.php,resources/views/convite.blade.php'
---

# Models Views

## Convite virtual: link assinado por convidado, 404 sem assinatura
`/convite` (view convite.blade, assets em public/assets/convite, reprodução fiel do impresso — regras de design em spec/CLAUDE.md) só abre com `URL::signedRoute('convite', ['convidado' => id])` ou sessão `admin_authed`; resto = 404 pra não revelar a página. `convidado` é obrigatório e o Guest tem que existir: excluir o convidado no painel revoga o link. Não existe link geral (o `convite:link` foi removido de propósito — não era revogável). Assinatura inclui host, então APP_URL tem que bater com o domínio. `Guest::phone` guarda só dígitos com DDI 55 (mutator); `Guest::whatsappInviteUrl()` monta wa.me com mensagem + link assinado. Envio é 1 a 1 pelo botão "Enviar convite" no painel, que também grava `invite_sent_at` (`markInviteSent`; marca o clique, não confirma que a mensagem saiu); texto da mensagem é o oficial do casal, não reescrever — bulk exigiria WhatsApp Business API (Meta), não implementado.
