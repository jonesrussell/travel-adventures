<?php

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('guests receive 401 for all private draft operations', function () {
    $this->getJson('/api/v1/adventures')
        ->assertUnauthorized();
    $this->getJson('/api/v1/adventures/999')
        ->assertUnauthorized();
    $this->postJson('/api/v1/adventures', ['creation_key' => fake()
        ->uuid()])
        ->assertUnauthorized();
});

test('unverified authors create a private draft with a generated UTC title', function () {
    $this->travelTo(Carbon::parse('2026-10-07 23:59:00', 'UTC'));
    $owner = User::factory()
        ->unverified()
        ->create();
    $key = fake()
        ->uuid();
    $response = $this->actingAs($owner)
        ->postJson('/api/v1/adventures', ['creation_key' => $key]);
    $response->assertCreated()
        ->assertJsonPath('data.title', 'Adventure · October 7, 2026')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('data.published_at', null)
        ->assertJsonPath('data.first_published_at', null)
        ->assertJsonPath('data.story', ['type' => 'doc', 'content' => [['type' => 'paragraph']]]);
    $this->assertDatabaseHas('adventures', ['id' => $response->json('data.id'), 'user_id' => $owner->id, 'creation_key' => $key]);
    expect(array_keys($response->json('data')))
        ->toEqualCanonicalizing([
            'id', 'title', 'location', 'travel_start_date', 'travel_end_date', 'status', 'version',
            'published_at', 'first_published_at', 'created_at', 'updated_at', 'page_path', 'story_format_version', 'story',
        ]);
    $this->travelBack();
});

test('identical normalized retries return 200 without changing the draft across dates', function () {
    $this->travelTo(Carbon::parse('2026-10-07 12:00:00', 'UTC'));
    $owner = User::factory()
        ->create();
    $key = fake()
        ->uuid();
    $first = $this->actingAs($owner)
        ->postJson('/api/v1/adventures', ['creation_key' => $key, 'title' => '   '])
        ->assertCreated();
    $this->travel(1)
        ->day();
    $this->postJson('/api/v1/adventures', ['creation_key' => strtoupper($key), 'title' => null, 'location' => ' ', 'story_format_version' => 1,
        'story' => ['content' => [['type' => 'paragraph']], 'type' => 'doc'], 'travel_start_date' => null])
        ->assertOk()
        ->assertJsonPath('data.id', $first->json('data.id'))
        ->assertJsonPath('data.title', 'Adventure · October 7, 2026')
        ->assertJsonPath('data.updated_at', $first->json('data.updated_at'));
    $this->assertDatabaseCount('adventures', 1);
    $this->travelBack();
});

test('separate intentional submissions may have the same content or another owners key', function () {
    $owner = User::factory()
        ->create();
    $other = Adventure::factory()
        ->create(['title' => 'Banff']);
    $this->actingAs($owner)
        ->postJson('/api/v1/adventures', ['creation_key' => $other->creation_key, 'title' => 'Banff'])
        ->assertCreated();
    $this->postJson('/api/v1/adventures', ['creation_key' => fake()
        ->uuid(), 'title' => 'Banff'])
        ->assertCreated();
    expect($owner->adventures()
        ->count())
        ->toBe(2);
});

test('a reused creation key with different input returns 409', function () {
    $owner = User::factory()
        ->create();
    $payload = ['creation_key' => fake()
        ->uuid(), 'title' => 'Banff'];
    $this->actingAs($owner)
        ->postJson('/api/v1/adventures', $payload)
        ->assertCreated();
    $this->postJson('/api/v1/adventures', [...$payload, 'title' => 'Jasper'])
        ->assertConflict()
        ->assertExactJson(['message' => 'Creation key conflicts with an existing request.']);
    $this->assertDatabaseCount('adventures', 1);
    $this->assertDatabaseHas('adventures', ['title' => 'Banff']);
});

test('retries cannot resurrect trash or reopen a published adventure', function (string $state) {
    $owner = User::factory()
        ->create();
    $payload = ['creation_key' => fake()
        ->uuid()];
    $id = $this->actingAs($owner)
        ->postJson('/api/v1/adventures', $payload)
        ->json('data.id');
    $draft = Adventure::findOrFail($id);
    if ($state === 'trash') {
        $draft->delete();
    } else {
        $draft->published_at = now();
        $draft->save();
    }
    $this->postJson('/api/v1/adventures', $payload)
        ->assertConflict();
    expect(Adventure::withTrashed()
        ->count())
        ->toBe(1);
})
    ->with(['trash', 'published']);

