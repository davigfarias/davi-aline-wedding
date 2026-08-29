<?php

use App\Models\Family;
use App\Models\Guest;

test('deleting a family cascades to its guests', function () {
    $family = Family::factory()->create();
    Guest::factory()->count(3)->for($family)->create();
    $other = Guest::factory()->create();

    $family->delete();

    expect(Guest::whereBelongsTo($family)->count())->toBe(0)
        ->and(Guest::whereKey($other->id)->exists())->toBeTrue();
});

test('a family exposes its guests', function () {
    $family = Family::factory()->create();
    Guest::factory()->count(2)->for($family)->create();

    expect($family->guests)->toHaveCount(2);
});
