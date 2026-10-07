# Feature Specification: Home-Page Responsive Polish & Playwright E2E

**Feature Branch**: `006-responsive-polish-and-e2e`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "which breakpoints must work (per the Safe Mídia responsive design: ≤420px, ≤768px, ≤1024px). The existing design-system tokens in theme/style.css's :root and the DESIGN.md reference should not change and the Vue Blocks title still render and not have effect when change on form customize theme title, and need to implement e2e testing with playwright"

**Reference design**: `layouts-html/01 - Home/01 - safemidia-home-fixed.html`
(the Safe Mídia HTML; documents the responsive breakpoints and the
brand string).

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md`).

**Scope guardrail**: This feature polishes the home page's mobile
breakpoints, fixes the navbar brand string, and adds a Playwright
e2e test suite. It MUST NOT modify the design-system tokens at
`theme/style.css`'s `:root`, MUST NOT modify `DESIGN.md`, and MUST
NOT change the theme name "Vue Blocks" (which remains in
`style.css`'s WP header).

## Clarifications

### Session 2026-10-06

- Q: Which home-page sections should the responsive polish cover? → A:
  Full home (12 sections) — every section on the home page
  (navbar, hero, news grid, newsletter compact, Para o Segurado,
  newsletter grande, Análise de Mercado, category tabs, Mais Lidas,
  Boletim Regulatório, Colunistas, footer). The user-reported three
  (Últimas Notícias, Para o Segurado, Mais Lidas) are included;
  the remaining nine also receive the same responsive-reflow
  treatment to prevent sibling regressions.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Home sections reflow correctly across the Safe Mídia breakpoints (Priority: P1) 🎯 MVP

A visitor opens the home page on a tablet (≤1024px), mobile (≤768px),
or small mobile (≤420px) device. The "Últimas Notícias" news grid,
the "Para o Segurado" 4-card rail, the "Mais Lidas da Semana"
numbered list, the "Boletim Regulatório" regulatory items, the
"Colunistas" columnist cards, and the tabs / newsletter sections
all reflow without horizontal overflow, without clipped text, and
without breaking the existing Safe Mídia typography tokens.

**Why this priority**: The current implementation reflows some
sections at ≤768px but not consistently across the three Safe Mídia
breakpoints (≤1024, ≤768, ≤420); cards 3 "next to" each other
overflow on small mobile.

**Independent Test**: With seeded content, visit the home page at
viewports 1280×800, 1024×800, 768×1024, 414×896, and 375×812;
confirm `document.documentElement.scrollWidth <= clientWidth` on
each (no horizontal scrollbar) and confirm no card / list item
clips past its container's edge.

**Acceptance Scenarios**:

1. **Given** the home page is loaded at 375×812 (small mobile), **When**
   the visitor scrolls through the page, **Then** no section
   produces a horizontal scrollbar (`document.scrollWidth` equals
   client).
2. **Given** the home page is loaded at 768×1024 (mobile), **When**
   the "Mais Lidas da Semana" section renders, **Then** each numbered
   item fits its container (no overflow on the number / thumb / title
   cell).
3. **Given** the home page is loaded at 1024×800 (tablet), **When**
   the "Colunistas" section renders, **Then** the 3 cards fit
   arranged in 3 columns with no overflow into the page padding.
4. **Given** the home page is loaded at 768×1024 (mobile), **When**
   the "Para o Segurado" section renders, **Then** the 4 cards
   arrange in a 2-column grid with no overflow into the page
   padding.

---

### User Story 2 - Navbar brand text is "Safe Mídia", decoupled from the Customizer's Site title (Priority: P1)

A visitor loads any page. The navbar's brand text reads "Safe
Mídia" (matching the Safe Mídia design). When an admin changes the
WordPress `Site title` via `wp-admin → Appearance → Customize`,
ONLY the document `<title>` tag and WordPress's `blogname` option
update — the navbar brand stays "Safe Mídia". The theme name in
`theme/style.css`'s WP header remains "Vue Blocks" (the theme
identity — separate from the brand string).

**Why this priority**: Currently the navbar reads "Vue Blocks",
which is the theme's WP name; the Safe Mídia design brand should be
"Safe Mídia". The Customizer's Site title must NOT affect the
navbar (it controls the WordPress `blogname` and document `<title>`).

**Independent Test**:
1. Load `/`, observe the navbar brand text reads "Safe Mídia".
2. As admin, set the Customizer's Site title to "Test Site". Reload
   `/`. The navbar brand still reads "Safe Mídia"; the document
   `<title>` tag reads "Test Site".
3. Inspect `theme/style.css`'s WP header — the `Theme Name:` field
   is still "Vue Blocks".

**Acceptance Scenarios**:

1. **Given** the theme is active, **When** the front-end navbar is
   rendered, **Then** the `.logo-text` element reads "Safe Mídia"
   (not "Vue Blocks").
2. **Given** an admin sets the Customizer's Site title to "Anything
   Here", **When** any front-end page is reloaded, **Then** the
   `<title>` element reflects "Anything Here" AND the navbar brand
   remains "Safe Mídia".
3. **Given** the theme, **When** the theme's `style.css` WP header is
   inspected, **Then** the `Theme Name:` field remains "Vue Blocks".

---

### User Story 3 - Playwright e2e suite covering spacing, overflow, and breakpoints (Priority: P1)

A contributor runs `npm run test:e2e` (or equivalent) and gets a
passing e2e suite that:
- Verifies the home page at viewports 1280, 1024, 768, 414, 375
  (the Safe Mídia breakpoints).
- Asserts `document.documentElement.scrollWidth === clientWidth`
  on each viewport (no horizontal overflow).
- Asserts the navbar brand text reads "Safe Mídia".
- Asserts the document `<title>` reflects the WordPress `blogname`
  (i.e., the Customizer's Site title drives `<title>`, not the
  navbar brand).
- Asserts long post titles in the news grid truncate without
  overflowing their card.

**Why this priority**: Browser-level verification is the only way
to catch CSS responsive regressions and the navbar / `<title>`
distinction before a release. The suite runs against the same Docker
stack the project ships (`docker compose up`), and the test runner
installs Chromium automatically via Playwright.

**Independent Test**:
1. `npm install` succeeds (Playwright + browsers install in CI; in
   dev, browsers may be downloaded on demand).
2. `npm run test:e2e` runs all tests against `http://localhost:8080`
   (configurable) and exits 0.
