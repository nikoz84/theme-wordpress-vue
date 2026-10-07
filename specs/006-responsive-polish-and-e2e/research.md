# Phase 0 Research: Home-Page Responsive Polish & Playwright E2E

**Date**: 2026-10-06
**Spec**: `specs/006-responsive-polish-and-e2e/spec.md`
**Branch**: `006-responsive-polish-and-e2e`

This file consolidates the research needed to resolve every
technology-choice question that emerged during planning.

## R1 — Media-query breakpoint strategy

**Decision**: Append a single `@media (max-width: 1024px)`, `@media
(max-width: 768px)`, and `@media (max-width: 420px)` block to
`theme/style.css` (at the bottom, after the existing Safe Mídia
section styles) covering every home section's grid / flex / fixed
sizing overrides.

**Rationale**: The Safe Mídia HTML already documents these three
breakpoints. Per the spec's constraint, the design tokens at
`:root` and the existing component styles are unchanged; only
breakpoint-specific overrides are added. A single `@media`
block per breakpoint is the canonical CSS pattern.

**Alternatives considered**:
- *Per-section stylesheet files (`theme/css/home/minimal-*`)* —
  over-engineered for 12 sections; rejected.
- *JS-driven resize hooks* — defeats the purpose (CSS-only
  reflow); rejected.

## R2 — Long-title truncation CSS pattern

**Decision**: Apply `text-overflow: ellipsis; overflow: hidden;
white-space: nowrap;` to the Safe Mídia `.ncard-title`,
`.analise-main-title`, `.scard-title`, `.col-article-title`,
`.tab-card-title`, `.boletim-text`, `.aitem-title`,
`.related-card-title`, and `.newsletter-grande h1` selectors within
the breakpoint media queries where the title would otherwise
overflow.

**Rationale**: The Safe Mídia HTML uses `overflow: hidden` and
`text-overflow: ellipsis` on cards; this is the established
pattern. Multi-line wrap inside a fixed-height card is brittle
across browsers.

**Alternatives considered**:
- *`-webkit-line-clamp: N`* — modern but inconsistent across
  browsers without `-webkit-` prefix; rejected for cross-browser
  stability.
- *Allowing titles to wrap and pushing card height to fit* —
  causes the grid to be uneven; rejected.

## R3 — Navbar brand string

**Decision**: In `theme/template-parts/header/navbar.php`, change
the `.logo-text` markup from "Vue Blocks" to "Safe Mídia".

**Rationale**: Per US2 (FR-008). The brand string is hardcoded
in the template part; the Customizer's Site title is a separate
field that drives `bloginfo('name')` consumers (`<title>`, header
text). The two are decoupled by design.

**Alternatives considered**:
- *Customizer field "Brand name"* — adds a new Customizer
  panel; deferred per the spec's Out-of-Scope list. The brand is
  hardcoded for v1.
- *Reading from `get_bloginfo('name')`* — would couple the brand
  to the Customizer's Site title; rejected (US2's FR-009 explicitly
  forbids coupling).

## R4 — Playwright config shape

**Decision**: A single `playwright.config.ts` at the repo root:

```ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? 'github' : 'list',
  use: {
    baseURL: process.env.PW_URL ?? 'http://localhost:8080',
    headless: !process.env.PWDEBUG,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
```

**Rationale**: The minimum viable Playwright config. Chromium is
the only project (matches the spec's "Chromium-only v1" constraint);
`PWDEBUG=1` enables headed mode for local debugging; `PW_URL`
overrides the base URL for remote targets.

**Alternatives considered**:
- *Multi-project config (chromium + firefox + webkit)* — explicit
  Out-of-Scope for v1 (the spec defers Firefox/WebKit).
- *Playwright Test runner via `@playwright/test` (instead of
  raw `playwright`)* — `@playwright/test` is the modern standard;
  accepted.

## R5 — Test fixtures

**Decision**: A shared `tests/e2e/fixtures.ts` exporting two
helpers:

1. `expectDockerHarnessUp()` — `test.beforeAll` hook that fetches
   `GET /` from `baseURL` and asserts the HTTP 200 response +
   the presence of `<title>Safe Mídia</title>` in the home page
   HTML (per US2 / FR-008).
2. `createSyntheticLongTitlePost()` — creates a draft post with a
   title of 100 `'A'` characters + body `'placeholder'`. Used by
   `long-title.spec.ts`. Test teardown deletes the post via
   `wp.deletePost()` (or REST `DELETE /wp-json/wp/v2/posts/<id>`).

**Rationale**: One central fixture file keeps the suite DRY.
The synthetic long-title post is a one-off; it must be cleaned up
to avoid leaving artifacts on the test target.

**Alternatives considered**:
- *Per-test fixtures* — duplicates the harness-check logic across
  every spec; rejected.
- *Global setup / teardown* (Playwright `globalSetup`) — overkill
  for a single Docker harness; rejected.

## R6 — Long-title length

**Decision**: 100 `'A'` characters (per FR-013's safe upper bound).
Long enough to overflow any fixed-width card on mobile; short
enough to be readable when not truncated.

**Rationale**: Safe Mídia cards on the news grid are ~480×195
(per the existing CSS). A 100-character title at 15px is
guaranteed to overflow the card width.

**Alternatives considered**:
- *`text-overflow: ellipsis` test on a fixed string* — equivalent
  to the chosen approach; rejected in favor of dynamic content.
- *Lorem ipsum copy* — words break unpredictably at character
  boundaries; a single-character string is the worst case.

## R7 — Viewport matrix

**Decision**: Five viewports — 1280×800 (desktop reference),
1024×800 (tablet), 768×1024 (mobile), 414×896 (mobile), 375×812
(small mobile). Documented in `playwright.config.ts` via the
`projects` and a per-spec `test.use({ viewport: {...} })` block.

**Rationale**: The three Safe Mídia breakpoints (≤1024, ≤768,
≤420) plus a desktop reference (1280) and a small-mobile reference
(375) cover the design's responsive matrix.

**Alternatives considered**:
- *Just the three breakpoints* — under-tests desktop and small-mobile
  edges; rejected.
- *More viewports (10+)* — over-tests; rejected.

## R8 — package.json addition shape

**Decision**: Add to `package.json`:

```jsonc
{
  "devDependencies": {
    "@playwright/test": "^1.48.0"
  },
  "scripts": {
    "test:e2e": "playwright test"
  }
}
```

**Rationale**: Standard Playwright + npm script shape. `^1.48.0`
is the current major at the time of writing. The script `test:e2e`
runs the full suite; `pw-downgrade=1` env var — handled inside
`playwright.config.ts`.

**Alternatives considered**:
- *Test runner per spec file* (e.g., `test:e2e:brand`,
  `test:e2e:overflow`) — over-engineered; the default
  `playwright test` already supports `test:e2e --grep=...`.

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified
during `/speckit.clarify` (scope = full home, 12 sections) and the
defaults documented in the spec's `## Assumptions` section hold.

## Deferred to `/speckit.tasks`

- The exact CSS class names per FR — already documented in the Safe
  Mídia HTML; deferred to implementation tasks.
- The exact synthetic long-title length (default 100 chars; tuned
  in the implementation task).
- The exact test-file structure (4 specs; tuned in the
  implementation task).