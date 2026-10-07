# Development guidance

Use Spec Driven Development (SDD). Read the product brief, roadmap and relevant specification before changing code. Implement only the current milestone. Update the specification before materially changing behaviour.

Laravel Boost is a priority and a foundation acceptance requirement. Install it as a development dependency, configure Codex through its installer, and verify the MCP connection before feature implementation. Consult its package-aware documentation tools when available. Keep project guidance outside generated Boost sections.

OpenAPI is the API contract. Specify operations, schemas, authentication, errors and examples before implementing endpoints. Keep the contract, implementation and tests aligned. Do not treat generated documentation as a substitute for design.

Use Laravel, Vue and TypeScript. Local runtime is WSL Ubuntu with Docker Engine. Do not require Docker Desktop. Resolve framework versions during foundation work using current official documentation and lock dependencies.

Never commit secrets. Public media must be explicitly published. Enforce ownership server-side. Do not accept client-supplied ownership IDs. Verify upload contents and limits; do not trust file extensions. Keep unprocessed video private.

Test observable behaviour, especially authorization, validation, media lifecycle and publication. Report checks actually performed. Do not claim deployment, backups or processing works until verified.

Preserve the prototype tag and unrelated user changes. No production deployment or paid services without a concrete deployment decision. No em dashes in prose to Russell.