3. The suite catches a known regression: temporarily break the
   navbar's `.logo-text` to "Vue Blocks" and confirm the test
   fails; restore and confirm it passes again.

**Acceptance Scenarios**:

1. **Given** the Docker harness is up at `http://localhost:8080`,
   **When** the contributor runs `npm run test:e2e`, **Then** the
   suite executes against the home page at the five breakpoints and
   passes.
2. **Given** the navbar is intact, **When** the brand-text test
   runs, **Then** it asserts the `.logo-text` element's text is
   "Safe Mídia".
3. **Given** the Customizer's Site title is "Anything Here", **When**
   the title-decode test runs, **Then** it asserts the document
   `<title>` is "Anything Here".
4. **Given** a post with a long title (> 80 chars) is in the news
   grid, **When** the long-title test runs at any breakpoint, **Then**
   the post card's title height fits within the grid cell (no
   overflow).

---

### Edge Cases

- **Empty seed categories**: Sections that depend on data (Boletim,
  Colunistas, etc.) gracefully hide per FR-004 — the test harness
  accounts for this and only checks sections that are present.
- **Headless vs. headed**: Tests MUST run headless by default
  (`--browser=chromium --headless`); a `PWDEBUG=1` env var allows
  headed mode for local debugging.
- **Long post titles**: Tested with a synthetic long-title post
  (`Lorem ipsum` × N chars); the test MUST clean up the synthetic
  post after running.
- **Theme name in `style.css`**: Tests MUST NOT assert that the
  theme name is "Vue Blocks" (it's the theme's WP identifier,
  separate from the brand string).

## Requirements *(mandatory)*

### Functional Requirements

Per Clarification Q1, every section on the home page receives the
Safe Mídia breakpoint treatment (full home — 12 sections). The
following per-section reflow requirements are the minimum
expectations; the e2e suite (FR-011) verifies each one.

- **FR-001**: The navbar MUST reflow at the three Safe Mídia
  breakpoints: ≤1024px (nav-links hidden, hamburger visible),
  ≤768px (logo centered + hamburger), ≤420px (compact layout).
  No horizontal scrollbar at any breakpoint.
- **FR-002**: The hero (featured + sidebar) MUST reflow: ≤1024px
  (single-column hero, sidebar as a 2-column row of small posts),
  ≤768px (single-column hero, sidebar as a vertical list),
  ≤420px (compact layout, no overflow).
- **FR-003**: The "Últimas Notícias" news grid MUST reflow at the
  three Safe Mídia breakpoints: ≤1024px (2-col), ≤768px (2-col),
  ≤420px (1-col). At ≤420px no horizontal scrollbar appears.
- **FR-004**: The newsletter-compact MUST reflow at ≤768px and ≤420px
  (stack the form below the headline).
- **FR-005**: The "Para o Segurado" 4-card rail MUST reflow: ≤1024px
  (2-col), ≤768px (2-col), ≤420px (1-col).
- **FR-006**: The newsletter-grande MUST reflow at ≤768px and ≤420px
  (single-column layout with stacked form).
- **FR-007**: The "Análise de Mercado" 2-col layout MUST reflow to
  1-col at ≤768px and below.
