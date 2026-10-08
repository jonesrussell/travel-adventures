<?php

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('authenticated unverified authors can create and list drafts', function () {
    $user = User::factory()->unverified()->create();
    expect(Gate::forUser($user)->allows('create', Adventure::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('viewAny', Adventure::class))->toBeTrue()
        ->and(Gate::forUser(null)->allows('create', Adventure::class))->toBeFalse();
});

test('only the owner may access an active draft', function (string $ability) {
    $draft = Adventure::factory()->create();
    $other = User::factory()->create();
    expect(Gate::forUser($draft->user)->allows($ability, $draft))->toBeTrue()
        ->and(Gate::forUser($other)->inspect($ability, $draft)->status())->toBe(404)
        ->and(Gate::forUser(null)->allows($ability, $draft))->toBeFalse();

    $draft->delete();
    expect(Gate::forUser($draft->user)->inspect($ability, $draft)->status())->toBe(404);
})->with(['view', 'update', 'delete']);
