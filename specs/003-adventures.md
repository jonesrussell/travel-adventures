# 003: Adventure creation and publishing

Status: Draft for the full stage; private create/list/view contract designed and validated on 2026-10-07. No product implementation authorized by this planning pass.

## Stage and slices

This stage refines first-release criteria A02–A06, A10 and A11. Design the whole text-adventure lifecycle, then implement private create/view first when authorized. Photos and reels are deferred to their required MVP milestones, not removed from MVP.

1. Specification and substantive OpenAPI contract, before endpoint code.
2. Private draft creation, owner list and owner detail.
3. Owner editing/deletion, preview and publish/unpublish.
4. Public feed, published detail and author profiles.

## Established requirements

- Server-assigned ownership (A02).
- Draft visibility limited to owner, including direct URLs (A03).
- Valid title/story and readiness of attached media required for publishing (A04).
- Stable public pagination and published-only profile content (A05).
- Owner-only mutation (A06).
- Accessible forms and meaningful loading/empty/error feedback (A10).
- Designed OpenAPI operations and behavior tests (A11).

## Agreed behavior and proposed technical representation

Product decisions below are agreed unless explicitly labelled Proposed or Open. Technical representation and the contract still require design.

- Owner: Assigned by the server. Never accepted from submitted input.
- Title: every saved draft has a title. Agreed: generate a default when the author leaves it blank; authors can edit it. Agreed: 1–160 characters and date-based default "Adventure · October 7, 2026". Proposed: application timezone.
- Story: Agreed: basic formatting supports headings, bold, italics, lists and ordinary HTTP/HTTPS links. Images and videos remain separate attachments in later milestones. Optional while drafting; required to publish. Agreed maximum: 20,000 visible-text characters; formatting markup does not count. Editor, storage representation and server-side sanitization rules must be designed before implementation; arbitrary HTML/scripts are not permitted.
- Location: Agreed: a simple text field, optional while drafting and required to publish. Maps, coordinates and place lookup are deferred. Agreed maximum: 200 characters.
- Identity [Agreed]: title-based page URLs with a stable numeric ID, such as /adventures/123/banff-weekend. Old title links resolve by ID and redirect to the current canonical URL after authorization/publication checks. Title changes must not break existing links. Proposed: JSON resource URLs remain ID-based.
- Publication: current visibility and original publication date are separate. Agreed: retain the first publication date through unpublishing, trash/restoration and republishing; republishing does not bump feed order. Proposed representation: nullable published_at for current publication state plus nullable first_published_at for stable ordering. Publishing and unpublishing are explicit actions.
- Created / updated: Server-managed timestamps. Owner list ordered by update time plus ID; public feed by first publication time plus ID.

- Travel dates [Agreed]: optional start/end dates. Include fields in the initial adventure migration, create/edit forms, resource and contract; test single-day, range and invalid ordering.

One user owns many adventures. An adventure belongs to one user. Future media will belong to its adventure, but no media tables or jobs are part of the private-draft slice.

Propose session-cookie authentication with CSRF for same-origin mutations, consistent with the foundation. Separate Inertia page delivery from JSON resource operations. Propose versioned `/api/v1/adventures` operations; final paths, errors, payloads and authentication examples must be designed in `contracts/openapi.yaml` before implementation. contracts/openapi.yaml now designs private draft create, owner list and owner detail only; all operations remain unimplemented.

Propose unauthenticated JSON access returns 401; authenticated attempts to inspect or mutate another author's draft return 404 to avoid existence disclosure. Validation returns 422. CSRF/session-expiry behavior and authentication/verification failures must be specified explicitly before the contract is Ready. Never rely on opaque identifiers for authorization.

Public reads require current published state. Propose unpublishing removes public access immediately. Published edits take effect immediately. Republish retains original feed placement. Repeated publish/unpublish behavior remains to be decided before its slice is Ready. Story-only publication is an intermediate milestone; attached-media readiness remains required when media is introduced.

## Decision register

- Decision scope: Russell accepted the recommendations already presented in this stage discussion on 2026-10-07. This does not approve future recommendations, unspecified dependencies or product implementation.

