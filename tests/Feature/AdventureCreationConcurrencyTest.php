<?php

use App\Actions\Adventures\CreateAdventure;
use App\Models\User;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

beforeEach(function () {
    if (getenv('RUN_POSTGRES_CONCURRENCY') !== '1') {
        $this->markTestSkipped('Run explicitly against an isolated PostgreSQL test database.');
    }
    if (DB::getDriverName() !== 'pgsql' || ! str_ends_with(DB::connection()
        ->getDatabaseName(), '_test')) {
        throw new RuntimeException('Concurrency tests require an isolated PostgreSQL database ending in _test.');
    }
});

test('simultaneous identical creation attempts produce one PostgreSQL draft', function () {
    $owner = User::factory()
        ->create();
    $ownerId = $owner->id;
    $key = fake()
        ->uuid();
    try {
        // Remove Pest's bound test instance so the closure can be serialized into child processes.
        $task = Closure::bind(static function () use ($ownerId, $key): array {
            $result = (new CreateAdventure)
                ->handle(User::findOrFail($ownerId), ['creation_key' => $key, 'title' => 'Concurrent trip']);

            return ['id' => $result['adventure']->id, 'created' => $result['created']];
        }, null, null);
        $results = Concurrency::driver('process')
            ->run([$task, $task]);
        expect($results[0]['id'])
            ->toBe($results[1]['id']);
        expect(array_column($results, 'created'))
            ->toEqualCanonicalizing([true, false]);
        expect($owner->adventures()
            ->count())
            ->toBe(1);
    } finally {
        $owner->adventures()
            ->withTrashed()
            ->forceDelete();
        $owner->delete();
    }
});

test('simultaneous differing creation attempts return one conflict in PostgreSQL', function () {
    $owner = User::factory()
        ->create();
    $ownerId = $owner->id;
    $key = fake()
        ->uuid();
    try {
        $tasks = [];
        foreach (['Banff', 'Jasper'] as $title) {
            // Child processes must resolve their own models and database connections.
            $tasks[] = Closure::bind(static function () use ($ownerId, $key, $title): int {
                try {
                    $result = (new CreateAdventure)
                        ->handle(User::findOrFail($ownerId), ['creation_key' => $key, 'title' => $title]);

                    return $result['created'] ? 201 : 200;
                } catch (ConflictHttpException) {
                    return 409;
                }
            }, null, null);
        }
        expect(Concurrency::driver('process')
            ->run($tasks))
            ->toEqualCanonicalizing([201, 409]);
        expect($owner->adventures()
            ->count())
            ->toBe(1);
    } finally {
        $owner->adventures()
            ->withTrashed()
            ->forceDelete();
        $owner->delete();
    }
});
