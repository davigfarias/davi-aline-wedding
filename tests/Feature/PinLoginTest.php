<?php

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('wedding.admin_pin', '4321');
    RateLimiter::clear('pin-login:127.0.0.1');
});

test('the panel is not reachable without a PIN', function () {
    $this->get(route('painel'))->assertRedirect(route('entrar'));
    $this->get(route('painel.convidados'))->assertRedirect(route('entrar'));
});

test('the panel is reachable once authenticated', function () {
    $this->withSession(['admin_authed' => true])
        ->get(route('painel'))
        ->assertOk();
});

test('the correct PIN authenticates and redirects to the panel', function () {
    Livewire::test('pages::pin-login')
        ->set('pin', '4321')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('painel'));

    expect(session('admin_authed'))->toBeTrue();
});

test('the wrong PIN is rejected and grants no access', function () {
    Livewire::test('pages::pin-login')
        ->set('pin', '0000')
        ->call('submit')
        ->assertNoRedirect();

    expect(session()->has('admin_authed'))->toBeFalse();
});

test('the PIN must be four digits', function () {
    Livewire::test('pages::pin-login')
        ->set('pin', '12')
        ->call('submit')
        ->assertHasErrors('pin');
});

test('it locks out after the configured number of failed attempts', function () {
    foreach (range(1, 5) as $ignored) {
        Livewire::test('pages::pin-login')->set('pin', '0000')->call('submit');
    }

    Livewire::test('pages::pin-login')
        ->set('pin', '4321')
        ->call('submit')
        ->assertNoRedirect();

    expect(session()->has('admin_authed'))->toBeFalse();
});

test('the lockout threshold comes from config', function () {
    config()->set('wedding.login.max_attempts', 2);

    foreach (range(1, 2) as $ignored) {
        Livewire::test('pages::pin-login')->set('pin', '0000')->call('submit');
    }

    Livewire::test('pages::pin-login')
        ->set('pin', '4321')
        ->call('submit')
        ->assertNoRedirect();

    expect(session()->has('admin_authed'))->toBeFalse();
});

test('an authenticated visitor to the PIN page is sent to the panel', function () {
    $this->withSession(['admin_authed' => true]);

    Livewire::test('pages::pin-login')->assertRedirect(route('painel'));
});

test('logging out clears the session', function () {
    $this->withSession(['admin_authed' => true])
        ->get(route('painel.sair'))
        ->assertRedirect(route('home'));

    expect(session()->has('admin_authed'))->toBeFalse();
});
