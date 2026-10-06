# Phase 1 Data Model: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

This file enumerates the entities defined in the spec's `### Key
Entities` section with attributes, validation rules, and lifecycle
notes.

---

## E1 — Site Title

A single string option, persisted by WordPress's `blogname` option.

| Field | Type | Description |
|---|---|---|
| `blogname` | string | The site's public title. Initial value: "Safe Mídia" (from `WP_SITE_TITLE` env var). Editable via `wp-admin → Settings → General` or `wp-admin → Appearance → Customize → Site Identity`. |

### Validation

- Initial value MUST be the string "Safe Mídia" on a fresh install.
- The site owner MAY override the value at runtime; the bootstrap
  only sets the initial value.
- The `blogdescription` (tagline) is NOT touched by the bootstrap
  (per clarification Q1).

### Lifecycle

| State | Trigger |
|---|---|
| **Unset** | Brand-new WordPress install (before `wp core install`). |
| **Initial** | `wp core install --title="Safe Mídia"` during bootstrap. |
| **Customized** | An admin edited it via the WordPress admin UI. |

---

## E2 — Social URL

A URL string per platform (Facebook, Instagram, X, LinkedIn). Four
values total. Persisted via `theme_mod` keyed by the theme slug.

| Field | Type | Description |
|---|---|---|
| `theme_mod:vb_social_facebook` | string | Facebook profile / page URL. |
| `theme_mod:vb_social_instagram` | string | Instagram profile URL. |
| `theme_mod:vb_social_x` | string | X / Twitter profile URL. |
| `theme_mod:vb_social_linkedin` | string | LinkedIn profile URL. |

### Validation

- Each value is sanitized via `esc_url_raw` (Customizer's default
  URL sanitizer).
- An empty value is allowed and stored as `''`.
- No EXCEPT the contiguous Facebook domain (`facebook.com`) — the
  spec intentionally keeps the field open to any URL the admin
  enters (their own profile, a page, etc.).

### Lifecycle

| State | Trigger |
|---|---|
| **Unset** | Fresh install; the theme_mod has never been written. |
| **Empty** | Admin opened the Customizer and clicked Publish without filling the field. |
| **Set** | Admin entered a URL and clicked Publish. |
| **Cleared** | Admin filled, then cleared, the URL and clicked Publish. The next render omits the corresponding `<a>` element. |

---

## E3 — Layout

One of the 9 entries under `layouts-html/`. Each has a directory name
and an HTML file documenting its sections.

| Field | Type | Description |
|---|---|---|
| `slug` | string | Directory name (e.g., `01 - Home`, `02 - Listagem das notícias`). |
| `title` | string | Human-readable title (in the original Portuguese). |
| `v1Status` | enum | `in-scope` (drives the home page in v1) \| `deferred` (Safe Mídia reference, planned for v2) \| `reference-only` (design exploration; not planned). |

### Lifecycle

| State | Trigger |
|---|---|
| **Documented** | Listed in `theme/DESIGN_LAYOUTS.md` with title + v1 disposition. |
| **Implemented** | The layout's sections have a corresponding template part in `theme/template-parts/`. (v1 only: home.) |

---

## Cross-entity invariants

1. The Site Title (E1) is set by the bootstrap on first install;
   the site owner can override it at any time via the WordPress
   admin UI.
2. The four Social URLs (E2) are written via `set_theme_mod` from
   the Customizer's `customize_update` action and read via
   `get_theme_mod` from the navbar and footer template parts.
3. The Layout inventory (E3) is a documentation artifact; it has no
   runtime effect.

---

## Out-of-scope entities (deliberately omitted)

- **Logo URL / image** — the spec keeps the text-only logo for v1.
- **Footer-column copy / subscribe CTA URL** — explicitly out of
  scope; deferred to a future feature.
- **Tagline** (`blogdescription`) — explicitly out of scope per the
  clarification (the bootstrap leaves it empty).