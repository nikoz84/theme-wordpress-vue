# Phase 0 Research: Safe Mídia Visual Fidelity Fixes

**Date**: 2026-10-06
**Spec**: `specs/007-safemidia-fidelity-fixes/spec.md`
**Branch**: `007-safemidia-fidelity-fixes`

This file consolidates the research needed to resolve every
technology-choice question that emerged during planning.

## R1 — Para o Segurado header markup structure

**Decision**: The new `<div class="segurado-hd">` wraps three children
in this exact order:
1. `<h2 class="segurado-title">Para o Segurado</h2>` (the heading)
2. `<span class="segurado-sub">Direitos, dicas e orientações para
   quem já tem ou quer contratar um seguro</span>` (the subtitle)
3. `<a class="segurado-btn-all" href="<archive url>">Ver todos
   &rarr;</a>` (the button link)

**Rationale**: This order matches the Safe Mídia reference HTML at
`layouts-html/01 - Home/01 - safemidia-home-fixed.html` (the
`segurado-hd` element contains those three in that sequence).

**Alternatives considered**:
- *Reverse order (button before subtitle)* — matches no version of
  the reference; rejected.
- *Wrapping `<h2>` inside the button (for accessibility)* — would
  hide the heading from assistive tech reading order; rejected.

## R2 — "Ver todos" link target resolution

**Decision**: Resolve the link target via a small priority chain:
1. If a WordPress category with slug `segurado` (or name "Para o
   Segurado") exists, link to its archive (`get_category_link()`).
2. Otherwise, link to the posts archive
   (`get_permalink( get_option( 'page_for_posts' ) )`) or
   `home_url( '/?post_type=post' )` as fallback.
3. Never `href="#"` — the link must be a real anchor (per FR-003).

**Rationale**: Matches the Safe Mídia design (a real archive link),
preserves the project's empty-state pattern, and avoids a
placeholder link that breaks the e2e `segurado-btn-all` assertion.

**Alternatives considered**:
- *Always `href="#"`* — placeholder; rejected (FR-003 forbids).
- *Customizer option* — out-of-scope per the spec's
  Out-of-Scope list.

## R3 — Footer brand-text decoupling

**Decision**: Replace `esc_html( $vb_site_title )` (where
`$vb_site_title = get_bloginfo('name')`) with the literal
hardcoded brand string `"Safe Mídia"`. The `$vb_site_title`
variable MAY stay in the file (it might be useful for context) but
is no longer rendered in the footer-bottom span.

**Rationale**: The footer copyright is a brand element (per the
Safe Mídia design), not a user-customizable value. The Customizer's
Site title still drives the document `<title>` tag and any other
`bloginfo('name')` consumers (preserved from feature 006's US2).

**Alternatives considered**:
- *Customizer option "Footer copyright"* — adds a new Customizer
  panel; deferred per the out-of-scope list.
- *Hidden CSS* (`display: none` on `.vb-year`) — hides the whole
  line, not just the brand; rejected.

## R4 — Playwright test extension strategy

**Decision**: Two new spec files (or one new + one extension):
- **Option A (chosen)**: New `tests/e2e/segurado-markup.spec.ts`
  (US1). New or extend `tests/e2e/title-decoupling.spec.ts`
  (US2 — extend the existing test with a footer-brand assertion).
- **Option B**: One combined `tests/e2e/fidelity-fixes.spec.ts` file.

Option A wins because the existing `title-decoupling.spec.ts`
already covers the title-vs-brand decoupling for the `<title>` tag
(US2 of feature 006); extending it with a footer-brand assertion is
a natural fit. New file for the "Para o Segurado" markup (US1)
because it's a different section / different assertion set.

**Rationale**: Spec files organized by feature concern match the
existing pattern (`navbar-brand`, `title-decoupling`,
`no-overflow`, `long-title` — one file per concern).

**Alternatives considered**:
- *Combined file (`fidelity-fixes.spec.ts`)* — bundles two concerns
  into one file; rejected per the per-concern naming pattern.

## R5 — Footer copyright assertion details

**Decision**: The footer-brand spec reads `.footer-bottom`'s
`textContent` (or queries via Playwright's `page.locator(...).
.textContent()`) and asserts it contains "Safe Mídia". The assertion
runs BEFORE and AFTER mutating the Customizer's Site title via
the existing test shim; both assertions must pass.

**Rationale**: The "decoupling" semantics is best tested as a
two-state assertion (default → mutate → re-assert), not just a
one-shot read. Matches the existing `title-decoupling.spec.ts`
pattern.

**Alternatives considered**:
- *Single one-shot assertion* — would miss the mutation half of
  the property; rejected.

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clear enough
that defaults documented in the spec's `## Assumptions` section
hold.

## Deferred to `/speckit.tasks`

- The exact `get_category_link()` fallback logic (handled
  inside the template-part edit; deferred to implementation).
- The exact Playwright assertion wording for `.segurado-btn-all`
  (deferred to implementation).
- The exact footer-brand regex / string-match approach (`includes("Safe Mídia")` is sufficient; deferred).