# Implementation Plan: Home-Page Responsive Polish & Playwright E2E

**Branch**: `006-responsive-polish-and-e2e` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/006-responsive-polish-and-e2e/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Two coordinated changes:

1. **Mobile responsive polish**: add breakpoint-specific overrides
   in `theme/style.css` so every home-page section (per FR-001..FR-012,
   covering navbar → footer) reflows correctly at the Safe Mídia
   breakpoints (≤1024px, ≤768px, ≤420px). Per Clarification Q1, all
   12 sections are in scope. The navbar brand string is fixed to
   "Safe Mídia" (was "Vue Blocks").
2. **Playwright e2e test infrastructure**: add `@playwright/test` as a
   devDependency, `playwright.config.ts`, and a `tests/e2e/` suite
   that exercises the home page at the five test viewports and
   asserts no horizontal scrollbar, navbar brand, `<title>`/Site-title
   decoupling, and long-title truncation.

The design-system tokens at `theme/style.css`'s `:root` and
`DESIGN.md` are unchanged (per FR-013 / SC-005).

## Technical Context

**Language/Version**:
- PHP 7.4+ (runtime, theme's existing constraint).
- Plain CSS, plain JavaScript (no build step — per Constitution
  Principle V).
- TypeScript (Playwright config + tests use `@playwright/test`,
  which is TypeScript by default; the suite typechecks via
  `tsc --noEmit` if installed, otherwise via Playwright's own
  esbuild step — both fine).

**Primary Dependencies**:
- `@playwright/test` (npm devDependency) — pulls in Playwright + the
  Chromium browser binary on `npx playwright install chromium`.
- WordPress 6.0+ (the Docker harness the suite tests against).
- No production-runtime dependency added (Playwright is dev-only).

**Storage**:
- Files only. The synthetic long-title post created by the suite
  is a transient fixture; deleted in test teardown.

**Testing**:
- Manual: open `http://localhost:8080` at the five viewports in a
  real browser and verify no horizontal scrollbar + navbar brand.
- E2e: `npm run test:e2e` runs the Playwright suite against the
  Docker harness. Suite uses `headless: true` by default;
  `PWDEBUG=1` enables `headless: false`.
- Visual / unit / CI / cross-browser matrix — out of scope for this
  feature (deferred).

**Target Platform**:
- The e2e suite runs in any Node 20+ environment with the Docker
  harness reachable on the configured `PW_URL` (default
  `http://localhost:8080`).

**Project Type**: Web application — additive CSS overrides + dev-time
test infrastructure.

**Performance Goals**:
- Adding breakpoint media queries to `theme/style.css` adds a few
  hundred lines of CSS; gzip-friendly. No measurable impact on
  time-to-interactive.
- Playwright Chromium cold start ≈ 1–2 s; warm start ≈ 200 ms.

**Constraints**:
- No new build step in the packaged theme (Principle V).
- The design-system tokens at `theme/style.css`'s `:root` and
  `DESIGN.md` MUST NOT change (FR-013 / SC-005).
- The navbar brand string is hardcoded to "Safe Mídia"; the Customizer's
  Site title only affects the document `<title>` and `blogname`.
- The packaged theme size (per `theme/package.sh`) remains ≤ 5 MB
  (Playwright and its test files are repo-root dev-only — NOT inside
  `theme/`).

**Scale/Scope**:
- 12 home sections × 3 breakpoints ≈ 12–18 media-query rules (some
  sections already have rules — refine + add).
- 1 navbar brand text edit (theme/template-parts/header/navbar.php).
- 1 test config (`playwright.config.ts`).
- 1 test helper (`tests/e2e/fixtures.ts` — Docker harness check
  + synthetic-post creation).
- 4 test files (`tests/e2e/*.spec.ts`) — one per story.
- 2 package.json additions (`@playwright/test` devDep + `test:e2e`
  npm script).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | The CSS overrides operate on server-rendered markup; no new client-side rendering is required. |
| II. Progressive Enhancement | **Pass** | Mobile reflow is a CSS-only change; no JS is added or modified. The navbar's mobile menu already works without JS (the hamburger is in the markup; the Vue layer can layer behavior on top). |
| III. WP Template Hierarchy Discipline | **Pass** | No template file structure changes; only one `theme/template-parts/header/navbar.php` brand text edit. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST endpoints added or changed. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | The packaged theme (built via `theme/package.sh`) is unaffected: Playwright is a devDependency at the repo root, not inside `theme/`. |
| Technical Constraints | **Pass** | PHP 7.4+, WP 6.0+, Vue 3.4.x, `vue-blocks` text domain — all preserved. |
| Development Workflow | **Pass** | File ownership preserved; new files at `tests/e2e/` and `playwright.config.ts` at the repo root. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

| Principle | Status post-design | Evidence |
|---|---|---|
| I–V (unchanged) | **Pass** | All additions are CSS overrides + dev-time tooling; the packaged theme is untouched. |
| V (CDN-first) | **Pass** | Playwright files live at the repo root, not in `theme/`; `theme/package.sh`'s exclusion patterns cover them. |
| Technical Constraints | **Pass** | The design tokens and `DESIGN.md` are unchanged; the navbar brand fix is a 1-line edit. |

**Final gate verdict**: PASS.

## Project Structure

### Documentation (this feature)

```text
specs/006-responsive-polish-and-e2e/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── README.md
│   └── e2e-suite.contract.md
├── checklists/
│   └── requirements.md
└── spec.md              # already created
```

### Source Code (additions)

```text
# Repo root — dev-time only, NOT shipped with the packaged theme
.
├── playwright.config.ts                 # NEW — Playwright config (Chromium, 5 viewports, PW_URL)
├── tests/                               # NEW directory
│   └── e2e/
│       ├── fixtures.ts                  # NEW — Docker harness precondition + synthetic-post helper
│       ├── navbar-brand.spec.ts         # NEW — US2 assertions
│       ├── title-decoupling.spec.ts     # NEW — US2 assertions
│       ├── no-overflow.spec.ts          # NEW — US1 assertions (5 viewports × 12 sections)
│       └── long-title.spec.ts           # NEW — US3 assertions
└── package.json                         # MODIFY — add @playwright/test devDep + test:e2e script

# Theme — runtime tweaks
theme/
├── style.css                            # MODIFY — append breakpoint-specific media queries (FR-001..FR-012)
└── template-parts/header/
    └── navbar.php                       # MODIFY — change .logo-text brand from "Vue Blocks" to "Safe Mídia"
```

**Structure Decision**: All CSS overrides go in `theme/style.css`
(below the Safe Mídia section styles from feature 003); no new
files inside `theme/` for this change. Playwright infrastructure
lives at the repo root and is gitignored-friendly (it adds
`node_modules/playwright` etc., all matched by the existing
`node_modules/` gitignore pattern).

**Repo root** (touch and add only):
- `package.json` (modify — new `devDependencies` entry + `scripts`
  entry).
- `playwright.config.ts` (new).
- `tests/` (new directory + 5 new files).

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.