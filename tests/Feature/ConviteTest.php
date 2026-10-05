<?php

use App\Models\Guest;
use Illuminate\Support\Facades\URL;

test('the invite is hidden from a plain URL visit', function () {
    $this->get('/convite')->assertNotFound();
});

test('a tampered signature does not open the invite', function () {
    $guest = Guest::factory()->create();

    $this->get(URL::signedRoute('convite', ['convidado' => $guest->id]).'x')->assertNotFound();
});

test('a signed link without a guest does not open the invite', function () {
    $this->get(URL::signedRoute('convite'))->assertNotFound();
});

test('a guest signed link opens the invite', function () {
    $guest = Guest::factory()->create();

    $this->get(URL::signedRoute('convite', ['convidado' => $guest->id]))
        ->assertOk()
        ->assertSee('Convidam para a cerimônia de seu casamento')
        ->assertSee('assets/convite/css/style.css')
        ->assertSee('no valor de R$ 106,90 o kilo.')
        ->assertSee('href="'.route('home').'#local"', false);
});

test('the couple logged into the panel can preview the invite', function () {
    $this->withSession(['admin_authed' => true])
        ->get('/convite')
        ->assertOk();
});

test('a guest link stops working once the guest is deleted', function () {
    $guest = Guest::factory()->create();
    $inviteUrl = URL::signedRoute('convite', ['convidado' => $guest->id]);

    $this->get($inviteUrl)->assertOk();

    $guest->delete();

    $this->get($inviteUrl)->assertNotFound();
});
