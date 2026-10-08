<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JsonException;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class RequireAdventureJson
{
    private const MAX_REQUEST_BODY_BYTES = 512 * 1024;

    public function handle(Request $request, Closure $next): Response
    {
        // The API contract requires an explicit JSON response preference, even for browser requests.
        if (! in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return response()
                ->json(['message' => 'Accept application/json is required.'], 406);
        }

        if ($request->isMethod('POST')) {
            // Reject encoded bodies so the raw byte limit also bounds the JSON we decode.
            if (strtolower(trim(explode(';', $request->header('Content-Type', ''))[0])) !== 'application/json'
                || $request->headers->has('Content-Encoding')) {
                return response()
                    ->json(['message' => 'An uncompressed application/json body is required.'], 415);
            }

            // Enforce the 512 KiB transport limit before decoding; story size is validated separately.
            if (strlen($request->getContent()) > self::MAX_REQUEST_BODY_BYTES) {
                return response()
                    ->json(['message' => 'Request body is too large.'], 413);
            }

            try {
                // Object decoding preserves the distinction between a JSON object and an array.
                $body = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return response()
                    ->json(['message' => 'A valid JSON object is required.'], 400);
            }

            if (! $body instanceof stdClass) {
                return response()
                    ->json(['message' => 'A valid JSON object is required.'], 400);
            }
        }

        return $next($request);
    }
}
