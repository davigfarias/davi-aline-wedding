<?php

test('the landing page renders with its sections and the RSVP entry point', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Nossa História')
        ->assertSee('O Evento')
        ->assertSee('Confirmar Presença')
        ->assertSee('O buffet será por adesão, no valor de R$106,90 o kilo.')
        ->assertSeeLivewire('rsvp');
});

test('the gift section links to the Havan and Amazon lists', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'https://lista.havan.com.br/Convidado/ItensListaPresente/957800',
            'https://www.amazon.com.br/hz/wishlist/ls/2YIREQKX7BCW8?ref_=wl_share',
        ], escape: false);
});
