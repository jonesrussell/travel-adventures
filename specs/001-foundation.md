# 001: Development foundation

Status: Draft

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

Pending implementation. Boost installation reference: https://laravel.com/framework/docs/boost
