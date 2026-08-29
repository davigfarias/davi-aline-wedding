<?php

test('the landing page renders with its sections and the RSVP entry point', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Nossa História')
        ->assertSee('O Evento')
        ->assertSee('Confirmar Presença')
        ->assertSeeLivewire('rsvp');
});