- Early permanent deletion [Agreed]: the owner may permanently delete an adventure from Trash before 30 days, after an explicit irreversible-deletion warning and confirmation. No restoration is possible afterward. Future media cleanup must be retry-safe.

- Story links [Agreed]: allow ordinary http:// and https:// links; reject unsafe link types. Validate links and supported formatting server-side, including requests that bypass the editor.

- Publication confirmation [Agreed]: show a preview and explicit Publish publicly confirmation before publishing. Saving drafts never publishes. Confirm the latest saved content and enforce verification, username and publication validation server-side.

- Search [Agreed]: defer title/location search until the core publishing flow works. No search UI, query contract or search infrastructure in this stage.

- Public home [Agreed]: the published adventure feed replaces the starter welcome page at /. Header provides sign-in and registration links. Signed-in visitors have an entry to My adventures.

- Author home [Agreed]: replace the starter dashboard with My adventures, with Drafts, Published and Trash views, a New adventure button and recently edited items first. Unverified authors can access their draft workspace. Trash remains owner-only.

- Public profile scope [Agreed]: display name, username and published adventures only. Bio, avatar uploads and social links are deferred. Never expose email, drafts or trash.

- Public feed [Agreed]: newest first by original publication date, 12 adventures per page, with a Load more button rather than infinite scrolling. Proposed: cursor pagination with numeric ID tie-breaker; contract must define cursor and end-of-results behavior.

- Travel dates [Agreed]: optional start date and optional end date for multi-day trips, separate from publication timing. Proposed validation: an end date requires a start date and cannot precede it; date-only values without timezones. Agreed: future travel dates are allowed in private drafts; publishing requires all supplied travel dates to be today or earlier. The same restriction applies to saved edits of published adventures. Proposed: use the application timezone for today.

- Adventure links [Agreed]: readable title slug plus stable ID; old slugs redirect to the current page only when the viewer has access. Private/trashed content must not leak through redirects.

- Draft saving [Agreed]: autosave private drafts after a short pause, show Saving/Saved feedback and retain a manual Save button. Published edits use explicit Save because saved changes appear publicly immediately. Proposed: debounce interval, failed-save retry behavior and concurrent-edit protection must be specified before implementation.

- Feed placement [Agreed]: retain original publication date when republishing; edits, unpublish and restore do not reset it. Current public visibility is tracked separately.

- Content limits [Agreed]: title 160 characters, location 200 characters and story 20,000 visible-text characters. Formatting markup does not count toward story length.

- Story formatting [Agreed]: headings, bold, italics, lists and ordinary HTTP/HTTPS links; media stays separate. Editor/storage choice and safe rendering are open implementation design decisions.

- Location [Agreed]: simple text field; maps and place lookup deferred.

- Draft title [Agreed]: a saved draft requires a title, with an automatically generated editable default when the author does not supply one.
- Default title format [Agreed]: generate "Adventure · October 7, 2026" using the creation date when title input is blank. Titles remain editable and need not be unique. Proposed: use the application timezone consistently; per-author timezone settings are deferred.
- Draft completeness [Agreed]: story and location may remain unfinished in drafts; both are required before publishing.
- Visibility [Agreed]: MVP supports private drafts and public published adventures. Unlisted and followers-only sharing are deferred.
- Author verification [Agreed]: authenticated authors may create, view and edit private drafts before email verification. Publishing requires verified email. The starter dashboard currently requires verification, so the owner-draft entry point must support unverified authors.
- Published editing [Agreed]: saved edits take effect immediately on published adventures. Authors can unpublish first for private editing. No separate revision workflow. Published saves must continue to satisfy publication requirements; incomplete changes are rejected unless the author unpublishes first.
- Content deletion [Agreed]: deleting an adventure moves it to owner-only trash for 30 days. The owner may restore it before permanent deletion. Trashed adventures are excluded from public and normal owner lists. Permanent cleanup after expiry must be retry-safe, including future media. Agreed: restoration always returns the adventure as a private draft, including previously published adventures; republishing requires an explicit publish action.
- Profile identity [Agreed]: each author has a unique public username alongside an editable display name. Public profile links use the username; email addresses remain private. Agreed: authors choose their username before their first publish; registration and private drafting do not require it. Agreed: the public username is fixed once chosen for the MVP; display name remains editable. Agreed format: 3–30 characters using lowercase letters, numbers and underscores. Usernames are unique; admin and support are reserved. Any additional reserved names must be specified before username implementation.
- Public launch audience [Open]: Invited pilot is proposed; no launch audience confirmed.

