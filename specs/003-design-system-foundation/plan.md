# Implementation Plan: Design System & Theme Package Foundation

**Branch**: `003-design-system-foundation` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/003-design-system-foundation/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Move the theme's runtime files into a top-level `theme/` subdirectory,
generate a `DESIGN.md` design-system reference (with a duplicate copy
shipped inside `theme/`), and reimplement the home page to match the
four core Safe Mídia sections (navbar, hero with sidebar posts, news
grid, footer). After the restructuring, the theme can be packaged into
a `.zip` independently of the Docker testing harness; the harness at
the repo root keeps working unchanged.

The Constitution as amended to v1.1.0 binds this work: no build step,
CSS tokens at `:root`, server-first rendering, progressive
enhancement, REST API as the PHP↔Vue bridge.

## Technical Context

**Language/Version**:
- PHP 7.4+ (runtime, theme's existing constraint).
- Plain CSS (no preprocessor, no PostCSS — per Constitution Principle
  V "no build step").
- Plain JavaScript (no transpiler; the existing `assets/js/app.js`
  pattern continues unchanged).
- Bash (for the packaging script).

**Primary Dependencies**:
- WordPress 6.0+ runtime (theme's existing constraint).
- Vue.js 3.4.x from a public CDN (CDN-first, unchanged).
- The existing official WordPress image for local testing (`wordpress:6`
  in the Docker harness).

**Storage**:
- Files only. No database changes.
- The packaged theme is a directory tree inside `theme/`; the
  deliverable is a `.zip` produced by the `zip` CLI.

**Testing**:
- Manual: open the rendered home page and visually compare to the Safe
  Mídia reference.
- Static: `zip -T` to verify zip integrity; a `grep -R` of the
  packaged archive to confirm no test-harness paths leaked in.
- Visual: load the page at the documented desktop and tablet
  breakpoints.

**Target Platform**:
- The theme itself runs in any WordPress 6.0+ / PHP 7.4+ environment.
- The packaging step runs on Linux / macOS / WSL2 (any host with
  `zip`).

**Project Type**: Web application — a WordPress theme (the public
  deliverable) plus a one-shot packaging / restructuring workflow.

**Performance Goals**:
- Theme: unchanged from the inherited Vue Blocks theme baseline
  (CDN-first, no build step).
- Packaging: ≤ 5 MB zip in ≤ 30 s (per SC-002).

**Constraints**:
- No build step in the theme (FR-010 / Constitution Principle V).
- All visual values are CSS custom properties at `:root` (FR-002).
- The packaged theme contains only runtime files — no Docker, no
  tests, no docs (the in-theme `DESIGN.md` is the one exception
  allowed by FR-001a).
- The Docker harness at the repo root keeps working without path
  edits (SC-005).

**Scale/Technical Context**:
- 4 core home-page sections (navbar, hero, news grid, footer).
- ~30 design tokens (colors, type, spacing, radii, shadows).
- ~8 components (navbar, hero-main, hero-aside, news-card, footer,
  logo, button, social).
- One packaging manifest file (e.g., `theme/PACKAGE.md`) listing the
  expected zip contents.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | The home page renders via the WordPress template hierarchy (`front-page.php`, `index.php`, `template-parts/`). Vue continues to layer behavior on already-rendered HTML. |
| II. Progressive Enhancement | **Pass** | No interactive feature is JS-only. The native search form, pagination, and post links remain; the Vue enhancements (menu, dark mode, search dropdown, load-more) are add-ons. |
| III. WP Template Hierarchy Discipline | **Pass** | New template files live under `theme/` and follow the existing pattern (`index.php` as fallback, `front-page.php` for the home, `template-parts/` for sections, `inc/` for helpers). |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST surface changes. The load-more-posts button continues to call `/wp-json/wp/v2/posts` with the `vbData.nonce`. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | The opt-in Vite pipeline (amended at v1.1.0) is untouched by this feature; the theme ships without a build step. `dist/` and `node_modules/` remain absent from the packaged theme. |
| Technical Constraints | **Pass** | PHP 7.4+, WP 6.0+, Vue 3.4.x, GPLv2+, `vue-blocks` text domain — all preserved. CSS tokens at `:root` per FR-002. |
| Development Work | **Pass** | File ownership preserved. New files inside `theme/` follow the `vb_*` / `VB_*` / `vb-` naming where applicable. |
| Governance | **Pass** | v1.1.0 amendment preceded this plan; no further amendment needed. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

| Principle | Status post-design | Evidence |
|---|---|---|
| I–V (unchanged) | **Pass** | Restructuring is purely organizational; no rendering surface changes. |
| V (CDN-first) | **Pass** | The packaged theme contains no `dist/` or `node_modules/`; SC-006 enforces this. |
| Technical Constraints | **Pass** | `theme/style.css`'s `:root` block is the canonical token source; `DESIGN.md` mirrors it. |
| Development Work | **Pass** | New files in `theme/` use the existing naming conventions; new files at the repo root are package-level (`DESIGN.md`, `theme/PACKAGE.md`) and not theme-internal. |

**Final gate verdict**: PASS.

## Project Structure

### Documentation (this feature)

```text
specs/003-design-system-foundation/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── README.md
│   ├── design-tokens.schema.md
│   └── theme-package.manifest.md
├── checklists/
│   └── requirements.md
└── spec.md              # already created
```

### Source Code (repository root + theme/)

```text
# Repo root — design system reference + Docker harness (UNCHANGED HARNESS)
.
├── DESIGN.md                                # NEW — canonical design-system reference
├── package.json                             # untouched by Vite feature (this feature doesn't add build steps)
├── vite.config.js                           # untouched (this feature doesn't touch the Vite path)
├── docker-compose.yml                       # unchanged; bind-mount target updates to ./theme
├── .env.example                             # unchanged
├── .gitignore                               # unchanged
├── .dockerignore                            # unchanged
├── README.md                                # unchanged (Docker quickstart doc)
├── bin/
│   ├── bootstrap.sh                         # unchanged
│   └── preflight.sh                         # unchanged
├── seed/
│   └── sample-content.xml                   # unchanged
├── config/
│   └── php.ini                              # unchanged
└── theme/                                   # NEW top-level subdirectory; the packaged deliverable
    ├── DESIGN.md                            # NEW — duplicate of repo-root DESIGN.md (FR-001a)
    ├── style.css                            # MOVED from repo root; re-tokenized to Safe Mídia palette
    ├── functions.php                        # MOVED from repo root; Vue CDN-first enqueue preserved
    ├── index.php                            # MOVED — fallback template
    ├── front-page.php                       # MOVED — home page (the v1 deliverable)
    ├── header.php                           # MOVED
    ├── footer.php                           # MOVED
    ├── sidebar.php                          # MOVED (if used)
    ├── comments.php                         # MOVED
    ├── search.php                           # MOVED
    ├── searchform.php                       # MOVED
    ├── 404.php                              # MOVED
    ├── page.php                             # MOVED
    ├── archive.php                          # MOVED
    ├── single.php                           # MOVED
    ├── screenshot.png                       # MOVED
    ├── readme.txt                           # MOVED
    ├── PACKAGE.md                           # NEW — describes the packaged deliverable
    ├── package.sh                           # NEW — `cd theme && bash package.sh` produces the zip
    ├── assets/
    │   └── js/
    │       └── app.js                       # MOVED — Vue 3 layer, unchanged as code
    ├── template-parts/
    │   ├── content.php                      # MOVED
    │   ├── content-none.php                 # MOVED
    │   ├── header/
    │   │   └── navbar.php                  # NEW — Safe Mídia navbar component
    │   ├── hero/
    │   │   └── hero.php                    # NEW — Safe Mídia hero + sidebar
    │   ├── news/
    │   │   ├── news-grid.php               # NEW — Safe Mídia news grid
    │   │   └── news-card.php               # NEW — single news card
    │   └── footer/
    │       └── site-footer.php             # NEW — Safe Mídia footer
    ├── inc/
    │   ├── template-tags.php                # MOVED
    │   ├── build-manifest.php               # MOVED (from Vite feature; preserved unchanged)
    │   └── nav-walker.php                   # NEW — wraps wp_nav_menu for the Safe Mídia navbar
    └── languages/                           # MOVED (untouched)
```

**Structure Decision**: The deliverable theme lives in `theme/`. The
repo root retains all testing infrastructure (Docker, .env, bin/,
seed/, config/, README.md) plus the new `DESIGN.md` reference. The
package script `theme/package.sh` operates only on the `theme/`
directory.

**Migration from current state**: Today's Vue Blocks theme files at the
repo root move into `theme/`. The Docker harness's bind-mount
(`- ./theme:/var/www/html/wp-content/themes/vue-blocks`) needs no
edits — it already pointed at the repo root's theme files; now it
points at `theme/` (the new home of those files).

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.