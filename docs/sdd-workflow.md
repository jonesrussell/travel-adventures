# Spec Driven Development

1. Define the user problem, scope, non-goals and open decisions in a numbered specification.
2. Write observable acceptance criteria and failure cases.
3. Design data, authorization, media lifecycle and OpenAPI operations before endpoint code.
4. Break the milestone into small tasks linked to acceptance criteria.
5. Implement a vertical slice, using Boost for Laravel documentation and application context.
6. Verify behaviour with appropriate tests, validate OpenAPI and exercise the user flow.
7. Record evidence and unresolved limitations, then update milestone status.

Specifications use Draft, Ready, Implementing or Verified status. Ready requires resolved decisions relevant to that slice. Verified requires evidence, not merely completed code.

API contracts will live at `contracts/openapi.yaml`. Do not create a token contract that implies unspecified endpoints are complete. Foundation work selects contract validation tooling; core work writes the initial substantive contract before implementation.

AI-generated work must remain reviewable. Keep changes scoped to the specification; verify permissions, validation and tests. Never substitute confident prose for execution evidence.
