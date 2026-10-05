<?php

namespace App\Http\Middleware;

use App\Models\Guest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConviteAccess
{
    /**
     * Libera o convite só para o link assinado de um convidado que ainda existe
     * (excluir no painel revoga o link) ou para os noivos logados no painel. Qualquer outro acesso
     * recebe 404, para não revelar que a página existe.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInvited = $request->hasValidSignature()
            && Guest::whereKey($request->query('convidado'))->exists();

        if (! $isInvited && ! $request->session()->get('admin_authed')) {
            abort(404);
        }

        return $next($request);
    }
}