- **FR-008**: The category-tabs nav MUST reflow at ≤768px and ≤420px
  (horizontally scrollable tab strip without overflow).
- **FR-009**: The "Mais Lidas da Semana" numbered list MUST reflow:
  ≤1024px (2-col), ≤768px (1-col), ≤420px (1-col with no number
  / thumb overflow).
- **FR-010**: The "Boletim Regulatório" list items MUST reflow at
  ≤768px and ≤420px without overflow of the `.boletim-tipo` chip.
- **FR-011**: The "Colunistas" cards MUST reflow: ≤1024px (3-col),
  ≤768px (1-col), ≤420px (1-col).
- **FR-012**: The footer columns MUST reflow at ≤1024px to ≤768px
  (single-column stack).
- **FR-007**: Long post titles in the news grid MUST truncate
  (text-overflow: ellipsis or equivalent) without overflowing their
  card at any breakpoint.
- **FR-008**: The navbar's `.logo-text` MUST render the brand
  string "Safe Mídia" (per US2).
- **FR-009**: The Customizer's Site title MUST update ONLY the
  document `<title>` tag and WordPress's `blogname` option —
  changing it MUST NOT affect the navbar brand.
- **FR-010**: A Playwright e2e suite MUST exist at `tests/e2e/` with
  configuration at `playwright.config.ts`. The suite MUST run
  against the Docker harness at `http://localhost:8080` (overridable
  via `PW_URL` env var).
- **FR-011**: The suite MUST include tests for: navbar brand text,
  no-horizontal-overflow at the five breakpoints, navbar / `<title>`
  decoupling, and long-title truncation.
- **FR-012**: The `npm run test:e2e` script MUST exist in
  `package.json` and exit non-zero on any test failure.
- **FR-013**: All visual tokens (colors, typography, spacing, radii,
  shadows) MUST continue to come from `theme/style.css`'s `:root`
  custom properties — no hard-coded visual values in component
  rules. The existing `:root` block and `DESIGN.md` MUST NOT
  change in this feature.

### Key Entities *(include if feature involves data)*

- **Synthetic Long-Title Post**: a temporary WP post created by
  the e2e suite (with title > 80 chars) to verify the long-title
  truncation test; deleted at test teardown.
- **Playwright Test Fixture**: a shared module that boots the
  suite (browser context, base URL, Docker harness precondition
  check).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: With seeded content, navigating to the home page at
  1280×800, 1024×800, 768×1024, 414×896, and 375×812 produces no
  horizontal scrollbar at any of the five viewports for any of the
  twelve home sections (per Clarification Q1).
- **SC-002**: The navbar brand text on every front-end page reads
  "Safe Mídia" regardless of the Customizer's Site title.
- **SC-003**: Setting the Customizer's Site title to any string
  updates the document `<title>` tag on every page but leaves the
  navbar brand unchanged.
- **SC-004**: `npm run test:e2e` exits 0 against a healthy Docker
  harness and exits non-zero when any of the responsive /
  brand-text / long-title assertions fail.
- **SC-005**: The `theme/style.css` `:root` token block and
  `DESIGN.md` are byte-identical before and after this feature lands.

## Assumptions

- The navbar brand "Safe Mídia" is hardcoded in the template part
  (`theme/template-parts/header/navbar.php`); it is NOT driven by
  WordPress's `blogname` or any theme option.
- The WordPress Customizer's Site title (feature 004) drives ONLY
  `get_bloginfo('name')` consumers (`<title>`, header text, etc.) —
  the navbar brand is intentionally separate.
- The Playwright suite installs Chromium via Playwright's built-in
  downloader; no separate browser-install step is needed.
- Long-title truncation uses `text-overflow: ellipsis` (the
  Safe Mídia CSS pattern).
- The five test viewports (1280, 1024, 768, 414, 375) cover the
  three Safe Mídia breakpoints (≤1024, ≤768, ≤420) plus a desktop
  reference (1280) and a small mobile reference (375).
- `package.json` is updated to add `@playwright/test` as a
  devDependency and `test:e2e` as an npm script — no new build
  step is introduced in the packaged theme (Principle V).
- The Docker harness (`docker compose up -d`) is the test target;
  the suite assumes `http://localhost:8080` is reachable.

## Out-of-Scope (deliberately omitted from v1)

- Visual / unit tests (recorded as a future feature in the
  TODO; not in scope here).
- CI integration (running `npm run test:e2e` in GitHub Actions
  on PRs).
- Cross-browser matrix beyond Chromium (Firefox / WebKit
  smoke-tests deferred).
- A11y assertions (axe-core integration deferred).
- Theme-options for the navbar brand text (deferred; the brand is
  hardcoded for v1).
- Replacing `package.json`'s Vite dependency (the Vite opt-in
  pipeline from feature 002 is preserved untouched).