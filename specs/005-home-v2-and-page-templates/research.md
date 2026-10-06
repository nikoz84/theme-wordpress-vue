# Phase 0 Research: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

This file consolidates the research needed to resolve every
technology-choice question that emerged during planning.

## R1 — Colunistas category registration

**Decision**: Register the "Colunistas" category via a callback
hooked to `after_setup_theme`, with an idempotency check via
`category_exists('colunistas')`. The callback creates the category
with `wp_create_category('Colunistas')` if it doesn't exist.

**Rationale**: `after_setup_theme` is the canonical WordPress hook
for theme-level setup; it fires after WordPress loads but before
any output. `category_exists` is the standard "has it been created
yet?" check; the registration is naturally idempotent (a re-run
during theme activation re-checks and no-ops).

**Alternatives considered**:
- *Use `init` hook* — fires later and can race with content output;
  rejected.
- *Use `switch_theme` hook* — fires only on theme switch, not on
  re-activation; rejected (we want idempotency on every activation
  too).
- *Embed in the existing `vb_setup()`* — would couple category
  creation with the existing setup work; rejected (categories are a
  distinct concern).

## R2 — Colunista card data sourcing

**Decision**: One card per distinct `post_author` in the
"Colunistas" category. Use `WP_Query` to fetch posts in the
category, group by `post_author`, take the first post per author,
and render one card with the post's featured image, title,
permalink, and the author's `display_name` / `user_description`.

**Rationale**: WordPress's standard `WP_Query` + `wp_get_post_terms`
+ `get_the_author_meta` cover all the data needed. The card list
is naturally capped at the number of distinct authors who have
posted in the category (no fixed cap).

**Alternatives considered**:
- *Custom post type "colunista"* — adds migration complexity;
  rejected per Assumptions.
- *Hard-coded list* — not maintainable; rejected.

## R3 — Mais Lidas da Semana sorting

**Decision**: Use the most-recent-N-posts-in-the-category approach
(falling back to all posts if the category has fewer than N). The
section title is rendered as "Mais Lidas da Semana" but the data
is "most recent posts" rather than view-count tracking.

**Rationale**: View-count tracking requires either a plugin
(post-views-counter) or a custom `meta_value` update on every page
load (slow, privacy-sensitive). Per the spec's Assumptions, the
simpler "most-recent" approach is the default for v2. The visual
label is editorial ("Mais Lidas da Semana"); the data is
time-based. This avoids over-promising.

**Alternatives considered**:
- *Plugin-based view tracking* — adds a runtime dependency; rejected.
- *Custom `meta_value` updates on page load* — performance penalty
  + privacy concern; rejected.

## R4 — Newsletter form rendering

**Decision**: Static HTML forms (`<form onsubmit="return false;">` per
the existing footer template) — no `action`, no persistence. Forms
collect no data and persist via form-element only.

**Rationale**: A newsletter backend (saving emails, double opt-in,
integration with Mailchimp / etc.) is explicitly out of scope per
the spec's Out-of-Scope list. v2 ships the visual layer only; v3
would add the backend.

**Alternatives considered**:
- *Mailchimp embed* — adds a third-party dependency; rejected.
- *WordPress `wp_mail` to the admin* — feasible but the spec
  defers it.

## R5 — Boletim Regulatório sourcing

**Decision**: The Boletim Regulatório section renders posts in a
specific "regulatory" category (slug `regulatorio`, name "Regulação",
or — if missing — posts tagged with a specific regulator-related tag).
Each row carries the post's category chip, the post type
(consulta pública, circular, resolução, etc., derived from the
post's first tag), and the post title.

**Rationale**: The Safe Mídia HTML's Boletim section has
post-type-specific chips (circular, resolução, consulta, portaria).
Mapping them to post tags keeps the implementation plugin-free.

**Alternatives considered**:
- *Custom taxonomy "regulatory_type"* — adds a term-registration
  step; rejected per Assumptions (no new taxonomies).
- *Hard-coded post list* — not maintainable; rejected.

## R6 — Análise de Mercado sourcing

**Decision**: One featured post + a list of recent posts in the
"Análise" category (slug `analise`). The featured card mirrors the
hero's `.analise-main` style; the list uses the `.aititem` rows.

**Rationale**: Same pattern as the hero/sidebar split. The
"Análise" category is identified by slug; if missing, the section
falls back to most-recent posts in any category tagged `analise`.

**Alternatives considered**:
- *Custom post type "analise"* — adds migration complexity;
  rejected.
- *Tag-based sourcing only* — works but less clean; rejected
  (category is the primary signal).

## R7 — Category tabs

**Decision**: Render the four most-populated categories as a tab
nav; each tab shows the most recent 4 posts in that category in a
4-column grid. The active tab uses the existing Safe Mídia's
`.tab-btn.active` styling.

**Rationale**: The Safe Mídia HTML shows 4 categories with a
4-column grid each. The "tab" interaction is a static
current-state (which tab is "active" on page render); no JS tab
switching needed in v2 (links to category archives serve as the
default "switch" affordance).

**Alternatives considered**:
- *JS tab switching* — adds a JS dependency for a minor UX win;
  rejected per the no-build constraint (the existing
  assets/js/app.js is the Vue layer; the tabs can stay in PHP).
- *Single category with carousel* — different layout, not the
  reference; rejected.

## R8 — Single-post template structure

**Decision**: `theme/single.php` composes, in order:
1. `theme/template-parts/content.php` (existing, used by the
   archive loops; refined to add the Safe Mídia hero / byline /
   category chip).
2. A "Leia também" rail at the bottom (3 most recent posts in the
   same category, excluding the current post).

The byline uses `get_the_author()` for the author and
`get_the_date()` for the publish date.

**Rationale**: The existing `template-parts/content.php` already
implements the "excerpt + thumbnail + meta" pattern; v2 refines it
to add the hero / category chip and uses it from `single.php` as
well. Per the spec's Q2 clarification, the layout is full editorial.

**Alternatives considered**:
- *Custom single-post template part* — duplicates logic; rejected
  (refining `content.php` is cheaper).
- *Inline everything in single.php* — no separation; rejected.

## R9 — Page template refinements (archive / search / 404)

**Decision**: All three templates continue to call `get_header()` /
`get_footer()` and use the standard `WP_Query` for archive / search.
The refinements:

- `archive.php` — switch from the existing Vue Blocks news-card
  pattern to the Safe Mídia `.ncard` markup (image + category chip
  + title).
- `search.php` — same Safe Mídia news-card markup for results; an
  Safe Mídia "no results" message when zero matches.
- `404.php` — Safe Mídia typography; a friendly "página não
  encontrada" message; navbar + footer present.

**Rationale**: Each template is a small edit to align the
existing `template-parts/content.php` usage with the Safe Mídia CSS
class names. No new template parts required.

**Alternatives considered**:
- *New template parts per template* — over-engineered; rejected.

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified
during `/speckit.clarify` (v2 = all 8 sections, single-post =
full editorial, Colunistas category auto-created) and the
defaults documented in the spec's `## Assumptions` section hold.

## Deferred to `/speckit.tasks`

- The exact CSS class names per section (transcribed from the Safe
  Mídia HTML's `.newsletter-compact`, `.segurado-grid`, etc.).
- The exact widget area registration order (if any).
- The exact customizer additions (none in v2; deferred).
- The exact post-type chips for Boletim Regulatório (derived from
  post tags; deferred to plan-level task description).