## Observable acceptance proposals

- An authenticated permitted author saves a draft and reopens it from their owner list (A02).
- Blank title input yields an automatically titled draft; an explicit title is preserved and the title can later be edited (A02, A10).
- Submitted owner IDs cannot assign or transfer ownership (A02, A06).
- A guest or different author cannot inspect a draft by URL or JSON request (A03).
- Invalid input leaves useful field errors and does not create a record (A10, A11).
- An owner edits their adventure; another account cannot edit or delete it (A06).
- A publish request missing required content fails without exposing the draft (A04).
- A published story appears publicly; unpublishing removes public access (A03–A05).
- Public feed/profile results never include drafts and pagination uses deterministic tie-breaking (A05).
- Public responses contain no email or private account fields (A03, A06).
- Implemented JSON payloads and failure cases match the designed contract (A11).

## Trash acceptance and PR impact

- Deleting a published adventure immediately removes public access and moves it to owner-only trash.
- Only its owner can view or restore it during the 30-day retention period.
- Restoration clears publication state and returns a private draft; no public access resumes until explicit publishing.
- The owner can permanently delete a trashed adventure early after an explicit irreversible-deletion confirmation. Other accounts cannot perform this action.
- Expired trash is not restorable and is permanently removed through retry-safe scheduled cleanup.
- Proposed files now include a trash/restore controller, trash page, retention migration fields, cleanup command, scheduled command registration and focused deletion/restore/expiry tests. The contract must describe deletion as trashing and specify restoration before endpoints.

## Story formatting PR impact

- Proposed conditional addition: resources/js/components/adventures/StoryEditor.vue for reusable accessible formatting controls.
- Proposed conditional addition: resources/js/components/adventures/StoryContent.vue for consistent safe preview/public rendering.
- Contract must specify the chosen story representation. Tests must cover supported formatting, rejection or removal of unsafe content and content that appears nonempty but contains no visible story.
- Any editor dependency requires the repository dependency approval process; Tiptap is selected; no package installed during planning.

## Author identity PR impact

- Proposed addition: database/migrations/<timestamp>_add_username_to_users_table.php; define uniqueness, normalization and existing-user handling before migration implementation.
- Modify app/Models/User.php and relevant profile validation/settings UI. Registration does not require username selection. Publishing requires a chosen username; provide an author-profile setup entry point before first publishing.
- Public profile routing resolves the username; contract/resources expose only intended public identity. Tests cover duplicate usernames, private email exclusion and rejection of username changes after initial selection.

## Autosave acceptance and PR impact

- Draft changes autosave after a pause; manual save remains available.
- Failed saves never display Saved and retain unsaved text for retry.
- Publish must not race an outstanding draft save or publish stale content.
- No autosave silently updates already published content.
- Proposed conditional addition: resources/js/composables/useAdventureAutosave.ts; update draft request/controller/contract coverage for saves and browser coverage for pending/failed save feedback. Reuse existing update operations rather than invent an autosave-only endpoint without need.

## URL acceptance

- Published adventure page links include stable ID and title slug.
- After renaming, a previously shared authorized link redirects to the current canonical URL.
- Guests and other accounts cannot learn draft titles from redirects or canonical metadata.
- Trashed content remains unavailable publicly regardless of a valid old URL.

## Selected technical design

Agreed on 2026-10-07 under Russell's authorization to select recommendations. These choices supersede earlier Proposed/Open technical annotations in this document. No product code or dependencies have been installed. Resolve and lock compatible package versions during authorized implementation.

### Story

