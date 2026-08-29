<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Sair do painel dos noivos.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authed');
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
