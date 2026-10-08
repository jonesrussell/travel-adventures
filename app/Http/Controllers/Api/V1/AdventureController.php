<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Adventures\CreateAdventure;
use App\Http\Controllers\Controller;
use App\Http\Requests\Adventures\ListAdventuresRequest;
use App\Http\Requests\Adventures\StoreAdventureRequest;
use App\Http\Resources\AdventureResource;
use App\Http\Resources\AdventureSummaryResource;
use App\Models\Adventure;
use Illuminate\Http\JsonResponse;

class AdventureController extends Controller
{
    private const PAGE_SIZE = 12;

    public function index(ListAdventuresRequest $request): JsonResponse
    {
        $page = $request->user()
            ->adventures()
            ->whereNull('published_at')
            // ID breaks timestamp ties so cursor pages have a deterministic order.
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->cursorPaginate(self::PAGE_SIZE, cursor: $request->cursor());

        return response()
            ->json([
                'data' => AdventureSummaryResource::collection($page->items())
                    ->resolve($request),
                'links' => ['prev' => $page->previousPageUrl(), 'next' => $page->nextPageUrl()],
                'meta' => ['per_page' => self::PAGE_SIZE, 'next_cursor' => $page->nextCursor()
                    ?->encode(), 'prev_cursor' => $page->previousCursor()
                    ?->encode()],
            ]);
    }

    public function store(StoreAdventureRequest $request, CreateAdventure $create): JsonResponse
    {
        $result = $create->handle($request->user(), $request->validated());

        return (new AdventureResource($result['adventure']))
            ->response()
            ->setStatusCode($result['created'] ? 201 : 200);
    }

    public function show(Adventure $adventure): AdventureResource
    {
        // This endpoint reopens private drafts; publication is a separate, later operation.
        abort_if($adventure->published_at !== null, 404);

        return new AdventureResource($adventure);
    }
}
