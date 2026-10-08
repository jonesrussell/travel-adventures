<?php

namespace App\Http\Requests\Adventures;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\Validator;

class ListAdventuresRequest extends FormRequest
{
    /**
     * Pagination comes from the URL; a GET body must not override the cursor.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return $this->query->all();
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'cursor' => ['sometimes', 'bail', 'required', 'string', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                if (! $this->isValidCursor($value)) {
                    $fail('The cursor is invalid.');
                }
            }],
        ];
    }

    /**
     * Require the canonical cursor shape for our two ordering columns, rather than
     * letting malformed cursors silently restart pagination or reach the database.
     */
    private function isValidCursor(string $value): bool
    {
        $cursor = Cursor::fromEncoded($value);
        $data = $cursor?->toArray();
        if ($cursor === null || $cursor->encode() !== $value || ! is_array($data)
            || count($data) !== 3
            || ! is_bool($data['_pointsToNextItems'] ?? null)
            || ! is_int($data['id'] ?? null)
            || $data['id'] < 1
            || ! is_string($data['updated_at'] ?? null)
            || substr($data['updated_at'], 0, 4) === '0000'
            || Validator::make($data, ['updated_at' => ['date_format:Y-m-d H:i:s']])
                ->fails()) {
            return false;
        }

        return true;
    }

    public function cursor(): ?Cursor
    {
        return Cursor::fromEncoded($this->validated('cursor'));
    }
}
