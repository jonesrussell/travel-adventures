# Local development

The application runs in native Windows Laravel Herd with isolated PHP 8.5. PostgreSQL runs in WSL Ubuntu Docker Engine, exposed only at 127.0.0.1:48192. Docker Desktop and Herd Pro are unnecessary. No database was moved to Windows. Keep the existing PostgreSQL volume.

## Windows application

Run PowerShell in `C:/dev/travel-adventures`. Install Herd and finish its onboarding first. Ensure Herd's bin directory is on PATH; restart the terminal after installation. If this terminal predates installation, this local session can use:

```powershell
$env:Path = "$env:USERPROFILE/.config/herd/bin;" + $env:Path
herd php -v
herd isolated
```

For a new checkout, install locked dependencies, create a local environment and link/isolate this site:

```powershell
herd composer install
Copy-Item .env.example .env
herd php artisan key:generate --no-interaction
herd link travel-adventures
herd isolate 8.5
npm ci
herd php artisan migrate --no-interaction
herd php artisan wayfinder:generate --with-form --no-interaction
npm run build
```

Do not overwrite an existing `.env` or regenerate its key when resuming this checkout. Set APP_URL to `http://travel-adventures.test`, DB_CONNECTION to `pgsql`, DB_HOST to `127.0.0.1` and DB_PORT to `48192`. Preserve existing database credentials. Example credentials are local development placeholders only. Herd serves HTTP; do not start an Artisan HTTP server. Native Windows Node/npm supplies frontend dependencies. Reinstall with `npm ci` when switching from Linux-installed dependencies.

## WSL database

Run these from Windows while Docker Engine in WSL Ubuntu is available:

```powershell
wsl -d Ubuntu --cd /mnt/c/dev/travel-adventures -- docker compose up -d pgsql
wsl -d Ubuntu --cd /mnt/c/dev/travel-adventures -- docker compose ps -a
wsl -d Ubuntu --cd /mnt/c/dev/travel-adventures -- docker compose stop pgsql
```

Stopping PostgreSQL makes the application unavailable until restarted. These commands retain its volume. Never use `down -v` for routine shutdown.

## Checks

```powershell
herd composer validate --strict
herd composer check-platform-reqs
herd php artisan migrate:status --no-interaction
herd php artisan wayfinder:generate --with-form --no-interaction
herd composer test
npm run check
npm run types:check
npm run build
npm run api:lint
```

Composer's test script includes Pint checking, Larastan and Pest. Quality tools use `@php vendor/bin/...` to avoid missing Windows executable proxies. Tests use SQLite for isolation; PostgreSQL migration status and database access are verified separately. New behavior tests use Pest; upstream class-based tests retain starter coverage.

## Laravel Boost

Boost runs through the actual isolated Herd PHP executable, not Sail. The ignored `.codex/config.toml` points to that executable, this checkout's Artisan file and working directory. Regenerate configuration with `herd php artisan boost:install` only when agent setup changes, then review machine-specific paths. A new Codex session may need MCP reload.

The optional verifier accepts the selected PHP executable and resolves its working directory from its own location:

```powershell
python scripts/verify-boost.py --php "$env:USERPROFILE/.config/herd/bin/php85/php.exe"
```

It verifies MCP initialization, application-info and URL resolution. A read-only Boost database query separately verifies database connectivity; listing connections alone does not prove reachability. Do not commit local MCP configuration.

## VS Code

The official Laravel extension is `laravel.vscode-laravel`. It is installed locally at version 2.0.1. This checkout's ignored `.vscode/settings.json` explicitly selects Herd and the verified isolated PHP executable using `Laravel.phpEnvironment` and the argv-array `Laravel.phpCommand`. Open this checkout in VS Code to activate Laravel LSP. If the editor was already open, reload its window to apply the runtime settings.

For another checkout, install the official extension if absent and resolve its actual Herd PHP path; do not copy this user's absolute path. Preserve existing editor settings. Workspace settings and recommendations are currently ignored by repository policy. Extension installation and PHP selection have been checked; live editor completion/diagnostic behavior has not been verified in this setup pass.

## Retained Sail fallback

The project-owned PHP 8.5/Node 24 Docker image and Compose application service remain available. The application container is stopped; PostgreSQL remains running. Before deliberately using Sail, temporarily configure DB_HOST=pgsql, DB_PORT=5432 and APP_URL=http://localhost:48190 in the ignored local environment. Restore Herd values before returning to Herd. Avoid simultaneous runtime use with conflicting environment settings.

From WSL `/mnt/c/dev/travel-adventures`, start with `./vendor/bin/sail up -d`, run commands through Sail, then stop only its application using `docker compose stop laravel.test`. Container database hostnames cannot be used by Herd. Do not rebuild Linux node_modules over native Windows dependencies without reinstalling for the target platform.

## API contracts

Redocly validates the private adventure API contract in `contracts/openapi.yaml` through `npm run api:lint`. Pest tests verify implemented responses and failure cases. Author screens, editing, publishing and media processing remain unimplemented.

## Private adventure API checks

The three session-backed operations are POST /api/v1/adventures, GET /api/v1/adventures and GET /api/v1/adventures/{adventure}. Require Accept: application/json; creation requires an uncompressed JSON object and normal web CSRF protection. Existing Fortify pages establish the session. No adventure author pages exist yet; page_path is a future page target.

The API and story tests run in SQLite by default. PostgreSQL checks use the separate local travel_adventures_test database in the existing WSL container. Never point RefreshDatabase tests at the application database. Create the isolated database once with the configured local database role, migrate it, then run:

```powershell
$env:DB_CONNECTION = 'pgsql'
$env:DB_DATABASE = 'travel_adventures_test'
try {
    herd php artisan migrate --no-interaction
    herd php artisan test --compact tests/Feature/AdventureApiTest.php tests/Feature/AdventurePostgresStorageTest.php
    $env:RUN_POSTGRES_CONCURRENCY = '1'
    herd php artisan test --compact tests/Feature/AdventureCreationConcurrencyTest.php
} finally {
    Remove-Item Env:DB_CONNECTION, Env:DB_DATABASE
    Remove-Item Env:RUN_POSTGRES_CONCURRENCY -ErrorAction SilentlyContinue
}
```

Concurrency tests use parallel PHP processes against committed test records, clean up their records and require a database name ending in _test. Default SQLite runs skip the PostgreSQL-only checks. CI has a separate PostgreSQL 17 service and job; its result must be checked after pushing.
