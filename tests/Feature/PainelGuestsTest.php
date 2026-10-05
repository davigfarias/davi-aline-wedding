<?php

use App\Models\Family;
use App\Models\Guest;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['admin_authed' => true]);
});

test('the guest list route is unreachable without the panel session', function () {
    session()->forget('admin_authed');

    $this->get(route('painel.convidados'))->assertRedirect(route('entrar'));
});

test('adding a family creates it with one guest per non-blank line', function () {
    Livewire::test('pages::painel.guests')
        ->set('newLabel', 'Família Farias')
        ->set('newNames', "  Dave Farias \n\nAline Farias\n  ")
        ->call('addFamily')
        ->assertHasNoErrors();

    $family = Family::firstWhere('label', 'Família Farias');

    expect($family->guests->pluck('name')->all())->toBe(['Dave Farias', 'Aline Farias']);
});

test('adding a family needs a label and at least one name', function () {
    Livewire::test('pages::painel.guests')
        ->set('newLabel', '')
        ->set('newNames', '   ')
        ->call('addFamily')
        ->assertHasErrors(['newLabel', 'newNames']);

    expect(Family::count())->toBe(0);
});

test('editing a family renames it, renames a guest, adds and removes guests', function () {
    $family = Family::factory()->create(['label' => 'Antigo']);
    $keep = Guest::factory()->for($family)->create(['name' => 'Nome Velho']);
    $drop = Guest::factory()->for($family)->create(['name' => 'Sai Fora']);

    $component = Livewire::test('pages::painel.guests')->call('startEdit', $family->id);
    $component->call('removeGuestRow', (string) $drop->id);
    $component->call('addGuestRow');

    $rows = $component->get('editGuests');
    $rows[(string) $keep->id]['name'] = 'Nome Novo';
    $newKey = collect(array_keys($rows))->first(fn ($k) => $rows[$k]['id'] === null);
    $rows[$newKey]['name'] = 'Pessoa Nova';

    $component->set('editLabel', 'Novo')
        ->set('editGuests', $rows)
        ->call('saveFamily')
        ->assertHasNoErrors()
        ->assertSet('editingId', null);

    $family->refresh()->load('guests');

    expect($family->label)->toBe('Novo')
        ->and($family->guests->pluck('name')->sort()->values()->all())->toBe(['Nome Novo', 'Pessoa Nova'])
        ->and(Guest::whereKey($drop->id)->exists())->toBeFalse()
        ->and($keep->fresh()->name_normalized)->toBe('nome novo');
});

test('a family cannot be saved with no names', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create();

    Livewire::test('pages::painel.guests')
        ->call('startEdit', $family->id)
        ->set('editGuests', ['a' => ['id' => null, 'name' => '  ']])
        ->call('saveFamily')
        ->assertHasErrors('editGuests');

    expect($family->fresh()->guests)->toHaveCount(1);
});

test('deleting a family removes it and its guests, including recorded responses', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->attending()->create();
    Guest::factory()->for($family)->create();
    $survivor = Family::factory()->create();
    Guest::factory()->for($survivor)->create();

    Livewire::test('pages::painel.guests')
        ->call('deleteFamily', $family->id)
        ->assertHasNoErrors();

    expect(Family::whereKey($family->id)->exists())->toBeFalse()
        ->and(Guest::where('family_id', $family->id)->count())->toBe(0)
        ->and(Family::whereKey($survivor->id)->exists())->toBeTrue();
});

test('the guest list page renders for an authenticated visitor', function () {
    Family::factory()->create(['label' => 'Família Visível']);

    $this->get(route('painel.convidados'))
        ->assertOk()
        ->assertSee('Família Visível');
});

test('editing a family saves each guest phone and rejects an invalid one', function () {
    $family = Family::factory()->create();
    $guest = Guest::factory()->for($family)->create();

    $component = Livewire::test('pages::painel.guests')
        ->call('startEdit', $family->id)
        ->set("editGuests.{$guest->id}.phone", '123')
        ->call('saveFamily')
        ->assertHasErrors("editGuests.{$guest->id}.phone");

    $component->set("editGuests.{$guest->id}.phone", '(61) 98407-6120')
        ->call('saveFamily')
        ->assertHasNoErrors();

    expect($guest->fresh()->phone)->toBe('5561984076120');
});

test('only guests with a phone get the send-invite button', function () {
    $family = Family::factory()->create();
    Guest::factory()->for($family)->create(['phone' => '61984076120']);
    Guest::factory()->for($family)->create(['phone' => null]);

    $html = Livewire::test('pages::painel.guests')->html();

    expect(substr_count($html, 'Enviar convite'))->toBe(1)
        ->and($html)->toContain('https://wa.me/5561984076120');
});

test('sending the invite records when it was sent and offers a resend', function () {
    $this->freezeSecond();
    $guest = Guest::factory()->create(['phone' => '61984076120']);

    Livewire::test('pages::painel.guests')
        ->assertSee('Enviar convite')
        ->call('markInviteSent', $guest->id)
        ->assertSee('Reenviar convite')
        ->assertSee('enviado em '.now()->format('d/m H:i'));

    expect($guest->fresh()->invite_sent_at->equalTo(now()))->toBeTrue();
});
