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
