# Phase 1 Data Model: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

This file enumerates the entities defined in the spec's `### Key
Entities` section, with attributes, validation rules, and lifecycle
notes for each.

---

## E1 — Design Token

A name → CSS custom property binding. The single source of truth is
`theme/style.css`'s `:root` block.

| Field | Type | Description |
|---|---|---|
| `name` | string (kebab-case) | Token name (e.g., `color-primary`, `font-serif`). |
| `category` | enum | `color` \| `font` \| `spacing` \| `radius` \| `shadow`. |
| `value` | string | The computed value (e.g., `#1E50D4`, `Merriweather, serif`, `0 4px 12px rgba(0,0,0,.08)`). |
| `usage` | string | One-line note describing where the token is consumed (e.g., "primary action background"). |

### Validation

- `name` MUST be lowercase kebab-case.
- `value` MUST be valid CSS for its `category`:
  - `color`: a `#rrggbb` hex (or rgba / named color).
  - `font`: a CSS `font-family` value (comma-separated list, ending
    with a generic family).
  - `spacing`: a CSS length (`px`, `em`, `rem`, `%`).
  - `radius`: a CSS length or `0`.
  - `shadow`: a CSS `box-shadow` / `text-shadow` value.
- `theme/style.css` MUST declare every token at `:root`.
- `DESIGN.md` MUST list every token with its computed value.

### Lifecycle

| State | Trigger |
|---|---|
| **Declared** | `theme/style.css`'s `:root` block lists the token. |
| **Documented** | `DESIGN.md` includes the token in its token catalog. |
| **In use** | A consumer (CSS rule, component) references `var(--name)`. |
| **Removed** | A contributor deletes the declaration; remaining `var()` references resolve to `unset` (visually broken — requires explicit follow-up). |

---

## E2 — Component

A reusable UI block documented in `DESIGN.md` and implemented as a
`template-parts/` file (PHP).

| Field | Type | Description |
|---|---|---|
| `slug` | string (kebab-case) | Component slug (e.g., `navbar`, `hero-main`, `news-card`). |
| `title` | string | Human-readable title for `DESIGN.md`. |
| `purpose` | string | One-paragraph description of what the component does. |
| `useWhen` | list<string> | Conditions under which the component is the right choice. |
| `avoidWhen` | list<string> | Conditions under which the component should NOT be used. |
| `tokens` | list<string> | CSS custom properties the component depends on. |
| `templatePartPath` | path | Path to the implementing file inside `theme/`. |

### Validation

- `slug` MUST be unique inside the theme.
- `templatePartPath` MUST exist at planning time (the
  implementation phase creates it).
- `tokens` MUST all be declared at `:root` in `theme/style.css`.

### Lifecycle

| State | Trigger |
|---|---|
| **Designed** | `DESIGN.md` describes the component. |
| **Implemented** | `templatePartPath` file exists and renders correctly. |
| **Used** | A page template (`front-page.php`, `page.php`, etc.) includes the template part. |
| **Removed** | The contributor deletes the `DESIGN.md` entry AND the file. |

---

## E3 — Section

A logical grouping on the home page — navbar, hero, news grid, footer
(in v1 scope). Each section is implemented as one or more components.

| Field | Type | Description |
|---|---|---|
| `slug` | string | Section slug (e.g., `hero`, `news-grid`). |
| `order` | int | The section's position on the home page (1-indexed). |
| `components` | list<string> | Component slugs the section is composed of. |
| `templateFile` | path | The PHP file that renders this section (typically `front-page.php` for v1). |

### v1 Sections

| `slug` | `order` | `components` | `templateFile` |
|---|---|---|---|
| `navbar` | 1 | `navbar` | `front-page.php` (header part) |
| `hero` | 2 | `hero-main`, `hero-aside` | `front-page.php` (hero part) |
| `news-grid` | 3 | `news-card` (×6) | `front-page.php` (news grid part) |
| `footer` | 4 | `site-footer` | `front-page.php` (footer part) |

### Validation

- `order` MUST be unique per section.
- Every `component` MUST exist in the Components inventory.

### Lifecycle

| State | Trigger |
|---|---|
| **Specified** | Listed in `DESIGN.md`'s layout-pattern section. |
| **Rendered** | The `templateFile` emits the section's HTML on the home page. |

---

## E4 — Theme Package

The zip deliverable, produced from the `theme/` directory.

| Field | Type | Description |
|---|---|---|
| `sourceDir` | path | Always `./theme/` relative to the repo root. |
| `outputZipPath` | path | Default: `./<theme-slug>-<version>.zip` (e.g., `vue-blocks-1.0.0.zip`). |
| `includedFiles` | set<path> | The files that MUST be present in the archive. |
| `excludedPaths` | set<path> | The paths that MUST NOT appear (test harness, build outputs). |

### Validation

- `includedFiles` MUST contain at minimum: `style.css`,
  `functions.php`, `index.php`, `front-page.php`, `header.php`,
  `footer.php`, `DESIGN.md`, `assets/js/app.js`,
  `template-parts/`, `inc/`, `screenshot.png`, `readme.txt`,
  `languages/`.
- `excludedPaths` MUST contain at minimum: `node_modules/`,
  `dist/`, `docker-compose.yml`, `.env`, `bin/`, `seed/`,
  `config/`, `package.json`, `vite.config.js`, `package.sh`,
  `PACKAGE.md`.
- The post-build `grep` test MUST succeed for the produced zip.

### Lifecycle

| State | Trigger |
|---|---|
| **Not built** | `theme/` exists; no zip artifact yet. |
| **Built** | `package.sh` (or `zip` command) produced a zip in the repo root. |
| **Installed** | A contributor uploaded the zip into a WordPress site's `wp-content/themes/` directory and activated the theme. |

---

## E5 — Test Harness

The Docker compose stack at the repo root, used to test the theme
locally.

| Field | Type | Description |
|---|---|---|
| `composeFile` | path | Always `./docker-compose.yml`. |
| `bindMount` | mapping | `host:container`; the path inside `theme/` mapped into `/var/www/html/wp-content/themes/vue-blocks`. |
| `bootstrapScript` | path | `bin/bootstrap.sh`. |
| `seedFile` | path | `seed/sample-content.xml`. |

### Validation

- The bind mount MUST resolve to the `theme/` subdirectory.
- `bootstrap.sh` MUST continue to run unchanged after the
  restructuring (the script is unaware of the host path).
- The harness MUST NOT be included in the packaged zip (per
  E4's `excludedPaths`).

### Lifecycle

| State | Trigger |
|---|---|
| **Stopped** | Containers are not running. |
| **Booting** | `docker compose up -d` was issued; db is starting. |
| **Healthy** | The db healthcheck passes; bootstrap is running. |
| **Running** | The wordpress container is up; the theme is active in WP; the home page is reachable. |
| **Torn down** | `docker compose down [-v]` was issued. |

---

## Cross-entity invariants

1. The Design Token catalog (E1) is the canonical source of every
   visual value; the Components (E2) and Sections (E3) consume
   tokens via `var()` references — never hard-coded.
3. The Theme Package (E4) is built from the union of E1 + E2 + E3
   + the supporting PHP files; the Test Harness (E5) is excluded.
4. The Test Harness (E5) consumes the Theme Package (E4) via a
   bind mount; the harness is otherwise unaware of E1–E4.

---

## Out-of-scope entities (deliberately omitted)

- **Production deployment** of the packaged theme — the deliverable
  is the zip itself.
- **WordPress multisite** — not requested in the spec.
- **Theme marketplace submission** — the theme ships as a zip;
  marketplace metadata is out of scope.