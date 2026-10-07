# Local development

Run commands in WSL Ubuntu from `/mnt/c/dev/travel-adventures`. Docker Engine must be running. Herd and Docker Desktop are not required.

Requirements: WSL PHP 8.5 and Composer for the initial dependency install, Docker Engine/Compose, and Python 3 for the optional MCP verification script. Sail commands use the project's small PHP 8.5/Node 24 Dockerfile rather than Sail's broad default image. The development user is UID/GID 1000, matching this WSL account. Adjust that image user if using another account. Media processing extensions will be added with the media specification.

## Bootstrap

```sh
composer install
cp .env.example .env
php artisan key:generate
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

Local HTTP is intended for `http://localhost:48190`; PostgreSQL binds only to local port 48192. Local database credentials in the example configuration are development-only. Never use them on a public host.

## Checks

```sh
./vendor/bin/sail composer test
./vendor/bin/sail npm run check
./vendor/bin/sail npm run types:check
./vendor/bin/sail npm run build
./vendor/bin/sail npm run api:lint
```

The scaffold contains standard account/dashboard screens. Product adventure flows have not been implemented. CI uses SQLite for isolated behaviour tests; local migration verification uses PostgreSQL.

## Laravel Boost

Boost is a development dependency. Run `php artisan boost:install` when changing the agent setup. Generated machine-specific MCP configuration stays local. Run Boost through WSL in this checkout. Verify application-info before relying on its context. The installation config and generated project guidance are reviewed alongside the SDD guidance.

The local Windows Codex configuration uses `wsl.exe -d Ubuntu --cd /mnt/c/dev/travel-adventures -- bash -lc "./vendor/bin/sail artisan boost:mcp"`. This runs Boost inside the application container so database tools can reach PostgreSQL. On another checkout, regenerate and adjust the working directory. With Sail running, `python3 scripts/verify-boost.py` verifies the stdio handshake and an application-info call. A running Codex session may need its project MCP connection reloaded before exposing the tools directly.

Pest 5 is the test runner. New tests use Pest syntax; upstream PHPUnit-style test classes remain compatible and retain starter coverage. PHP static analysis uses a 512 MB limit. The local PostgreSQL volume is persistent; stopping services does not delete it.

## API contracts

Redocly validates `contracts/openapi.yaml` once the first substantive contract is introduced. Before then, `api:lint` explicitly reports that it is skipped and fails if an API routes file appears without a contract. Passing that foundation guard is not contract test coverage.
