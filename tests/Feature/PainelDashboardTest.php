<?php

use App\Models\Family;
use App\Models\Guest;
use Livewire\Livewire;

test('the stats count people by response state', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->attending()->count(2)->create();
    Guest::factory()->for($family)->declined()->create();
    Guest::factory()->for($family)->count(3)->create();

    Livewire::test('pages::painel.dashboard')
        ->assertSet('stats.attending', 2)
        ->assertSet('stats.declined', 1)
        ->assertSet('stats.pending', 3)
        ->assertSet('stats.total', 6);
});

test('only families with a response show by default', function () {
    $responded = Family::factory()->create(['label' => 'Família Respondeu']);
    Guest::factory()->for($responded)->attending()->create();

    $silent = Family::factory()->create(['label' => 'Família Silenciosa']);
    Guest::factory()->for($silent)->create();

    Livewire::test('pages::painel.dashboard')
        ->assertSee('Família Respondeu')
        ->assertDontSee('Família Silenciosa')
        ->set('showAll', true)
        ->assertSee('Família Silenciosa');
});

test('families are ordered by their most recent response', function () {
    $older = Family::factory()->create(['label' => 'Família Antiga']);
    Guest::factory()->for($older)->create(['is_attending' => true, 'responded_at' => now()->subDays(3)]);

    $newer = Family::factory()->create(['label' => 'Família Recente']);
    Guest::factory()->for($newer)->create(['is_attending' => true, 'responded_at' => now()->subHour()]);

    Livewire::test('pages::painel.dashboard')
        ->assertSeeInOrder(['Família Recente', 'Família Antiga']);
});

test('a card shows each member status and the family message', function () {
    $family = Family::factory()->create(['label' => 'Família Teste', 'message' => 'Até lá!']);
    Guest::factory()->for($family)->attending()->create(['name' => 'Ana Vai']);
    Guest::factory()->for($family)->declined()->create(['name' => 'Bia Nao']);

    Livewire::test('pages::painel.dashboard')
        ->assertSee('Ana Vai')
        ->assertSee('Bia Nao')
        ->assertSee('Até lá!');
});

test('the panel page renders the confirmed count for an authenticated visitor', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->attending()->count(4)->create();

    $this->withSession(['admin_authed' => true])
        ->get(route('painel'))
        ->assertOk()
        ->assertSee('confirmados');
});
