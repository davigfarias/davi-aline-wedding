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
