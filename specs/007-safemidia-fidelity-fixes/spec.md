# Feature Specification: Safe Mídia Visual Fidelity Fixes

**Feature Branch**: `007-safemidia-fidelity-fixes`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "propose short name safemidia-fidelity-fixes. This captures both targeted edits in one feature spec so they go through plan → tasks → implement together with full checklist + e2e verification that the helpers keep including the new assertions."

**Reference design**: `layouts-html/01 - Home/01 - safemidia-home-fixed.html`
(Safe Mídia HTML; documents the "Para o Segurado" section structure and
the footer copyright line).

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md`).

**Scope guardrail**: This feature addresses two specific visual
discrepancies between the Safe Mídia reference and the current
shipped theme: (a) the "Para o Segurado" section markup is missing
the divider / subtitle / "Ver todos" button, and (b) the footer's
copyright line is sourcing `bloginfo('name')` (which renders "Vue
Blocks" until the bootstrap default propagates) instead of the Safe
Mídia brand. It MUST NOT modify any other section or change the
existing design tokens at `:root` (per FR-007 / SC-005 of feature
006).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - "Para o Segurado" section renders the Safe Mídia structure (Priority: P1) 🎯 MVP

A visitor scrolls to the "Para o Segurado" section on the home page and
sees the Safe Mídia visual structure: a horizontal divider line above
the heading, the heading "Para o Segurado" in the Safe Mídia
Merriweather typography, the subtitle "Direitos, dicas e orientações
para quem já tem ou quer contratar um seguro", and a "Ver todos"
button on the right edge that links to a relevant page (the "Para o
Segurado" archive or category).

**Why this priority**: The current implementation renders the
section as a single `<h2 class="segurado-title">` heading with the 4
cards below; the divider line, the subtitle, and the "Ver todos"
button are missing. The Safe Mídia reference HTML shows these as
distinct elements and they convey editorial positioning.

**Independent test**: With seeded content, load the home page; confirm
the rendered HTML for the `.segurado-section` contains a
`.segurado-hd` wrapper that holds `.segurado-title`, `.segurado-sub`,
and `.segurado-btn-all` (per the CSS classes already declared in
`theme/style.css`'s Safe Mídia section).

**Acceptance scenarios**:

1. **Given** the home page is loaded, **When** the "Para o Segurado"
   section renders, **Then** a `<div class="segurado-hd">` element is
   present with `.segurado-title` ("Para o Segurado") +
   `.segurado-sub` ("Direitos, dicas e orientações para quem já tem
   ou quer contratar um seguro") + `.segurado-btn-all` ("Ver todos"
   link).
2. **Given** the section is rendered, **When** the visitor clicks
   "Ver todos", **Then** they navigate to a "Para o Segurado" archive
   page (or the "Seguro" category archive as a sensible fallback).

---

### User Story 2 - Footer copyright reads "Safe Mídia", not `bloginfo('name')` (Priority: P1)

A visitor loads any page and the footer's bottom-bar reads
"© 2025 Safe Mídia. Todos os direitos reservados." — the brand text is
hardcoded to "Safe Mídia" (matching the Safe Mídia design), NOT
sourced from `get_bloginfo('name')` (which renders "Vue Blocks"
unless the bootstrap default propagated). The Customizer's Site
title still drives the document `<title>` tag and other
`bloginfo('name')` consumers — the footer copyright is a separate
hardcoded brand element.

**Why this priority**: Currently the footer renders
"© 2025 Vue Blocks..." until the Docker bootstrap's
`WP_SITE_TITLE="Safe Mídia"` propagates to the option; after that
propagation it would render "© 2025 Safe Mídia..." but sourcing
the brand from `bloginfo('name')` couples the footer to the
Customizer's Site title (which is intentionally NOT coupled to the
navbar brand per feature 006's US2).

**Independent test**: With the Customizer's Site title set to any
string (e.g., "Anything Here"), load any page; confirm the footer
bottom-bar STILL reads "© YYYY Safe Mídia" (NOT `"© YYYYAnything
Here"`).

**Acceptance scenarios**:

1. **Given** the Customizer's Site title is "Anything Here", **When**
   the footer is rendered, **Then** the footer-bottom span contains
   "© YYYY Safe Mídia" (the brand text is hardcoded; the Customizer's
   Site title does NOT affect the footer copyright).

---

### User Story 3 - E2E suite covers the fidelity fixes (Priority: P1)

A contributor runs `npm run test:e2e` and the suite passes — the
new Playwright specs assert the "Para o Segurado" structure
(US1) and the footer brand independence (US2) so future regressions
are caught early.

**Why this priority**: The existing Playwright suite from feature
006 covers overflow + navbar brand + Customizer decoupling for the
`<title>` tag. Adding coverage for the new fidelity fixes closes the
regression loop.

**Independent test**: `npm run test:e2e` exits 0 with the new specs
included.

**Acceptance scenarios**:

