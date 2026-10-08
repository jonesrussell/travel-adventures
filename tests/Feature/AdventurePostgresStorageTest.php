<?php

use App\Models\Adventure;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

beforeEach(function () {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Run explicitly against PostgreSQL with transaction rollback.');
    }
});

test('PostgreSQL persists the structured story and owner scoped creation key', function () {
    $draft = Adventure::factory()
        ->create();
    $story = $draft->story;
    expect($draft->fresh()
        ->story)
        ->toBe($story);
    Adventure::factory()
        ->create(['creation_key' => $draft->creation_key]);

    expect(fn () => Adventure::factory()
        ->for($draft->user)
        ->create(['creation_key' => $draft->creation_key]))
        ->toThrow(QueryException::class);
});

test('PostgreSQL prevents deletion of an owner with retained drafts', function () {
    $draft = Adventure::factory()
        ->create();
    expect(fn () => $draft->user->delete())
        ->toThrow(QueryException::class);
});