Use open-source Tiptap Vue 3 with a restricted schema and PostgreSQL jsonb storage. Store a versioned JSON document, not submitted HTML. Allow paragraphs, text, hard breaks, h2/h3 headings, ordered/bullet lists and list items; marks are bold, italic and HTTP/HTTPS links. Disable other formatting. Laravel validates nodes, attributes and URLs; reject unknown/unsafe content with 422. Render approved nodes as explicit Vue elements with escaped text, without arbitrary v-html. Limit serialized story JSON to 256 KiB, depth 16 and 5,000 nodes as well as 20,000 visible-text Unicode code points. Count text plus one newline between blocks; reject whitespace-only publication. No paid editor service.

### Autosave

Debounce private draft saves for 1 second. Keep one request in flight and coalesce later changes. Create on the first content change or manual Save, with a client UUID creation key unique per owner to prevent duplicate drafts on uncertain retries. Mutations carry an integer version; compare and increment atomically. Return 409 on stale versions, preserving local writing for comparison/reload. Never silently overwrite. Failed saves retain writing in memory and offer Retry; no automatic retry for validation/authentication/conflict failures. Publish flushes saves and confirms the latest version; changes after preview require reconfirmation. Persistent browser recovery and collaborative editing are deferred.

### Lifecycle and trash

Use nullable published_at for current visibility, immutable first_published_at for original feed placement and deleted_at for trash. Unpublish/restore clear published_at without resetting first_published_at. Transactional row locks serialize transitions. Repeated publish/unpublish against the current version succeeds without changing dates or incrementing version when already in that state; stale versions return 409. Trashed items cannot be published or normally edited.

Trash retention is exactly 30 times 24 hours from deleted_at in UTC. Restore is allowed strictly before expiry; expired items return 404 even before physical cleanup. Purge hourly without overlapping executions, in bounded batches, rechecking expiry under row locks. Restore and early permanent deletion use the same locking rules. Repeated cleanup of missing rows succeeds. Early permanent deletion requires explicit irreversible confirmation. Future media cleanup must be designed in its milestone, not scaffolded here.

### Dates and usernames

Keep the existing UTC application timezone for generated title dates and publication's today checks. Travel dates are YYYY-MM-DD calendar values. End requires start and cannot precede it. Generate the default title once on creation, not each save. Per-author timezones remain deferred.

Trim and lowercase usernames before ASCII validation and database uniqueness checks. Reserve admin, administrator, support, help, system, root, api, auth, login, logout, register, settings, dashboard, adventures, authors, trash and me. Existing users have null username until selection before first publish; never derive identity from email. Lock the user row for initial selection so concurrent requests cannot change a fixed username.

### JSON contract and pagination

Register /api/v1 JSON routes with web session/CSRF middleware explicitly, rather than stateless default API middleware. No bearer-token dependency. Require Accept: application/json. Public reads allow guests; owner operations require authentication and policies. Design errors: 401 unauthenticated; 403 verification required or origin rejection; 404 private/unavailable/expired content; 409 stale version; 419 CSRF mismatch; 422 invalid fields or missing username. Use Laravel resource envelopes and field errors.

Public cursor ordering: first_published_at DESC, id DESC, 12 items, published and nontrashed only. Owner ordering: updated_at DESC, id DESC, 12 items. Null next cursor means end. The substantive OpenAPI contract must specify exact operations, payloads, error examples, cursor validation and creation-key behavior before code.

### Shared logic and tests

Thin Inertia and JSON controllers share focused creation/update/lifecycle actions. Policies govern ownership; Form Requests and a dedicated story validator govern input. Avoid additional architecture layers. Use Pest behavior/authorization tests and PostgreSQL integration tests for JSON, uniqueness and concurrency. Choose @playwright/test for actual autosave/publishing/trash browser flows, with Chromium on PRs and Firefox/WebKit plus narrow layouts for staging validation. Retain fast SQLite starter tests; add a PostgreSQL 17 CI service for integration coverage. Local browser tests use Herd; CI can start an isolated test server.

### PR forecast adjustments

