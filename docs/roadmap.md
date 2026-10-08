# Roadmap to MVP

Planning baseline: 2026-10-07. Adventure storage and private draft API are verified locally; author screens and later features remain to build. This expands the product brief and specs/002-first-release.md. Proposed choices below are not confirmed requirements.

## Product outcome

An author registers, drafts a story and location, adds photos or a short reel, previews and publishes. Visitors browse published adventures and author profiles. Owners edit, unpublish and delete their content. Photos and reels are required for MVP. A staged MVP demonstrates A01–A12; public launch adds operational readiness.

## Existing foundation

The official Laravel/Vue/TypeScript starter, Fortify and Wayfinder are installed. Herd PHP 8.5.11, 42 passing tests and 143 assertions were reverified during runtime closure; see foundation-validation.md. PostgreSQL remains in WSL Docker Engine at 127.0.0.1:48192; no database relocation occurred. The prototype tag is prototype-before-rebuild-2026-10-07. The runtime transition and adventure storage are merged into main. Active-runtime docs describe Herd and the WSL database. The private adventure API contract and endpoints are implemented and locally verified on codex/private-adventure-api. Media processing remains to build.

## 0. Close the runtime transition

Status: Verified locally; current transition CI pending. Dependency: existing foundation. Acceptance: F01–F07.

Review Herd documentation, Composer @php scripts, Boost verifier and ignored MCP configuration. Keep PostgreSQL in WSL and preserve Sail fallback.

- Update existing runtime documentation
- Review Windows scripts and local MCP configuration
- Resolve formatting and regenerate Wayfinder
- Run frontend checks, backend checks and build
- Verify Herd and Boost database access
- Record evidence and review CI after authorized delivery

Exit: Documented setup and checks agree with the actual Herd/WSL runtime.

## 1. Specify the first adventure slice

Status: Ready. Dependency: stage 0. Acceptance: A02, A03, A06, A11.

Design the adventure model, authorization matrix, create/view UI and substantive OpenAPI contract before endpoints.

- Define fields, identifiers, limits and owner relationship
- Define draft visibility and guest/owner/other-user permissions
- Specify create form, owner detail and paginated list
- Design JSON schemas, authentication, CSRF, errors and examples
- Validate contracts/openapi.yaml

Exit: A Ready specification and valid contract cover success and failure behavior.

## 2. Create and view private adventures

Status: Implementing. Private API verified locally; author screens pending. Dependency: stage 1. Acceptance: A02, A03, A06, A11.

Deliver a complete authenticated create/view draft flow through UI and designed JSON endpoints.

- Implement server-assigned ownership and validation
- Build create form, saved draft detail and owner dashboard
- Test guests, other accounts and draft isolation
- Test endpoint contract behavior
- Verify relevant database behavior on PostgreSQL

Exit: An author creates and reopens a private draft; ownership and contract checks pass.

## 3. Edit, publish and browse

Status: Draft. Dependency: stage 2. Acceptance: A03–A06, A10, A11.

Specify then implement editing, deletion, preview, publish/unpublish, public feed, detail and author profiles.

- Define published editing and future media-readiness rules
- Implement owner editing and deletion
- Implement preview, publish and unpublish
- Build stable paginated feed and public profiles
- Test publishing in browser and private URL isolation

Exit: A visitor browses a published story while drafts stay private. Media-complete MVP remains pending.

## 4. Photos

Status: Draft. Dependency: stage 3. Acceptance: A03, A04, A06, A07, A09–A11.

Specify bounded content-validated uploads, private originals, derivatives, ordering and cleanup.

- Choose format, size, dimension and count limits
- Define cover, ordering, alt text and retention
- Implement private uploads and processing states
- Implement derivatives, preview and retry-safe removal
- Verify unpublished access and cross-account rejection
- Browser-test valid and invalid uploads

Exit: A photo adventure publishes successfully; private access and storage cleanup are verified.

## 5. Short video reels

Status: Draft. Dependency: stage 4. Acceptance: A03, A04, A06–A11.

Specify and deliver queued private source processing, posters, playable derivatives and accessible playback.

- Choose duration, size, resolution and source formats
- Define supported browsers, metadata, timeouts and retries
- Run representative processing experiment
- Implement explicit processing states and author feedback
- Block publication until media is ready
- Test corrupt input, duplicate jobs and deletion during processing
- Verify real browser playback and private originals

Exit: A real reel processes and plays; retries and deletion cannot revive or expose private media.

## 6. Staged MVP

Status: Draft. Dependency: stage 5. Acceptance: A01–A12.

Complete responsive and accessible product flows and demonstrate the entire journey on staging with real media workers.

- Complete loading, empty, error and retry states
- Verify account recovery, verification and deletion with owned assets
- Choose hosting, storage, mail, workers and budget before provisioning
- Verify staging journey with real uploads
- Verify deployment, queue restart and rollback
- Record evidence for every A01–A12 criterion

Exit: All first-release acceptance criteria have evidence. Ready for a controlled pilot, not unrestricted public launch.

## 7. Public launch readiness

Status: Draft. Dependency: stage 6. Acceptance: Launch gate.

Deliver moderation, privacy, abuse controls, monitoring and demonstrated recovery with a named operator.

- Assign moderation and incident ownership
- Implement reporting and takedown controls
- Define privacy, retention and deletion policies
- Set upload quotas and rate limits
- Configure application and worker monitoring
- Restore database and media backups
- Exercise rollback and failed-worker recovery

Exit: Named operators can handle abuse, failures and recovery through verified procedures.

## Decision register

All starting positions are provisional; resolve each before its dependent slice.

| Decision                        | Needed before | Proposed starting position                      |
| ------------------------------- | ------------- | ----------------------------------------------- |
| Initial audience                | Stage 1       | Small invited pilot                             |
| Visibility                      | Stage 3       | Private drafts, public published adventures     |
| Location                        | Stage 1       | Text location; maps/geocoding deferred          |
| Published editing               | Stage 3       | Immediate edits with explicit unpublish         |
| Media limits                    | Stages 4/5    | Set numeric limits after representative testing |
| Browser support                 | Stage 5       | Named desktop/mobile browser matrix             |
| Hosting/storage/mail and budget | Stage 6       | Choose after media requirements are known       |
| Moderation/incident owner       | Stage 7       | Named person and response procedure             |

## Delivery discipline

Each slice follows Draft → Ready → Implementing → Verified. Ready requires relevant decisions, observable acceptance criteria and designed contracts. Verified requires recorded checks and observed behavior. Update the specification before materially changing behavior; implement only the current milestone. Stage 0 uses specs/001-foundation.md; product slices refine specs/002-first-release.md with focused specifications before implementation.

Keep payments, messaging, maps, trip planning, recommendations, native mobile rebuilding, follows, reactions and saved adventures outside MVP. Estimate dates after the first complete adventure slice and a representative reel-processing experiment. No paid services or public deployment without a concrete deployment decision. Commit/push only within authorized delivery scope.

The temporary [dashboard tracker](mvp-tracker.html) is a read-only snapshot for glancing at current scope, milestones and release gates. Maintain it alongside the roadmap; it does not verify work or change specification status. This Markdown roadmap is the planning source of truth.
