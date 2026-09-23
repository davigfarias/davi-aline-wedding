<?php

use App\Models\Family;
use App\Models\Guest;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('wedding.rsvp_deadline', now()->addMonth()->toDateString());
    config()->set('wedding.contact', '(11) 90000-0000');
});

test('a search matching one guest goes straight to confirmation and shows only that guest', function () {
    $family = Family::factory()->create(['label' => 'Família Silva']);
    Guest::factory()->for($family)->create(['name' => 'João Silva']);
    Guest::factory()->for($family)->create(['name' => 'Maria Silva']);

    Livewire::test('rsvp')
        ->set('query', 'joão silva')
        ->call('search')
        ->assertSet('step', 'confirm')
        ->assertSee('João Silva')
        ->assertDontSee('Maria Silva');
});

test('the search ignores accents and case', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['name' => 'João da Conceição']);

    Livewire::test('rsvp')
        ->set('query', 'JOAO DA CONCEICAO')
        ->call('search')
        ->assertSet('step', 'confirm');
});

test('a partial name match still finds the guest', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['name' => 'Anderson Farias']);

    Livewire::test('rsvp')
        ->set('query', 'farias')
        ->call('search')
        ->assertSet('step', 'confirm');
});

test('multiple guests matching the name lead to a choice step without exposing other family members', function () {
    $a = Family::factory()->create(['label' => 'Família Silva']);
    $b = Family::factory()->create(['label' => 'Família Souza']);
    $marceloA = Guest::factory()->for($a)->create(['name' => 'Marcelo Silva']);
    Guest::factory()->for($a)->create(['name' => 'Ana Silva']);
    Guest::factory()->for($b)->create(['name' => 'Marcelo Souza']);
    Guest::factory()->for($b)->create(['name' => 'Bruno Souza']);

    $component = Livewire::test('rsvp')
        ->set('query', 'marcelo')
        ->call('search')
        ->assertSet('step', 'choose')
        ->assertSee('Marcelo Silva')
        ->assertSee('Marcelo Souza')
        ->assertDontSee('Ana Silva')
        ->assertDontSee('Bruno Souza');

    $component->call('choose', $marceloA->id)
        ->assertSet('step', 'confirm')
        ->assertSet('guestId', $marceloA->id)
        ->assertSee('Marcelo Silva')
        ->assertDontSee('Ana Silva');
});

test('a guest cannot be chosen unless it was in the search results', function () {
    $shown = Family::factory()->create();
    $hidden = Family::factory()->create();
    Guest::factory()->for($shown)->create(['name' => 'Carlos Dias']);
    $hiddenGuest = Guest::factory()->for($hidden)->create(['name' => 'Outra Pessoa']);

    Livewire::test('rsvp')
        ->set('query', 'carlos')
        ->call('search')
        ->call('choose', $hiddenGuest->id)
        ->assertStatus(403);
});

test('an unknown name shows the not-found step with the contact', function () {
    Livewire::test('rsvp')
        ->set('query', 'ninguém aqui')
        ->call('search')
        ->assertSet('step', 'notfound')
        ->assertSee('(11) 90000-0000');
});

test('submitting records attendance and timestamp only for the searched guest', function () {
    $family = Family::factory()->create();
    $going = Guest::factory()->for($family)->create(['name' => 'Rita Nunes']);
    $sibling = Guest::factory()->for($family)->create(['name' => 'Paulo Nunes']);

    Livewire::test('rsvp')
        ->set('query', 'rita nunes')
        ->call('search')
        ->set('isAttending', true)
        ->set('familyMessage', 'Mal podemos esperar!')
        ->call('submit')
        ->assertSet('step', 'done');

    expect($going->fresh())->is_attending->toBeTrue()
        ->responded_at->not->toBeNull();
    expect($sibling->fresh()->responded_at)->toBeNull();
    expect($family->fresh()->message)->toBe('Mal podemos esperar!');
});

test('re-opening a responded guest prefills the previous answer', function () {
    $family = Family::factory()->create(['message' => 'Recado antigo']);
    $guest = Guest::factory()->for($family)->attending()->create(['name' => 'Sofia Lima']);

    Livewire::test('rsvp')
        ->set('query', 'sofia')
        ->call('search')
        ->assertSet('step', 'confirm')
        ->assertSet('isAttending', true)
        ->assertSet('familyMessage', 'Recado antigo');
});

test('after the deadline the confirmation is read-only and submit is blocked', function () {
    config()->set('wedding.rsvp_deadline', now()->subDay()->toDateString());

    $family = Family::factory()->create();
    Guest::factory()->for($family)->attending()->create(['name' => 'Bruno Rocha']);

    $component = Livewire::test('rsvp')
        ->set('query', 'bruno')
        ->call('search')
        ->assertSet('step', 'confirm')
        ->assertDontSee('wire:submit');

    $component->call('submit')->assertStatus(403);
});

test('startOver clears the flow', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['name' => 'Tiago Melo']);

    Livewire::test('rsvp')
        ->set('query', 'tiago')
        ->call('search')
        ->call('startOver')
        ->assertSet('step', 'search')
        ->assertSet('guestId', null)
        ->assertSet('query', '');
});