- package.json and package-lock.json: Tiptap packages and Playwright dev dependency at implementation time.
- StoryEditor.vue and StoryContent.vue under resources/js/components/adventures: editor and safe JSON display.
- app/Rules/StoryDocument.php: bounded schema, URL and visible-text validation.
- app/Actions/Adventures/: shared mutation logic as needed per slice.
- Adventure migration: JSON story, integer version, owner-scoped unique creation key, timestamps, trash state and query indexes.
- bootstrap/app.php and routes/api.php: explicit session/CSRF JSON route registration.
- playwright.config.ts and tests/Browser/*.spec.ts: browser coverage, replacing the earlier PHP browser-path forecast.
- .github/workflows/checks.yml: PostgreSQL integration and browser checks.

### First-slice contract boundary

The private create/list/view slice is design-ready. It does not yet implement autosave updates: update/version-conflict operations must extend the contract before the next slice. Likewise publication, usernames, trash and public reads remain later implementation slices. Creating on first form change/manual save is covered by create; autosave feedback must not imply subsequent edits persist until update support exists.

- POST /api/v1/adventures: 201 new draft, 200 identical-key replay, 409 different normalized input for the same key. Creation keys are owner-scoped and retained for the row lifetime.
- GET /api/v1/adventures: owner drafts only, fixed 12-item cursor page ordered by updated_at and ID descending. Editing may move rows in live pagination; clients deduplicate IDs rather than claim snapshot consistency.
- GET /api/v1/adventures/{adventure}: owner draft detail; nonowned/trashed/missing IDs return identical 404.
- Session cookie is travel-adventures-session by default. Default Laravel forgery protection uses same-origin HTTPS checks with token fallback; HTTP Herd requires a valid CSRF token. No origin-only customization is selected.
- Request body limit is 512 KiB; story JSON separately limited to 256 KiB. Unknown request fields are rejected, including ownership/publication inputs.
- Empty story is a document with one empty paragraph. Tree validation adds semantic nesting/node/visible-text limits beyond OpenAPI.
- Draft responses omit email, ownership IDs, creation keys and other internal/private account data. Publication fields remain null.

### Acceptance coverage required at implementation

A02: create with generated/custom title, authenticated unverified owner, initial version 1, repeat/concurrent creation key behavior. A03/A06: owner isolation, guests, unavailable IDs and no private account leakage. A10/A11: formatting boundaries/unsafe links, length/tree/body limits, unknown ownership input, travel-date validation, session/CSRF failures, cursor validation, deterministic tie ordering and empty/final pages. PostgreSQL verifies JSON storage and unique owner-scoped creation keys. No such endpoint tests have run yet.

### Sources consulted

- https://tiptap.dev/docs/editor/getting-started/install/vue3
- https://tiptap.dev/docs/guides/output-json-html
- https://github.com/ueberdosis/tiptap/blob/main/LICENSE.md
- https://playwright.dev/docs/browsers
- Laravel 13 package-aware Boost documentation: cursor pagination, session routes, CSRF and scheduling.

Launch audience remains a later-stage decision.

## Ready gate

### Storage implementation boundary

Step 3 adds storage and owner policies only. User deletion is restricted while adventure rows exist, including trash, so a future account-deletion workflow must explicitly handle adventure retention and media cleanup. Creation-key uniqueness is stored per owner; replay handling and title generation belong to the next endpoint slice.

Private draft create/list/view is Ready for implementation: data, permissions, substantive OpenAPI schemas, failure examples and acceptance coverage are designed. The full creation/publishing stage remains Draft for later contract extensions. Browser coverage enters with actual autosave/publishing flows. Contract lint passed through Redocly directly and npm; one initial Windows Node shutdown assertion was not reproduced on later runs. Storage and owner policies are implemented; eight SQLite behavior tests and two PostgreSQL transaction tests pass. The additive migration is applied to the WSL PostgreSQL database and verified through Boost. No product endpoints exist yet. Remaining PR file paths are forecasts. Work is paused after step 3 on codex/adventure-draft-storage, with planning baseline f504a16 committed.

## Readable planner

See `docs/adventure-planner.html` for stage scope, journey, permissions, decisions and expected PR file paths. This specification remains the behavioral planning source; the planner presents it in readable form.
