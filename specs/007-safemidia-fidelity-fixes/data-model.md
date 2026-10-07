# Phase 1 Data Model: Safe Mídia Visual Fidelity Fixes

**Date**: 2026-10-06
**Spec**: `specs/007-safemidia-fidelity-fixes/spec.md`
**Branch**: `007-safemidia-fidelity-fixes`

This file enumerates the entities introduced or modified by this
feature, with attributes, validation rules, and lifecycle notes.

---

## E1 — "Para o Segurado" Section Header

A new sub-tree rendered by `theme/template-parts/home/segurado.php`
BEFORE the existing WP_Query loop.

| Field | Tag | Markup |
|---|---|---|
| Wrapper | `<div>` | `<div class="segurado-hd">` (the Safe Muestra reference HTML structure). |
| Title | `<h2>` | `<h2 class="segurado-title">Para o Segurado</h2>` (literal text per FR-002). |
| Subtitle | `<span>` | `<span class="segurado-sub">Direitos, dicas e orientações para quem já tem ou quer contratar um seguro</span>` (literal text per FR-002). |
| Button | `<a>` | `<a class="segurado-btn-all" href="<archive-url>">Ver todos &rarr;</a>` (the link target per R2). |

### Validation

- `.segurado-hd` MUST be present (US1 acceptance 1).
- The four children MUST be present in the documented order.
- The `<a class="segurado-btn-all">` MUST have a non-`#` href
  (per FR-003).
- The block MUST be inside `<section class="segurado-section">` and
  between the `.vb-container` wrapper and the `.segurado-grid`
  (i.e., above the cards, not below them).

### Lifecycle

Stateless. Rendered every time the "Para o Segurado" template
part is included (per the front-page.php composition from feature
005). Hidden entirely when the section has no content (per
FR-004 of feature 005 — the existing empty-state guard).

---

## E2 — Footer Brand String

A single hardcoded literal that replaces `$vb_site_title` in the
footer-bottom span.

| Field | Value |
|---|---|
| Brand string | `"Safe Mídia"` (literal; per FR-004 / FR-005). |
| Source | Hardcoded literal in `theme/template-parts/footer/site-footer.php`. |
| Variable `$vb_site_title` | MAY stay in the file (used for context elsewhere); MUST NOT be rendered in `footer-bottom`. |

### Validation

- The `footer-bottom` span MUST contain the literal "Safe Mídia".
- After mutating `blogname` to "Anything Here" via the test shim,
  the `footer-bottom` span MUST STILL contain "Safe Mídia" (per US2
  acceptance 1 / SC-002).

### Lifecycle

Stateless. Rendered every page render. Independent of the
Customizer's Site title (which still drives `<title>` and
`bloginfo('name')` consumers elsewhere).

---

## Cross-entity invariants

1. The "Para o Segurado" header (E1) is rendered only when the
   section has content (per FR-004 of feature 005); otherwise the
   entire `.segurado-section` is hidden.
2. The Footer Brand String (E2) is decoupled from the WordPress
   Customizer's Site title (per US2 from feature 006); a site admin
   can change the Customizer's Site title to any string without
   affecting the footer copyright.

---

## Out-of-scope entities (deliberately omitted)

- A Customizer field for the footer copyright text (deferred).
- A Customizer field for the "Ver todos" link target (deferred).
- A translated (i18n) subtitle text (deferred).