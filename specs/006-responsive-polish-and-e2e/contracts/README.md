# Contracts: Home-Page Responsive Polish & Playwright E2E

**Date**: 2026-10-06
**Spec**: `specs/006-responsive-polish-and-e2e/spec.md`
**Branch**: `006-responsive-polish-and-e2e`

This directory holds the interface definitions for the Playwright
e2e suite.

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `e2e-suite.contract.md` | Tooling → Suite | Defines the `playwright.config.ts` shape, viewport matrix, fixture contract, and `npm run test:e2e` exit-code contract |

## Contract reference

### E2E suite (per `e2e-suite.contract.md`)

The suite MUST:

- Live at `tests/e2e/` with config at `playwright.config.ts`.
- Use `@playwright/test` as the test runner.
- Target `process.env.PW_URL ?? 'http://localhost:8080'` as the
  base URL (overridable for remote targets).
- Run Chromium headless by default; `PWDEBUG=1` enables headed
  mode for local debugging.
- Fail fast if the Docker harness is unreachable at the base URL
  (the harness-up fixture's `beforeAll`).
- Cover five test files (per Story → file mapping):
  - `navbar-brand.spec.ts` — US2
  - `title-decoupling.spec.ts` — US2
  - `no-overflow.spec.ts` — US1 (5 viewports × 12 sections)
  - `long-title.spec.ts` — US3
- Exit non-zero on any test failure (per FR-012 / SC-004).
- Use GitHub Actions reporter when `process.env.CI` is set;
  `list` reporter otherwise.

## Cross-contract invariants

1. The Playwright fixture contract (E2 in `data-model.md`) is the
   only way tests create or delete WP content; per-test fixtures are
   forbidden.
2. The synthetic post is the only post the suite creates; the
   test must not introduce other side effects.

## Out-of-scope surfaces (deliberately omitted)

- Screenshot / visual-diff fixtures (deferred).
- Accessibility API hooks (deferred).
- Cross-browser matrix (deferred).
- CI workflow integration (deferred).