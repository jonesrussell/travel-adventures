# Foundation validation

Date: 2026-10-07. Branch: rebuild/sdd-foundation.

## Earlier Sail foundation evidence

Laravel 13.35.0, Inertia Laravel 3.5.1, Boost 2.10.3, Pest 5.3.0 and Pest Laravel plugin 5.0.1. Exact PHP/JS versions are in lock files. Container runtime: PHP 8.5.11, Node 24.21.0. PostgreSQL 17 image and PHP/Node/Composer base images are pinned to the digests actually tested.

## Earlier Sail checks

- Docker Engine/Compose under WSL: application and PostgreSQL services running. PostgreSQL healthy.
- HTTP GET of the local root: 200, rendered Travel Adventures application name.
- Composer platform requirements inside the container: all satisfied. Composer manifest validates.
- Five starter migrations applied to PostgreSQL through WSL; container migration command then confirmed nothing pending.
- Container Pest suite: 42 tests passed, 143 assertions. Upstream class-based starter tests preserved; new foundation tests use Pest syntax.
- Larastan/PHPStan level 7: no errors with 512 MB memory limit.
- Pint: passed. Frontend lint/format checks: passed.
- Vue type check and production asset build: passed under Node 24 in the container.
- Boost: configured for Codex; stdio MCP initialization, tools/list and application-info succeeded inside the application container, exposing 10 tools. GetAbsoluteUrl and DatabaseConnections also succeeded.
- Redocly CLI 2.60.0 installed. The API guard passed while explicitly skipping contract lint because no product contract exists yet.
- Dependency audit after updating Vite Plus and removing unused concurrently: zero npm vulnerabilities. Composer resolution reported no security advisories.
- npm clean-install dry run: succeeded. Git diff whitespace check: passed.

## Herd transition verification

Verified on 2026-10-07 in native Windows PowerShell using Herd's selected PHP 8.5.11 and Node 24.13.1 / npm 11.8.0.

- Herd isolation identifies travel-adventures.test as PHP 8.5. This terminal needed Herd's bin directory added to its session PATH; no global PHP version was changed.
- Composer manifest validation and installed platform requirements passed.
- PostgreSQL remains healthy in the original WSL container at 127.0.0.1:48192. The Sail application container is stopped. No database relocation or destructive migration occurred.
- Herd migration status confirms all five starter migrations applied. No migration was run in this closure pass.
- Boost MCP tools in this chat returned application information, resolved the Herd URL and executed a read-only PostgreSQL query. PostgreSQL reports 17.11 and five migration records.
- Reviewed ignored local MCP configuration: Herd PHP executable, absolute Artisan path and checkout working directory. Configuration stays ignored.
- The optional Python verifier passed initialization, tools/list, application-info and URL resolution using Herd PHP. It resolves the checkout from the script location and sends stderr directly to the parent instead of leaving an undrained pipe.
- Composer's Windows-compatible @php quality commands passed: Pint, Larastan/PHPStan and Pest (42 tests, 143 assertions).
- Wayfinder generation, frontend lint/format checking and Vue TypeScript checking passed.
- Native Windows production build passed. An informational message notes optional optimized font fallbacks are unavailable without fontaine; no dependency was added.
- API guard passed with an explicit skip: no substantive product OpenAPI contract exists yet. This is not API contract coverage.
- HTTP GET http://travel-adventures.test returned 200 and the application name.
- Existing guidance, example environment and temporary dashboard were updated and formatted. Current transition CI remains pending because these changes have not been committed or pushed.

## Limits and next work

These are local results, not deployment or release readiness. A clean-machine installation and live browser authentication journey were not repeated in this pass; starter authentication behavior is covered by the existing tests. Earlier scaffold CI success does not verify this uncommitted patch.

No adventure endpoints, product contract, media processing or publishing/media browser tests exist. Next: specify the adventure data model, ownership and create/view contract before feature implementation. No paid services or live deployment were provisioned.

The project-owned Sail runtime remains a fallback. Switching back requires the documented container database host/port and a target-platform frontend dependency reinstall. Local machine-specific MCP configuration is ignored; other checkouts must configure their own Herd executable and working directory.
