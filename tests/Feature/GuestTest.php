<?php

use App\Models\Guest;

test('it normalizes the name when a guest is created', function () {
    $guest = Guest::factory()->create(['name' => 'João da Conceição']);

    expect($guest->name)->toBe('João da Conceição')
        ->and($guest->name_normalized)->toBe('joao da conceicao');
});

test('it keeps the normalized name in sync when the name changes', function () {
    $guest = Guest::factory()->create(['name' => 'Ana']);

    $guest->update(['name' => 'MÁRCIO']);

    expect($guest->fresh()->name_normalized)->toBe('marcio');
});

test('it casts the response columns', function () {
    $guest = Guest::factory()->attending()->create();

    expect($guest->is_attending)->toBeTrue()
        ->and($guest->responded_at)->toBeInstanceOf(DateTimeInterface::class);

    expect(Guest::factory()->create()->is_attending)->toBeNull();
});

test('it stores the phone as digits with the Brazilian country code', function (?string $typed, ?string $stored) {
    expect(Guest::factory()->create(['phone' => $typed])->phone)->toBe($stored);
})->with([
    'DDD + número formatado' => ['(61) 98407-6120', '5561984076120'],
    'já com DDI' => ['+55 61 98407-6120', '5561984076120'],
    'vazio' => ['', null],
    'nulo' => [null, null],
]);

test('the WhatsApp link carries a signed invite link that opens the invite', function () {
    $guest = Guest::factory()->create(['name' => 'Ana Souza', 'phone' => '61984076120']);

    $whatsappUrl = $guest->whatsappInviteUrl();
    parse_str((string) parse_url($whatsappUrl, PHP_URL_QUERY), $query);
    preg_match('/https?:\/\/\S+/', $query['text'], $inviteUrl);

    expect($whatsappUrl)->toStartWith('https://wa.me/5561984076120?text=')
        ->and($query['text'])->toStartWith("Olá!\n\nCom o coração cheio de alegria")
        ->and($query['text'])->toEndWith($inviteUrl[0]);

    $this->get($inviteUrl[0])->assertOk();
});

test('a guest without a phone has no WhatsApp link', function () {
    expect(Guest::factory()->create()->whatsappInviteUrl())->toBeNull();
});
