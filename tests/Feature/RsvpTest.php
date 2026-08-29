<?php

use App\Models\Family;
use App\Models\Guest;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('wedding.rsvp_deadline', now()->addMonth()->toDateString());
    config()->set('wedding.contact', '(11) 90000-0000');
});

test('a search matching one family goes straight to confirmation', function () {
    $family = Family::factory()->create(['label' => 'Família Silva']);
    Guest::factory()->for($family)->create(['name' => 'João Silva']);
    Guest::factory()->for($family)->create(['name' => 'Maria Silva']);

    Livewire::test('rsvp')
        ->set('query', 'joão silva')
        ->call('search')
        ->assertSet('step', 'confirm')
        ->assertSet('familyId', $family->id)
        ->assertSee('Maria Silva');
});

test('the search ignores accents and case', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['name' => 'João da Conceição']);

    Livewire::test('rsvp')
        ->set('query', 'JOAO DA CONCEICAO')
        ->call('search')
        ->assertSet('step', 'confirm');
});

test('a partial name match still finds the family', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['name' => 'Anderson Farias']);

    Livewire::test('rsvp')
        ->set('query', 'farias')
        ->call('search')
        ->assertSet('step', 'confirm');
});

test('multiple matching families lead to a choice step', function () {
    $a = Family::factory()->create(['label' => 'Família A']);
    $b = Family::factory()->create(['label' => 'Família B']);
    Guest::factory()->for($a)->create(['name' => 'João Alves']);
    Guest::factory()->for($b)->create(['name' => 'João Brito']);

    $component = Livewire::test('rsvp')
        ->set('query', 'joão')
        ->call('search')
        ->assertSet('step', 'choose')
        ->assertSee('Família A')
        ->assertSee('Família B');

    $component->call('choose', $a->id)
        ->assertSet('step', 'confirm')
        ->assertSet('familyId', $a->id);
});

test('a family cannot be chosen unless it was in the search results', function () {
    $shown = Family::factory()->create();
    $hidden = Family::factory()->create();
    Guest::factory()->for($shown)->create(['name' => 'Carlos Dias']);
    Guest::factory()->for($hidden)->create(['name' => 'Outra Pessoa']);

    Livewire::test('rsvp')
        ->set('query', 'carlos')
        ->call('search')
        ->call('choose', $hidden->id)
        ->assertStatus(403);
});

test('an unknown name shows the not-found step with the contact', function () {
    Livewire::test('rsvp')
        ->set('query', 'ninguém aqui')
        ->call('search')
        ->assertSet('step', 'notfound')
        ->assertSee('(11) 90000-0000');
});

test('submitting records attendance, timestamp and message for the whole family', function () {
    $family = Family::factory()->create();
    $going = Guest::factory()->for($family)->create(['name' => 'Rita Nunes']);
    $notGoing = Guest::factory()->for($family)->create(['name' => 'Paulo Nunes']);

    Livewire::test('rsvp')
        ->set('query', 'nunes')
        ->call('search')
        ->set("attendance.{$going->id}", true)
        ->set("attendance.{$notGoing->id}", false)
        ->set('familyMessage', 'Mal podemos esperar!')
        ->call('submit')
        ->assertSet('step', 'done');

    expect($going->fresh())->is_attending->toBeTrue()
        ->responded_at->not->toBeNull();
    expect($notGoing->fresh()->is_attending)->toBeFalse();
    expect($family->fresh()->message)->toBe('Mal podemos esperar!');
});

test('re-opening a responded family prefills the previous answers', function () {
    $family = Family::factory()->create(['message' => 'Recado antigo']);
    $guest = Guest::factory()->for($family)->attending()->create(['name' => 'Sofia Lima']);

    Livewire::test('rsvp')
        ->set('query', 'sofia')
        ->call('search')
        ->assertSet('step', 'confirm')
        ->assertSet("attendance.{$guest->id}", true)
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
        ->assertSet('familyId', null)
        ->assertSet('query', '');
});
