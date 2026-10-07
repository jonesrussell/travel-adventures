# Architecture direction

Status: proposed foundation, subject to runtime verification.

- Laravel application with a Vue/TypeScript frontend and explicit JSON API.
- A modular monolith, keeping accounts, adventures and media separated internally without introducing distributed services.
- Relational database; PostgreSQL is the proposed default. Confirm local and deployment support during foundation work.
- Queue workers for media processing; object storage interface for media. Select concrete providers later.
- WSL Ubuntu and Docker Engine for local development.
- Laravel Boost development dependency and Codex MCP integration.
- OpenAPI as the designed API contract, validated in CI with behaviour tests against implemented endpoints.

Decide the frontend integration and authentication model in foundation work. A same-origin web app can use session authentication; do not introduce bearer tokens solely because the retired mobile prototype used them. Keep API design usable by a future mobile client without promising one now.

Media lifecycle: uploaded privately, validated, processed, ready or failed, then exposed only when its adventure is published. Removal must clean up derivative files and avoid orphaned storage. Repeated jobs must not duplicate media or corrupt state.

Public launch requires infrastructure and recovery decisions beyond local Docker. Do not reuse unrelated live services or credentials by assumption.
