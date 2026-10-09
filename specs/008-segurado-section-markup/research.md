# Phase 0 Research: Segurado Section Markup

**Date**: 2026-10-07
**Spec**: `specs/008-segurado-section-markup/spec.md`
**Branch**: `008-segurado-section-markup`

This file consolidates the research needed to resolve every
technology-choice question that emerged during planning.

## R1 — Segurado data storage strategy

**Decision**: Store segurado data as post meta on the `post` post type
(or a custom `policy` CPT if one exists). Use `register_meta()` to
expose fields via REST API with proper sanitization callbacks.

**Rationale**: The project already uses `register_rest_field()` for
custom fields (see `vb_register_rest_fields()` in `functions.php`).
Post meta is the simplest approach that follows WordPress conventions
and avoids introducing a new CPT. If a `policy` CPT is introduced
later, the meta keys remain the same.

**Alternatives considered**:
- *Separate `segurado` CPT* — adds complexity (new CPT registration,
  admin UI, post-to-post relationships) without clear benefit for
  a 1:1 relationship with a policy. Rejected for v1.
- *Custom database table* — violates WordPress conventions and the
  Constitution's "REST API as the PHP↔Vue Bridge" principle.
  Rejected.

## R2 — REST API field registration approach

**Decision**: Extend `vb_register_rest_fields()` in `functions.php`
to register a `vb_segurado` field on the `post` post type. The field
returns a structured object with all segurado attributes. Use
`register_meta()` with `show_in_rest => true` for individual field
updates via the standard REST API post update endpoint.

**Rationale**: Follows the existing pattern in the codebase. The
`vb_segurado` field provides a read-optimized aggregate for the
display page, while `register_meta()` enables granular updates from
the Vue inline editor via `POST /wp-json/wp/v2/posts/{id}`.

**Alternatives considered**:
- *Custom REST route* — violates Constitution Principle IV ("Do not
  invent ad-hoc endpoints outside the WP REST API"). Rejected.
- *Only `register_rest_field()` without `register_meta()`* — would
  require a custom update callback, which is essentially an ad-hoc
  endpoint. Rejected.

## R3 — Vue inline editing architecture

**Decision**: Add a new Vue app mount in `assets/js/app.js` targeting
a `vb-segurado-edit` wrapper element. The app:
1. Reads initial field values from `data-*` attributes on the
   server-rendered markup (already escaped by PHP).
2. On field blur, sends `POST /wp-json/wp/v2/posts/{id}` with the
   updated meta fields and `X-WP-Nonce` header from `vbData.nonce`.
3. Shows inline success/error feedback.
4. Falls back to full form submission if REST API is unavailable.

**Rationale**: Matches the existing pattern in `app.js` (header,
search, feed apps). Uses the `vbData` global for REST URL and nonce.
The `data-*` attribute approach follows the existing pattern (e.g.,
`data-total-pages` on the feed element).

**Alternatives considered**:
- *Separate Vue component file* — would require a build step,
  violating the CDN-first principle. Rejected.
- *Full page reload on edit* — poor UX; rejected per FR-006.

## R4 — Form validation strategy

**Decision**: Implement validation in two layers:
1. **Server-side (PHP)**: `sanitize_text_field()`, `sanitize_email()`,
   `sanitize_phone()` (custom), and `wp_kses_post()` for address
   fields. Validation on `save_post` hook or REST API
   `pre_insert_post` filter.
2. **Client-side (Vue)**: Real-time validation on input/blur using
   the same rules. Show inline error messages.

**Rationale**: Server-side validation is mandatory for data
integrity. Client-side validation provides immediate feedback.
Both layers use consistent rules per FR-004.

**Alternatives considered**:
- *Client-side only* — insecure; rejected.
- *Server-side only* — poor UX; rejected per FR-004.

## R5 — CSS token usage for segurado section

**Decision**: Add new CSS custom properties to `:root` in `style.css`
for segurado-specific tokens (e.g., `--vb-segurado-bg`,
`--vb-segurado-border`, `--vb-segurado-radius`). Reference these in
the segurado section styles. No hard-coded color or spacing values.

**Rationale**: Constitution Technical Constraints require all visual
tokens to live as CSS custom properties at `:root`.

**Alternatives considered**:
- *Hard-coded values* — violates Constitution. Rejected.
- *Reuse existing tokens* — may not provide sufficient visual
  distinction for the segurado section. Rejected.

## R6 — i18n strategy for segurado fields

**Decision**: All user-facing strings (labels, placeholders, error
messages) use the `vue-blocks` text domain. Strings are passed to
Vue via `vbData.i18n` (extended in `wp_localize_script()`). PHP
templates use `esc_html_e()` and `esc_attr_e()`.

**Rationale**: Follows the existing i18n pattern in the codebase.
Constitution Technical Constraints require `vue-blocks` text domain.

**Alternatives considered**:
- *Hard-coded strings* — violates i18n requirements. Rejected.
- *Separate text domain* — unnecessary; rejected.

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clear enough
that defaults documented in the spec's `## Assumptions` section
hold.

## Deferred to `/speckit.tasks`

- Exact meta key names (e.g., `vb_segurado_full_name` vs
  `vb_segurado_name`).
- Exact CSS token names and values.
- Exact Vue app mount point ID and data attribute names.
- Exact validation regex patterns for CPF/CNPJ formats.