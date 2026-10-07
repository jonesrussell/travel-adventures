# 001: Development foundation

Status: Verified locally

## Foundation decisions

Use the official Laravel 13 Vue starter kit with Vue 3, TypeScript and Inertia. Browser authentication uses Fortify session cookies and CSRF protection. Future JSON adventure endpoints will be explicitly specified in OpenAPI; Inertia page responses are not the public API contract. PostgreSQL is the local application database. Sail uses WSL Docker Engine; Herd is deferred. PHP 8.5 and Node 24 are container runtime targets, subject to dependency resolution. The starter kit's account screens and tests are scaffolding, not completion of the product account specification.

OpenAPI tooling: Redocly CLI validation in CI, with behavioural endpoint contract tests added alongside each implemented API slice. No placeholder public API endpoints will be invented for foundation work.

Testing and quality: Pest with its Laravel plugin; preserve starter coverage during conversion. Larastan/PHPStan, Pint, Vue TypeScript checks and Wayfinder are part of the foundation. Browser tests are introduced with actual product flows. Media uses queued, retry-safe processing in the media milestone.

Runtime detail: use a project-owned PHP 8.5/Node 24 image with PostgreSQL and process-control extensions, accessed through Sail. The broad default Sail image was replaced after slow Ubuntu mirror retries exposed unnecessary image dependencies. Add media processing dependencies with their feature specification.

## Outcome

A reproducible Laravel/Vue development environment with Boost available to Codex and contract validation in place.

## Acceptance criteria

- F01: A clean checkout starts under WSL Ubuntu with Docker Engine using documented commands. Docker Desktop is unnecessary.
- F02: Framework, PHP and Node versions are verified against official compatibility guidance and dependency versions are locked.
- F03: Laravel Boost is installed using `composer require laravel/boost --dev`, configured through `php artisan boost:install`, and its Codex MCP connection is verified with a real application-info or documentation request.
- F04: A database migration and a simple application test run successfully in the documented environment.
- F05: CI runs backend/frontend checks and validates the substantive OpenAPI contract once introduced. Choose and document tooling before adding the contract.
- F06: Example environment configuration contains no secrets. Generated agent configuration is reviewed for machine-specific paths and unsafe exposure.
- F07: Choose and record frontend integration and web authentication model before implementing accounts.

## Tasks

Inspect local tools; select supported versions; scaffold; configure runtime/database; install and verify Boost; configure CI; document commands and evidence.

## Non-goals

Feature implementation, media infrastructure provisioning, production deployment, paid service setup.

## Evidence

Validated on 2026-10-07. See docs/foundation-validation.md for results and limits. Boost installation reference: https://laravel.com/framework/docs/boost
