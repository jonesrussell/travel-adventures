<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\AdventureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property CarbonImmutable|null $travel_start_date
 * @property CarbonImmutable|null $travel_end_date
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $first_published_at
 */
#[Fillable(['title', 'story', 'story_format_version', 'location', 'travel_start_date', 'travel_end_date'])]
#[Hidden(['user_id', 'creation_key', 'request_fingerprint'])]
class Adventure extends Model
{
    /** @use HasFactory<AdventureFactory> */
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'story' => 'array',
            'story_format_version' => 'integer',
            'version' => 'integer',
            'travel_start_date' => 'immutable_date',
            'travel_end_date' => 'immutable_date',
            'published_at' => 'immutable_datetime',
            'first_published_at' => 'immutable_datetime',
        ];
    }
}
