# Foundation validation

Date: 2026-10-07. Branch: rebuild/sdd-foundation.

## Installed foundation

Laravel 13.35.0, Inertia Laravel 3.5.1, Boost 2.10.3, Pest 5.3.0 and Pest Laravel plugin 5.0.1. Exact PHP/JS versions are in lock files. Container runtime: PHP 8.5.11, Node 24.21.0. PostgreSQL 17 image and PHP/Node/Composer base images are pinned to the digests actually tested.

## Evidence

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

## Limits and next work

These are local results, not proof of public deployment or release readiness. GitHub CI is configured to repeat checks after pushing; its result must be read separately.

No adventure endpoints, OpenAPI product contract, media processing or product browser-flow tests exist yet. Account/dashboard UI is official starter scaffolding. Core account/adventure behaviour must be specified before product work. No paid services or live deployment were provisioned.

The broad default Sail image was cancelled after prolonged Ubuntu mirror retries. The project's smaller runtime built successfully and all container checks above used that replacement. First-build network availability remains a dependency; subsequent builds reuse Docker cache.

Local MCP configuration is machine-specific and ignored. Another checkout must install Boost and configure its own WSL/Sail command. The current chat's dynamically exposed tools were not reloaded; MCP protocol calls were independently verified.