1. **Given** the theme is active, **When** the e2e suite runs,
   **Then** a new spec (e.g., `segurado-markup.spec.ts`) asserts
   the presence of `.segurado-hd`, `.segurado-title` ("Para o
   Segurado"), `.segurado-sub` ("Direitos, dicas e orientações..."),
   and `.segurado-btn-all`.
2. **Given** the theme is active, **When** the e2e suite runs,
   **Then** the existing `title-decoupling.spec.ts` (or a new
   dedicated footer spec) asserts the footer-bottom contains "Safe
   Mídia" even after the Customizer's Site title is mutated.

---

### Edge cases

- **No "Para o Segurado" content**: Per FR-004 of feature 005, the
  section hides itself entirely when there are no posts. The new
  markup must not break that behavior (i.e., the empty case still
  hides the whole `.segurado-section`).
- **Customizer Site title empty**: If a site admin clears the
  Customizer's Site title entirely, the existing brand-text spec
  continues to expect "Safe Mídia" in the footer — the brand is
  fully decoupled.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: `theme/template-parts/home/segurado.php` MUST emit a
  `<div class="segurado-hd">` containing a `<h2 class="segurado-title">`,
  a `<span class="segurado-sub">` (the Safe Mídia subtitle), and an
  `<a class="segurado-btn-all" href="...">` ("Ver todos" link).
- **FR-002**: The Safe Mídia subtitle text MUST be exactly
  "Direitos, dicas e orientações para quem já tem ou quer contratar
  um seguro" (per the Safe Mídia reference HTML's
  `.segurado-sub` content).
- **FR-003**: The "Ver todos" link MUST point to the "Para o Segurado"
  category archive if such a category exists; otherwise to the
  category archive slug `segurado` (or to `home_url( '/?post_type=post' )`
  as a safe fallback). The link MUST be a real `<a href>` (not a
  placeholder `href="#"`).
- **FR-004**: `theme/template-parts/footer/site-footer.php` MUST
  replace `esc_html( $vb_site_title )` in the footer-bottom line with
  the hardcoded brand string `"Safe Mídia"`. The `$vb_site_title =
  get_bloginfo('name')` line in the same file MAY stay (used for
  context elsewhere) but is no longer rendered in the copyright.
- **FR-005**: The footer-bottom MUST continue to use the existing
  CSS classes (no new classes added; existing `.footer-bottom` styles
  apply). The rendered text MUST be "© YYYY Safe Mídia. Todos os
  direitos reservados." (the year is current; the rest is the Safe
  Mídia copyright).
- **FR-006**: Two new Playwright specs MUST be added at
  `tests/e2e/`: one for the "Para o Segurado" markup (asserts the
  `.segurado-hd` / `.segurado-title` / `.segurado-sub` /
  `.segurado-btn-all` structure), and one (or an extension of the
  existing `title-decoupling.spec.ts`) that mutates the Customizer's
  Site title and asserts the footer STILL contains "Safe Mídia".
- **FR-007**: The existing design tokens at `theme/style.css`'s
  `:root` and the `DESIGN.md` reference MUST remain byte-identical
  before and after this feature lands. The `segurado-hd`,
  `segurado-title`, `segurado-sub`, and `segurado-btn-all` CSS classes
  are already declared in the existing Safe Mídia section of
  `theme/style.css`; no NEW styles are added.

### Key Entities *(include if feature involves data)*

- **"Para o Segurado" Section Header**: a `<div class="segurado-hd">`
  with three children — `.segurado-title` (h2), `.segurado-sub`
  (span), `.segurado-btn-all` (anchor). Existing data-model entity
  E2 (Colunista Card) is the only existing precedent for the
  header structure; this is a parallel pattern for a different
  section.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: With seeded content, the home page's `.segurado-section`
  rendered markup contains `.segurado-hd` with the three children
  (`.segurado-title` reading "Para o Segurado", `.segurado-sub`
  containing the Safe Mídia subtitle text, `.segurado-btn-all` reading
  "Ver todos").
- **SC-002**: With the Customizer's Site title set to any string,
  the footer-bottom span still contains "Safe Mídia" (decoupled
  from the Customizer).
- **SC-003**: `npm run test:e2e` exits 0 against a healthy Docker
  harness; the new specs (or extended existing ones) cover US1's
  markup assertions and US2's footer-brand assertion.
- **SC-004**: `theme/style.css`'s `:root` token block and `DESIGN.md`
  are byte-identical before and after this feature lands.

## Assumptions

- The "Para o Segurado" section's "Ver todos" link target is the
  "Para o Segurado" category archive. If a "Para o Segurado" WordPress
  category does not exist, the link falls back to a category archive
  slug match (e.g., `segurado`) or to `home_url( '/?post_type=post' )`.
- The Safe Mídia subtitle text is a literal translation from the Safe
  Mídia reference HTML (`layouts-html/01 - Home/01 -
  safemidia-home-fixed.html`); no i18n needed for v1.
- The footer copyright "Safe Mídia" is hardcoded (not Customizer-
  driven). The Customizer's Site title still drives the document
  `<title>` tag and other `bloginfo('name')` consumers (preserved
  from feature 006's US2).
- Two new Playwright specs are added; the existing
  `title-decoupling.spec.ts` MAY be reused (it asserts the navbar
  brand independence) or extended to also assert the footer brand
  independence.
- No new REST endpoints, no new Customizer settings, no new build
  step in the packaged theme (Principle V).

## Out-of-Scope (deliberately omitted from v1)

- Translating the subtitle text via `__()` / i18n (deferred).
- A Customizer option for the footer copyright text (deferred; the
  brand is hardcoded for v1).
- A customizer option for the "Ver todos" link target (deferred;
  the section auto-selects the "Para o Segurado" archive for v1).
- Adjusting other Safe Mídia sections (newsletter compact, etc.) —
  these were reported as out-of-scope by feature 005.