# Implementation Plan: Theme Branding & Customizer Integration

**Branch**: `004-theme-customizer-and-branding` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/004-theme-customizer-and-branding/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Polish the Safe Mídia integration in three coordinated changes:
1. **Branding**: the Docker bootstrap sets the WordPress site title to
   "Safe Mídia" on fresh installs (the theme name in `style.css` stays
   "Vue Blocks").
2. **Fonts**: `functions.php` enqueues Google Fonts (Merriweather +
   Inter) with preconnect and `display=swap` so `.logo-text` and the
   rest of the Safe Mídia typography actually render in the intended
   faces.
3. **Customizer**: a new "Social media" panel in `wp-admin →
   Appearance → Customize` exposes four URL fields (Facebook,
   Instagram, X, LinkedIn) consumed by the existing navbar and
   footer template parts.

A side deliverable — `theme/DESIGN_LAYOUTS.md` — inventories the
nine entries under `layouts-html/`.

## Technical Context

**Language/Version**:
- PHP 7.4+ (runtime, theme's existing constraint).
- Plain CSS, plain JavaScript (no build step — per Constitution
  Principle V).

**Primary Dependencies**:
- WordPress 6.0+ (runtime, theme's existing constraint).
- The Customizer API (`WP_Customize_Manager`, `theme_mod`,
  `add_setting`, `add_control`) — built into WordPress core, no
  third-party plugin needed.
- Google Fonts (Merriweather, Inter) — loaded over HTTPS from
  `fonts.googleapis.com`.

**Storage**:
- Files only (one new Markdown file).
- Customizer data persisted via `theme_mod` in the WordPress
  `wp_options` table under the theme slug.

**Testing**:
- Manual: open the Customizer, set a Facebook URL, publish, reload
  the front end, confirm the icon links correctly; clear the URL,
  republish, confirm the icon hides.
- Static: grep the rendered HTML for the expected `<link rel="preconnect">`
  count (1 per host), the `<link rel="stylesheet">` to Google Fonts
  with the documented query, and the four `<a class="soc">` /
  `<a class="footer-soc">` hrefs.
- Manual: confirm a fresh `docker compose down -v && docker compose up
  -d` leaves `blogname` equal to "Safe Mídia".

**Target Platform**:
- The theme runs in any WordPress 6.0+ / PHP 7.4+ environment.
- No host-side tooling required (no Node.js, no bundler).

**Project Type**: Web application — additive work in an existing
WordPress theme; no new runtime or build surfaces.

**Performance Goals**:
- Font load: non-blocking, `display=swap`, preconnect — no impact on
  time-to-interactive.
- Customizer: a single new panel with four settings; no measurable
  overhead.

**Constraints**:
- Theme name "Vue Blocks" preserved (FR-002).
- Site title is overridable via `WP_SITE_TITLE` env var
  (documented in `.env.example`).
- Customizer URL fields are validated as URLs (basic `esc_url_raw` /
  `sanitize_callback`); empty URLs render no `<a>` element.
- The `theme/DESIGN_LAYOUTS.md` file ships with the packaged theme
  (no `package.sh` exclusion needed — it lives inside `theme/`, not
  at the repo root).
- No additional Customizer settings beyond the four social URLs in
  v1.

**Scale/Scope**:
- One new Markdown file.
- One `wp_enqueue_style` call (the Google Fonts URL).
- One Customizer panel with four settings.
- A few lines of PHP in each of `theme/functions.php`,
  `theme/template-parts/header/navbar.php`,
  `theme/template-parts/footer/site-footer.php`.
- One env-var default change in `.env.example` and `bin/bootstrap.sh`.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | The Customizer writes to `theme_mod` (server-side, persisted in `wp_options`). The navbar and footer template parts read these values via PHP and emit `<a>` elements server-side; no client-side state is required for the social icons to be correct. |
| II. Progressive Enhancement | **Pass** | The site's no-JS fallback continues to render correctly: navbar links and footer columns work without JS; the social icons are static `<a>` tags. The font load uses `display=swap` so text is visible immediately with the system fallback even before Google Fonts arrive. |
| III. WP Template Hierarchy Discipline | **Pass** | All new code lives inside `theme/` (the WordPress theme package). The Customizer integration hooks into the standard `customize_register` action; the social URL reads use the standard `get_theme_mod` API. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST endpoints are added or changed. The Customizer saves via the standard WordPress Customizer endpoint, not the REST API. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | The opt-in Vite pipeline (amended at v1.1.0) is unaffected. The theme still ships with no build step. |
| Technical Constraints | **Pass** | PHP 7.4+, WP 6.0+, Vue 3.4.x (for the existing interactivity layer), GPLv2+, `vue-blocks` text domain — all preserved. |
| Development Workflow | **Pass** | `vb_` / `VB_` / `vb-` naming continues. New code lives inside `theme/`. |
| Governance | **Pass** | v1.1.0 amendment preceded this plan; no further amendment needed. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

| Principle | Status post-design | Evidence |
|---|---|---|
| I–V (unchanged) | **Pass** | All Customizer + font-load work is server-side or non-blocking. |
| V (CDN-first) | **Pass** | Google Fonts CDN is a separate concern from the theme's own asset strategy; the theme's CSS/JS are still served as-is. |
| Technical Constraints | **Pass** | The new `wp_enqueue_style` call uses the WordPress-standard URL; no third-party hosting introduced. |

**Final gate verdict**: PASS.

## Project Structure

### Documentation (this feature)

```text
specs/004-theme-customizer-and-branding/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── README.md
│   ├── fonts.contract.md
│   └── customizer.contract.md
├── checklists/
│   └── requirements.md
└── spec.md              # already created
```

### Source Code (additions inside `theme/`)

```text
theme/
├── functions.php                    # +Google Fonts wp_enqueue_style + Customizer panel + show social settings
├── template-parts/header/
│   └── navbar.php                    # read 4 social theme_mods, conditionally render each <a class="soc">
├── template-parts/footer/
│   └── site-footer.php               # read 4 social theme_mods, conditionally render each <a class="footer-soc">
├── DESIGN_LAYOUTS.md                 # NEW — the layouts inventory (ships with the package)
└── ...                               # everything else unchanged
```

**Structure Decision**: Additive only. No files moved, renamed, or
deleted; no PHP files replaced wholesale. The Customizer integration
adds a single `add_action('customize_register', ...)` callback plus
helper functions to read the four settings.

**Repo root** (one-line edits):
- `bin/bootstrap.sh`: `WP_SITE_TITLE` default value changes from
  "Vue Blocks (local)" to "Safe Mídia".
- `.env.example`: `WP_SITE_TITLE` default changes to "Safe Mídia".

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.