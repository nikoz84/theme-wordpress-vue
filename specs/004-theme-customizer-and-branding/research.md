# Phase 0 Research: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

This file consolidates the research needed to resolve every
technology-choice question that emerged during planning.

## R1 — Font-loading approach

**Decision**: Use WordPress's `wp_enqueue_style` to load Google Fonts
with `<link rel="preconnect">` to `fonts.googleapis.com` and
`fonts.gstatic.com`, plus the stylesheet `<link>` with the documented
query string and `display=swap`.

**Rationale**: `wp_enqueue_style` is the canonical WordPress way to
enqueue CSS, automatically producing the right `<link>` tag with
the theme's `ver` query arg for cache busting. The `wp_resource_hint`
hook lets us add the two `preconnect` directives in the right
order. `display=swap` ensures text is visible immediately with the
system fallback even before Google Fonts arrive.

**Alternatives considered**:
- *Inline `@font-face` with self-hosted fonts* — would require
  shipping font files in `theme/`; rejected per spec out-of-scope
  and per the principle of CDN-first distribution.
- *Webfontloader.js* — extra runtime JS for marginal benefit;
  rejected.
- *Pure CSS `@import url(https://fonts.googleapis.com/...)`* — render-
  blocking; rejected.

## R2 — WordPress Customizer section registration

**Decision**: Register the new section + settings + controls via
`$wp_customize->add_section('vb_social')` plus four
`add_setting` / `add_control` pairs. Persist via `theme_mod`
(per spec FR-004).

**Rationale**: The Customizer API is the WordPress-native extension
point for theme-level configuration. `theme_mod` is the standard
storage location for per-theme values, isolated by the theme slug
and surfaced via `get_theme_mod('vb_social_facebook')` etc.

**Alternatives considered**:
- *Custom options page under `tools.php`* — adds UI surface the
  user has to discover; rejected.
- *Widget area for social links* — different semantic; rejected.

## R3 — Empty-URL rendering rule

**Decision**: When a social URL is empty (`''`), the corresponding
`<a>` element is omitted from the rendered HTML entirely (per spec
edge case "Empty Customizer URL").

**Rationale**: An `<a href="">` is visually broken (no destination)
and is also an accessibility anti-pattern (the icon looks clickable
but goes nowhere). Omitting the element when no URL is set gives a
clean appearance and avoids accessibility regressions.

**Alternatives considered**:
- *Render the icon, link to `/` as a placeholder* — confusing for
  users; rejected.
- *Render the icon, link to `javascript:void(0)`* — also broken;
  rejected.

## R4 — Site-title default propagation

**Decision**: The Docker bootstrap sets `blogname` to whatever
`WP_SITE_TITLE` is in `.env` (default: "Safe Mídia"). The
`blogdescription` is NOT set by the bootstrap (per spec clarification
Q1).

**Rationale**: The `wp core install --title="$WP_SITE_TITLE"`
command accepts the title as a CLI flag. Passing through the env var
keeps contributors' overrides working; the documented default is
"Safe Mídia".

**Alternatives considered**:
- *Hardcode "Safe Mídia" in the bootstrap script* — would make
  customization require editing the script; rejected.
- *Use the WordPress Customizer's "Site Identity" panel* — that's
  the admin's runtime override path; bootstrap default is the
  *initial* value, set on first install.

## R5 — `theme/DESIGN_LAYOUTS.md` location and packaging

**Decision**: Lives at `theme/DESIGN_LAYOUTS.md` per the spec's
clarification Q2. It is **not** excluded by `theme/package.sh`
(only meta files at the repo root are excluded; `theme/*` files
ship).

**Rationale**: The inventory travels with the packaged theme so
downstream maintainers can reference what layouts informed the
theme without checking the upstream repo. Storage cost is
negligible (a few KB of Markdown).

**Alternatives considered**:
- *Repo-root `docs/layouts-inventory.md`* — rejected by Q1 from the
  user (per clarification Q2).
- *Inside `specs/004-.../`* — co-located with this spec but would
  not ship with the theme; rejected.

## R6 — Layouts-inventory content shape

**Decision**: The inventory is a single Markdown file with one table
per layout under `layouts-html/`, each row giving the layout's title,
the Safe Mídia sections present, and the v1 disposition (in scope /
deferred / reference only). Plus a short header with the inventory
purpose and a footer pointing to `DESIGN.md`.

**Rationale**: One table per layout is easy to scan; the disposition
column tells them at a glance whether the layout will be implemented
in v1 or later.

**Alternatives considered**:
- *Free-form prose per layout* — harder to scan; rejected.
- *JSON manifest* — machine-friendly at the cost of human-readable
  rendering; rejected for contributor-facing docs.

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified during
`/speckit.clarify` (site title text = "Safe Mídia"; inventory
location = `theme/DESIGN_LAYOUTS.md`) and all defaults documented in
the spec's `## Assumptions` section hold.

## Deferred to `/speckit.tasks`

- The exact sanitize-skip / fallback behavior of the four Customizer
  URL controls (basic URL sanitization via `esc_url_raw` is
  sufficient).
- The exact wording of the Customizer panel description.
- The exact layout-inventory table contents (per-row data per
  layout).