<?php

namespace App\Http\Resources;

use App\Models\Adventure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Explicit response fields keep ownership and retry metadata out of the API contract.
 * This resource serves private drafts only; published responses need their own design.
 *
 * @mixin Adventure
 */
class AdventureSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'location' => $this->location,
            'travel_start_date' => $this->travel_start_date?->format('Y-m-d'),
            'travel_end_date' => $this->travel_end_date?->format('Y-m-d'),
            'status' => 'draft',
            'version' => $this->version,
            'published_at' => null,
            'first_published_at' => $this->first_published_at?->utc()
                ->toISOString(),
            'created_at' => $this->created_at?->utc()
                ->toISOString(),
            'updated_at' => $this->updated_at?->utc()
                ->toISOString(),
            // The contract reserves this target for the author-screen slice; no page route exists yet.
            'page_path' => '/adventures/'.$this->id.'/'.(Str::slug($this->title) ?: 'adventure'),
        ];
    }
}