test('custom title location and travel dates persist while story whitespace is preserved', function () {
    $owner = User::factory()
        ->create();
    $story = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '  A walk  ']]]]];
    $response = $this->actingAs($owner)
        ->postJson('/api/v1/adventures', [
            'creation_key' => fake()
                ->uuid(), 'title' => '  Banff  ', 'location' => '  Canada  ', 'story' => $story,
            'travel_start_date' => '2030-01-01', 'travel_end_date' => '2030-01-01',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Banff')
        ->assertJsonPath('data.location', 'Canada')
        ->assertJsonPath('data.story', fn (array $actual): bool => $actual == $story)
        ->assertJsonPath('data.travel_end_date', '2030-01-01');
    $this->assertDatabaseHas('adventures', ['id' => $response->json('data.id'), 'title' => 'Banff', 'location' => 'Canada']);
});

test('missing nonowned published and trashed detail all return the same 404', function (string $state) {
    $owner = User::factory()
        ->create();
    $draft = Adventure::factory()
        ->for($owner)
        ->create();
    $viewer = $owner;
    $id = $draft->id;
    if ($state === 'nonowned') {
        $viewer = User::factory()
            ->create();
    } elseif ($state === 'published') {
        $draft->published_at = now();
        $draft->save();
    } elseif ($state === 'trash') {
        $draft->delete();
    } else {
        $id += 999;
    }
    $this->actingAs($viewer)
        ->getJson('/api/v1/adventures/'.$id)
        ->assertNotFound()
        ->assertExactJson(['message' => 'Not found.']);
})
    ->with(['missing', 'nonowned', 'published', 'trash']);

test('an owner can reopen a draft without internal or account fields', function () {
    $draft = Adventure::factory()
        ->create(['title' => 'Banff weekend']);
    $this->actingAs($draft->user)
        ->getJson('/api/v1/adventures/'.$draft->id)
        ->assertOk()
        ->assertJsonPath('data.title', 'Banff weekend')
        ->assertJsonPath('data.page_path', '/adventures/'.$draft->id.'/banff-weekend')
        ->assertJsonMissingPath('data.user_id')
        ->assertJsonMissingPath('data.creation_key')
        ->assertJsonMissingPath('data.request_fingerprint')
        ->assertJsonMissingPath('data.email');
});

test('owner pagination excludes others publication and trash with deterministic ties', function () {
    $this->freezeTime();
    $owner = User::factory()
        ->create();
    $drafts = Adventure::factory()
        ->count(13)
        ->for($owner)
        ->create();
    Adventure::factory()
        ->create();
    Adventure::factory()
        ->for($owner)
        ->create(['published_at' => now()]);
    Adventure::factory()
        ->for($owner)
        ->create()
        ->delete();
    $first = $this->actingAs($owner)
        ->getJson('/api/v1/adventures')
        ->assertOk()
        ->assertJsonCount(12, 'data');
    expect(array_column($first->json('data'), 'id'))
        ->toBe($drafts->pluck('id')
            ->reverse()
            ->take(12)
            ->values()
            ->all());
    $first->assertJsonMissingPath('data.0.story')
        ->assertJsonPath('meta.per_page', 12);
    $cursor = $first->json('meta.next_cursor');
    expect($cursor)
        ->toBeString();
    $last = $this->getJson('/api/v1/adventures?cursor='.$cursor)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $drafts->first()
            ->id)
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonPath('links.next', null);
    $this->getJson('/api/v1/adventures?cursor='.$last->json('meta.prev_cursor'))
        ->assertOk()
        ->assertJsonCount(12, 'data');
});

test('empty owner list has no next cursor', function () {
    $this->actingAs(User::factory()
        ->create())
        ->getJson('/api/v1/adventures')
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.next_cursor', null)
        ->assertJsonPath('links.next', null);
});

