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

## Retained Sail fallback

The project-owned PHP 8.5/Node 24 Docker image and Compose application service remain available. The application container is stopped; PostgreSQL remains running. Before deliberately using Sail, temporarily configure DB_HOST=pgsql, DB_PORT=5432 and APP_URL=http://localhost:48190 in the ignored local environment. Restore Herd values before returning to Herd. Avoid simultaneous runtime use with conflicting environment settings.

From WSL `/mnt/c/dev/travel-adventures`, start with `./vendor/bin/sail up -d`, run commands through Sail, then stop only its application using `docker compose stop laravel.test`. Container database hostnames cannot be used by Herd. Do not rebuild Linux node_modules over native Windows dependencies without reinstalling for the target platform.

## API contracts

Redocly validates `contracts/openapi.yaml` once the first substantive contract exists. Until then `api:lint` explicitly skips contract lint and rejects an API routes file without a contract. Passing this guard is not endpoint contract coverage. Adventure features and media processing are not implemented.
