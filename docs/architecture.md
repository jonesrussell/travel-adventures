# Architecture direction

Status: foundation runtime selected and verified locally; product implementation pending.

- Laravel application with a Vue/TypeScript frontend and explicit JSON API.
- A modular monolith, keeping accounts, adventures and media separated internally without introducing distributed services.
- PostgreSQL is the local application database, retained in WSL Docker Engine. Deployment support remains a staging decision.
- Queue workers for media processing; object storage interface for media. Select concrete providers later.
- Native Windows Herd for PHP/HTTP and native Node/npm for assets; WSL Ubuntu Docker Engine for PostgreSQL. Sail remains a stopped application fallback.
- Laravel Boost development dependency and Codex MCP integration.
- OpenAPI as the designed API contract, validated in CI with behaviour tests against implemented endpoints.

Use the official Vue starter kit with Inertia, TypeScript and Fortify session authentication for the same-origin browser application. Explicit JSON adventure endpoints will have OpenAPI contracts. Inertia page payloads are not the public API. Do not introduce bearer tokens solely because the retired mobile prototype used them. Keep API design usable by a future mobile client without promising one now.

Media lifecycle: uploaded privately, validated, processed, ready or failed, then exposed only when its adventure is published. Removal must clean up derivative files and avoid orphaned storage. Repeated jobs must not duplicate media or corrupt state.

Public launch requires infrastructure and recovery decisions beyond local Docker. Do not reuse unrelated live services or credentials by assumption.
