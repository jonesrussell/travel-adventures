<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;
use stdClass;

/**
 * Validate the restricted editor schema without sanitizing or silently discarding input.
 */
class StoryDocument implements ValidationRule
{
    private const MAX_JSON_BYTES = 256 * 1024;

    private const MAX_VISIBLE_CHARACTERS = 20000;

    private const MAX_NODES = 5000;

    private const MAX_DEPTH = 16;

    private const MAX_LINK_CHARACTERS = 2048;

    private const MAX_ORDERED_LIST_START = 1000000;

    private int $nodes = 0;

    /** @var list<string> */
    private array $blocks = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // A rule instance may be reused; traversal state belongs to this validation only.
        $this->nodes = 0;
        $this->blocks = [];
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || strlen($json) > self::MAX_JSON_BYTES) {
            $this->invalid($attribute, 'The story must not exceed 262144 UTF-8 JSON bytes.');
        }
        $this->node($value, $attribute, 1, ['doc']);
        if (mb_strlen(implode("\n", $this->blocks)) > self::MAX_VISIBLE_CHARACTERS) {
            $this->invalid($attribute, 'The story must not exceed 20000 visible characters.');
        }
    }

    /** @param list<string> $allowed */
    private function node(mixed $node, string $path, int $depth, array $allowed): void
    {
        if ($node instanceof stdClass) {
            $node = get_object_vars($node);
        }
        if (++$this->nodes > self::MAX_NODES || $depth > self::MAX_DEPTH) {
            $this->invalid($path, 'The story exceeds its node or depth limit.');
        }
        if (! is_array($node) || array_is_list($node) || ! in_array($node['type'] ?? null, $allowed, true)) {
            $this->invalid($path, 'The story contains an unsupported node.');
        }
        $type = $node['type'];
        $keys = match ($type) {
            'text' => ['type', 'text', 'marks'],
            'hardBreak' => ['type'],
            'heading', 'orderedList' => ['type', 'attrs', 'content'],
            default => ['type', 'content'],
        };
        $this->keys($node, $keys, $path);
        if ($type === 'text') {
            if (! is_string($node['text'] ?? null) || $node['text'] === '' || mb_strlen($node['text']) > self::MAX_VISIBLE_CHARACTERS) {
                $this->invalid($path.'.text', 'Text must contain 1 to 20000 characters.');
            }
            if (array_key_exists('marks', $node)) {
                $this->marks($node['marks'], $path.'.marks');
            }

            return;
        }
        if ($type === 'hardBreak') {
            return;
        }
        if (in_array($type, ['heading', 'orderedList'], true)) {
            $this->nodeAttributes($type, $node['attrs'] ?? null, $path);
        }
        $children = $node['content'] ?? [];
        $optionalContent = in_array($type, ['paragraph', 'heading'], true);
        if (! is_array($children) || ! array_is_list($children) || count($children) > self::MAX_NODES
            || (! $optionalContent && $children === [])
            || (array_key_exists('content', $node) && $node['content'] === null)) {
            $this->invalid($path.'.content', 'The node content is invalid.');
        }
        $allowedChildren = match ($type) {
            'doc' => ['paragraph', 'heading', 'bulletList', 'orderedList'],
            'paragraph', 'heading' => ['text', 'hardBreak'],
            'bulletList', 'orderedList' => ['listItem'],
            'listItem' => ['paragraph', 'bulletList', 'orderedList'],
            default => $this->invalid($path, 'The story contains an unsupported node.'),
        };
        $firstChild = $children[0] ?? null;
        if ($firstChild instanceof stdClass) {
            $firstChild = get_object_vars($firstChild);
        }
        if ($type === 'listItem' && (! is_array($firstChild) || ($firstChild['type'] ?? null) !== 'paragraph')) {
            $this->invalid($path.'.content', 'A list item must start with a paragraph.');
        }
        $text = '';
        foreach ($children as $index => $child) {
            $this->node($child, $path.'.content.'.$index, $depth + 1, $allowedChildren);
            if ($optionalContent) {
                if ($child instanceof stdClass) {
                    $child = get_object_vars($child);
                }
                $text .= $child['type'] === 'hardBreak' ? "\n" : $child['text'];
            }
        }
        // Paragraphs and headings contribute text; list containers must not count it again.
        if ($optionalContent) {
            $this->blocks[] = $text;
        }
    }

    private function nodeAttributes(string $type, mixed $attrs, string $path): void
    {
        if ($attrs instanceof stdClass) {
            $attrs = get_object_vars($attrs);
        }
        $field = $type === 'heading' ? 'level' : 'start';
        if (! is_array($attrs) || array_keys($attrs) !== [$field] || ! is_int($attrs[$field])) {
            $this->invalid($path.'.attrs', 'The node attributes are invalid.');
        }
        if (($type === 'heading' && ! in_array($attrs[$field], [2, 3], true))
            || ($type === 'orderedList' && ($attrs[$field] < 1 || $attrs[$field] > self::MAX_ORDERED_LIST_START))) {
            $this->invalid($path.'.attrs.'.$field, 'The node attribute is out of range.');
        }
    }

    private function marks(mixed $marks, string $path): void
    {
        if (! is_array($marks) || ! array_is_list($marks) || count($marks) > 3) {
            $this->invalid($path, 'The text marks are invalid.');
        }
        $seen = [];
        foreach ($marks as $index => $mark) {
            if ($mark instanceof stdClass) {
                $mark = get_object_vars($mark);
            }
            $markPath = $path.'.'.$index;
            if (! is_array($mark) || ! in_array($mark['type'] ?? null, ['bold', 'italic', 'link'], true)
                || in_array($mark['type'], $seen, true)) {
                $this->invalid($markPath, 'The text mark is unsupported or repeated.');
            }
            $seen[] = $mark['type'];
            $this->keys($mark, $mark['type'] === 'link' ? ['type', 'attrs'] : ['type'], $markPath);
            if ($mark['type'] === 'link') {
                $attrs = $mark['attrs'] ?? null;
                if ($attrs instanceof stdClass) {
                    $attrs = get_object_vars($attrs);
                }
                if (! is_array($attrs) || array_keys($attrs) !== ['href'] || ! is_string($attrs['href'])) {
                    $this->invalid($markPath.'.attrs', 'A link requires only an href.');
                }
                $this->link($attrs['href'], $markPath.'.attrs.href');
            }
        }
    }

    /**
     * Reject ambiguous URL forms as well as unsafe schemes before links reach the renderer.
     */
    private function link(string $href, string $path): void
    {
        $url = parse_url($href);
        if (mb_strlen($href) > self::MAX_LINK_CHARACTERS || preg_match('/[\x00-\x20\x7f\\\\]/', $href)
            || preg_match('/%(?:0[0-9a-f]|1[0-9a-f]|7f)/i', $href)
            || ! preg_match('~^https?://~', $href)
            || ! is_array($url) || empty($url['host'])
            || isset($url['user']) || isset($url['pass'])
            || filter_var($href, FILTER_VALIDATE_URL) === false) {
            $this->invalid($path, 'Links must use HTTP or HTTPS with a host and no credentials or control characters.');
        }
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @param  list<string>  $allowed
     */
    private function keys(array $value, array $allowed, string $path): void
    {
        if (array_diff(array_keys($value), $allowed) !== []) {
            $this->invalid($path, 'The story contains unknown attributes.');
        }
    }

    /**
     * Stop at the first invalid node and retain its nested field path in the 422 response.
     */
    private function invalid(string $path, string $message): never
    {
        throw ValidationException::withMessages([$path => [$message]]);
    }
}
