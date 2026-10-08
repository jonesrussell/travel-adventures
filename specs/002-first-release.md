# 002: First release

Status: Draft

## User journey

An author creates a draft, adds a story and location, attaches photos or a short video reel, previews it and publishes. A visitor browses published adventures. The author can edit or remove their own content.

## Acceptance criteria

- A01: Registration, sign-in and sign-out work with server-side validation and meaningful errors.
- A02: Authenticated authors create drafts. The server assigns ownership.
- A03: Drafts are visible only to their author. Public lists and direct URLs cannot expose drafts or their media.
- A04: Publishing requires valid title/story, a location and all attached media to be ready. Failed processing cannot produce a broken published adventure.
- A05: Feed pagination is stable and adventure detail/profile show published content.
- A06: Editing/deletion requires ownership. Another account cannot modify an adventure or attach/remove its media.
- A07: Photos and reels upload within explicitly specified limits. Invalid content is rejected; processing states and failures are visible to the author.
- A08: Reels have a playable derivative and poster image. Playback works in supported browsers; originals remain private.
- A09: Removing adventures/media cleans up stored assets through a retry-safe process.
- A10: Responsive forms support keyboard use, labelled controls, loading/empty/error states and accessible media controls.
- A11: OpenAPI specifies each implemented operation, authentication, validation/authorization errors and examples. Behaviour tests demonstrate the contract.
- A12: A staging deployment reproduces the complete journey, including queued media processing. No public launch claim before launch readiness.

## Required specifications before feature implementation

Account/authentication contract; adventure lifecycle and schema; upload/processing contract; authorization matrix; UI flows; deployment procedure.

## Open decisions

Audience, public visibility policy, media constraints, supported browsers, hosting/storage and operational ownership. Resolve each before its dependent slice. No numeric media limits are confirmed yet.

## Non-goals

Payments, messaging, recommendation algorithms, native mobile rebuild and trip planning.

## Evidence

Partial evidence: adventure storage and the private draft create/list/view API pass local Pest, PostgreSQL and concurrent retry checks. A02/A03/A11 have API evidence; author screens, publication, media and staged end-to-end evidence remain pending. See specs/003-adventures.md for exact checks and scope.

## Current stage planning

Adventure creation and publishing is being refined in `specs/003-adventures.md` and the readable `docs/adventure-planner.html` sidecar. The private API slice is Verified locally under implementation authorization. The broader publishing stage remains Draft; author screens are next, with later contract extensions still required.
