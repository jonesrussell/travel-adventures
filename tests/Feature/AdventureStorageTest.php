<?php

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('draft storage preserves structured story and private defaults', function () {
    $owner = User::factory()
        ->create();
    $story = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'A mountain walk.']]]]];
    $adventure = Adventure::factory()
        ->for($owner)
        ->create(['story' => $story, 'travel_start_date' => '2026-10-07']);
    $adventure->refresh();

    expect($adventure->story)
        ->toBe($story)
        ->and($adventure->version)
        ->toBe(1)
        ->and($adventure->story_format_version)
        ->toBe(1)
        ->and($adventure->published_at)
        ->toBeNull()
        ->and($adventure->first_published_at)
        ->toBeNull()
        ->and($adventure->travel_start_date->format('Y-m-d'))
        ->toBe('2026-10-07')
        ->and($adventure->user->is($owner))
        ->toBeTrue()
        ->and($owner->adventures->sole()
            ->is($adventure))
        ->toBeTrue()
        ->and($adventure->toArray())
        ->not->toHaveKeys(['user_id', 'creation_key', 'request_fingerprint']);
});

test('creation keys are scoped to an owner and retained in trash', function () {
    $draft = Adventure::factory()
        ->create();
    Adventure::factory()
        ->create(['creation_key' => $draft->creation_key]);
    $draft->delete();
    expect(Adventure::find($draft->id))
        ->toBeNull();
    $this->assertSoftDeleted($draft);

    expect(fn () => Adventure::factory()
        ->for($draft->user)
        ->create(['creation_key' => $draft->creation_key]))
        ->toThrow(QueryException::class);
});

test('content filling cannot assign ownership or lifecycle attributes', function () {
    $draft = new Adventure;
    $draft->fill(['title' => 'My walk', 'user_id' => 999, 'version' => 99, 'published_at' => now(), 'creation_key' => fake()
        ->uuid(), 'request_fingerprint' => 'forged']);
    expect($draft->title)
        ->toBe('My walk')
        ->and($draft->getAttributes())
        ->not->toHaveKeys(['user_id', 'version', 'published_at', 'creation_key', 'request_fingerprint']);
});

test('account deletion cannot orphan retained adventures', function () {
    $draft = Adventure::factory()
        ->create();
    expect(fn () => $draft->user->delete())
        ->toThrow(QueryException::class);
});
