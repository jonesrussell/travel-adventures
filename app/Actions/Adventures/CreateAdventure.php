<?php

namespace App\Actions\Adventures;

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CreateAdventure
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{adventure: Adventure, created: bool}
     */
    public function handle(User $owner, array $input): array
    {
        $content = [
            'title' => $input['title'] ?? null,
            'story' => $input['story'] ?? ['type' => 'doc', 'content' => [['type' => 'paragraph']]],
            'story_format_version' => $input['story_format_version'] ?? 1,
            'location' => $input['location'] ?? null,
            'travel_start_date' => $input['travel_start_date'] ?? null,
            'travel_end_date' => $input['travel_end_date'] ?? null,
        ];
        // Fingerprint the normalized input before generating a title so retries across dates still match.
        $fingerprint = hash('sha256', json_encode($this->canonicalize($content), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return DB::transaction(function () use ($owner, $input, $content, $fingerprint): array {
            // Lock an existing owner row because a new creation key has no adventure row to lock yet.
            User::query()
                ->whereKey($owner->id)
                ->lockForUpdate()
                ->firstOrFail();
            $existing = $owner->adventures()
                ->withTrashed()
                ->where('creation_key', $input['creation_key'])
                ->lockForUpdate()
                ->first();
            if ($existing !== null) {
                if ($existing->trashed() || $existing->published_at !== null
                    || ! hash_equals($existing->request_fingerprint, $fingerprint)) {
                    throw new ConflictHttpException('Creation key conflicts with an existing request.');
                }

                return ['adventure' => $existing, 'created' => false];
            }

            $adventure = new Adventure;
            $adventure->fill($content);
            $adventure->title = $content['title'] ?? 'Adventure · '.now('UTC')
                ->format('F j, Y');
            $adventure->creation_key = $input['creation_key'];
            $adventure->request_fingerprint = $fingerprint;
            $owner->adventures()
                ->save($adventure);

            return ['adventure' => $adventure->refresh(), 'created' => true];
        }, attempts: 3);
    }

    /**
     * Ignore object key order while preserving ordered story nodes and text whitespace.
     */
    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map($this->canonicalize(...), $value);
    }
}