test('invalid cursor returns 422 instead of resetting the page', function (mixed $cursor) {
    $this->actingAs(User::factory()
        ->create())
        ->getJson('/api/v1/adventures?'.http_build_query(['cursor' => $cursor]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cursor');
})
    ->with([
        'garbage' => 'oops', 'empty' => '', 'array' => [['x']], 'oversize' => str_repeat('x', 2049),
        'wrong shape' => (new Cursor(['id' => 1]))
            ->encode(),
        'string id' => (new Cursor(['id' => '1', 'updated_at' => '2026-10-07 12:00:00']))
            ->encode(),
        'invalid date' => (new Cursor(['id' => 1, 'updated_at' => '2026-02-30 12:00:00']))
            ->encode(),
    ]);

test('draft validation rejects invalid fields with 422', function (array $input, string $field) {
    $this->actingAs(User::factory()
        ->create())
        ->postJson('/api/v1/adventures', [...['creation_key' => fake()
            ->uuid()], ...$input])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
    $this->assertDatabaseCount('adventures', 0);
})
    ->with([
        'missing key' => [['creation_key' => null], 'creation_key'],
        'invalid key' => [['creation_key' => 'abc'], 'creation_key'],
        'title length' => [['title' => str_repeat('a', 161)], 'title'],
        'title type' => [['title' => 123], 'title'],
        'location length' => [['location' => str_repeat('a', 201)], 'location'],
        'owner injection' => [['user_id' => 99], 'user_id'],
        'publication injection' => [['published_at' => '2026-10-07'], 'published_at'],
        'version injection' => [['version' => 99], 'version'],
        'unknown field' => [['surprise' => true], 'surprise'],
        'story version' => [['story_format_version' => 2], 'story_format_version'],
        'string story version' => [['story_format_version' => '1'], 'story_format_version'],
        'null story' => [['story' => null], 'story'],
        'invalid date' => [['travel_start_date' => '2026-02-30'], 'travel_start_date'],
        'year zero' => [['travel_start_date' => '0000-01-01'], 'travel_start_date'],
        'end without start' => [['travel_end_date' => '2026-10-07'], 'travel_end_date'],
        'reversed dates' => [['travel_start_date' => '2026-10-08', 'travel_end_date' => '2026-10-07'], 'travel_end_date'],
    ]);

test('query parameters cannot supply the creation body', function () {
    $this->actingAs(User::factory()
        ->create())
        ->postJson('/api/v1/adventures?creation_key='.fake()
            ->uuid(), ['title' => null])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('creation_key');
});

test('JSON transport errors are explicit', function (string $body, array $headers, int $status) {
    $this->call('POST', '/api/v1/adventures', server: $headers, content: $body)
        ->assertStatus($status)
        ->assertHeader('Content-Type', 'application/json');
})
    ->with([
        'accept' => ['{}', ['CONTENT_TYPE' => 'application/json'], 406],
        'content type' => ['{}', ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'text/plain'], 415],
        'malformed' => ['{', ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'], 400],
        'array body' => ['[]', ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'], 400],
        'body limit' => ['{"padding":"'.str_repeat('a', 524288).'"}', ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'], 413],
    ]);

test('HTTP mutations enforce CSRF token fallback outside the framework test bypass', function () {
    // Laravel bypasses forgery checks during tests; exercise the real token path explicitly.
    $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });
    $this->actingAs(User::factory()
        ->create())
        ->postJson('/api/v1/adventures', ['creation_key' => fake()
            ->uuid()])
        ->assertStatus(419);
    $this->withSession(['_token' => 'test-csrf-token'])
        ->postJson('/api/v1/adventures', ['creation_key' => fake()
            ->uuid()], ['X-CSRF-TOKEN' => 'test-csrf-token'])
        ->assertCreated();
});

test('the API applies story validation and preserves JSON object versus list shapes', function (array $story, string $field) {
    $this->actingAs(User::factory()
        ->create())
        ->postJson('/api/v1/adventures', ['creation_key' => fake()
            ->uuid(), 'story' => $story])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
    $this->assertDatabaseCount('adventures', 0);
})
    ->with([
        'unsupported node' => [['type' => 'doc', 'content' => [['type' => 'image']]], 'story.content.0'],
        'object content' => [['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => new stdClass]]], 'story.content.0.content'],
        'object marks' => [['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'x', 'marks' => new stdClass]]]]], 'story.content.0.content.0.marks'],
    ]);

test('formatted lists are accepted through the API', function () {
    $story = ['type' => 'doc', 'content' => [['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Walk', 'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://example.com']]]]]]]]]]]];
    $this->actingAs(User::factory()
        ->create())
        ->postJson('/api/v1/adventures', ['creation_key' => fake()
            ->uuid(), 'story' => $story])
        ->assertCreated()
        ->assertJsonPath('data.story', fn (array $actual): bool => $actual == $story);
});
