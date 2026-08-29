<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('/entrar', 'pages::pin-login')->name('entrar');

Route::get('/sair', function () {
    session()->forget('admin_authed');
    session()->regenerate();

    return redirect()->route('home');
})->name('painel.sair');

Route::middleware('admin.pin')->group(function () {
    Route::livewire('/painel', 'pages::painel.dashboard')->name('painel');
    Route::livewire('/painel/convidados', 'pages::painel.guests')->name('painel.convidados');
});
