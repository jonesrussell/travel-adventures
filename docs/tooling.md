# Tooling decisions

## Foundation

- Laravel 13, PHP 8.5: supported framework/runtime resolved through Composer.
- Vue 3, TypeScript, Inertia 3: official starter kit integration.
- Fortify: session authentication and starter account scaffolding.
- Wayfinder: generated TypeScript route functions.
- Laravel Boost: Codex guidelines, skills and MCP application context.
- Pest 5 with Laravel plugin: behaviour tests, retaining upstream class-based tests.
- Larastan/PHPStan: level 7 static analysis, 512 MB memory limit.
- Pint: PHP formatting.
- Vite Plus: starter frontend formatting, lint and builds.
- Redocly CLI: OpenAPI validation when the substantive contract is introduced.
- PostgreSQL 17: local relational database.
- Herd: native Windows application runtime with isolated PHP 8.5.
- WSL Docker Engine: PostgreSQL service; retained Sail PHP 8.5/Node 24 image is an application fallback.

Exact PHP and JavaScript package versions live in the committed lock files. Versions above describe selected majors, not promises to upgrade automatically.

## Add at the relevant milestone

- Browser tests for publishing, upload failure states and media playback.
- Queue jobs and workers for photo/reel processing; database-backed queues initially unless measured needs justify Redis/Horizon.
- FFmpeg processing and poster generation, with media storage access controls and cleanup.
- Reporting/moderation, logging/error monitoring and verified restore procedures before public launch.

Herd is the verified local application runtime. PostgreSQL remains in WSL; Herd Pro is not required. Hosting, object storage, mail delivery and monitoring vendors are not selected. Boost is development tooling, not a runtime AI feature or paid model integration.

## References

- https://laravel.com/framework/docs/13.x/starter-kits
- https://laravel.com/framework/docs/13.x/boost
- https://pestphp.com/docs/installation
- https://www.viteplus.dev/guide/local-cli

These were checked on 2026-10-07; dependency locks and actual checks are authoritative for the installed application.
