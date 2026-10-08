<?php

namespace Database\Factories;

use App\Models\Adventure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Adventure> */
class AdventureFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()
                ->sentence(4),
            'story' => ['type' => 'doc', 'content' => [['type' => 'paragraph']]],
            'story_format_version' => 1,
            'creation_key' => fake()
                ->uuid(),
            'request_fingerprint' => hash('sha256', fake()
                ->uuid()),
        ];
    }
}
