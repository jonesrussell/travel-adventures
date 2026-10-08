<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AdventureResource extends AdventureSummaryResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'story_format_version' => $this->story_format_version,
            'story' => $this->story,
        ];
    }
}
