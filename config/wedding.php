<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PIN do painel
    |--------------------------------------------------------------------------
    |
    | PIN fixo de 4 dígitos usado para acessar o painel dos noivos. Apenas uma
    | pessoa usa o painel, então não existe cadastro de usuários.
    |
    */

    'admin_pin' => env('ADMIN_PIN'),

    /*
    |--------------------------------------------------------------------------
    | Throttle do login
    |--------------------------------------------------------------------------
    |
    | Tentativas de PIN erradas permitidas por IP antes do bloqueio, e por
    | quantos segundos cada tentativa conta contra o IP.
    |
    */

    'login' => [
        'max_attempts' => 5,
        'decay_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prazo do RSVP
    |--------------------------------------------------------------------------
    |
    | Depois desta data a modal de confirmação de presença fica somente-leitura.
    |
    */

    'rsvp_deadline' => env('RSVP_DEADLINE'),

    /*
    |--------------------------------------------------------------------------
    | Contato dos noivos
    |--------------------------------------------------------------------------
    |
    | Mostrado quando um convidado não é encontrado na busca do RSVP.
    |
    */

    'contact' => env('WEDDING_CONTACT'),

];
