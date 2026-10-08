<?php

use App\Rules\StoryDocument;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

function storyWithText(string $text, array $marks = []): array
{
    return ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text, 'marks' => $marks]]]]];
}

test('supported headings lists hard breaks and marks are accepted', function () {
    $story = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Trip']]],
        ['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Walk', 'marks' => [['type' => 'bold'], ['type' => 'italic'], ['type' => 'link', 'attrs' => ['href' => 'https://example.com/travel']]]], ['type' => 'hardBreak']]],
            ['type' => 'orderedList', 'attrs' => ['start' => 1], 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph']]]]],
        ]]]],
    ]];
    expect(Validator::make(['story' => $story], ['story' => [new StoryDocument]])
        ->passes())
        ->toBeTrue();
});

test('story accepts exactly 20000 Unicode visible characters', function () {
    expect(Validator::make(['story' => storyWithText(str_repeat('é', 20000))], ['story' => [new StoryDocument]])
        ->passes())
        ->toBeTrue();
});

test('unsafe or ambiguous links are rejected', function (string $href) {
    expect(fn () => Validator::make(['story' => storyWithText('Link', [['type' => 'link', 'attrs' => ['href' => $href]]])], ['story' => [new StoryDocument]])
        ->validate())
        ->toThrow(ValidationException::class);
})
    ->with(['javascript:alert(1)', 'data:text/html,test', '//example.com', 'https:///missing', 'https://user:pass@example.com', "https://example.com/\nfoo", 'https://example.com/%0afoo', 'https://example.com\\evil']);

test('invalid story structures and limits are rejected', function (array $story) {
    expect(fn () => Validator::make(['story' => $story], ['story' => [new StoryDocument]])
        ->validate())
        ->toThrow(ValidationException::class);
})
    ->with([
        'unknown node' => [['type' => 'doc', 'content' => [['type' => 'image']]]],
        'unknown attribute' => [['type' => 'doc', 'content' => [['type' => 'paragraph', 'attrs' => ['onclick' => 'bad']]]]],
        'heading level' => [['type' => 'doc', 'content' => [['type' => 'heading', 'attrs' => ['level' => 1]]]]],
        'empty document' => [['type' => 'doc', 'content' => []]],
        'text limit' => [storyWithText(str_repeat('a', 20001))],
        'duplicate marks' => [storyWithText('x', [['type' => 'bold'], ['type' => 'bold']])],
        'bad list nesting' => [['type' => 'doc', 'content' => [['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'heading', 'attrs' => ['level' => 2]]]]]]]]],
        'node limit' => [['type' => 'doc', 'content' => array_fill(0, 5000, ['type' => 'paragraph'])]],
        'byte limit' => [['type' => 'doc', 'content' => array_fill(0, 100, ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => str_repeat('é', 2000)]]])]],
    ]);

test('visible text counts separating newlines between blocks', function () {
    $story = storyWithText(str_repeat('a', 19999));
    $story['content'][] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'b']]];
    expect(fn () => Validator::make(['story' => $story], ['story' => [new StoryDocument]])
        ->validate())
        ->toThrow(ValidationException::class);
});

test('overly deep nested lists are rejected', function () {
    $node = ['type' => 'paragraph'];
    for ($i = 0; $i < 10; $i++) {
        $node = ['type' => 'bulletList', 'content' => [['type' => 'listItem', 'content' => [['type' => 'paragraph'], $node]]]];
    }
    expect(fn () => Validator::make(['story' => ['type' => 'doc', 'content' => [$node]]], ['story' => [new StoryDocument]])
        ->validate())
        ->toThrow(ValidationException::class);
});
