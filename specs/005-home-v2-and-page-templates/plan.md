# Implementation Plan: Home-Page Sections v2 & Page Templates

**Branch**: `005-home-v2-and-page-templates` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/005-home-v2-and-page-templates/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Bring the remaining eight Safe Mídia home-page sections into the
shipped theme (Newsletter Compact, Para o Segurado, Newsletter Grande,
Análise de Mercado, Category tabs, Mais Lidas, Boletim Regulatório,
Colunistas) and refine the single / archive / search / 404 page
templates to the Safe Mídia visual language. The "Colunistas"
WordPress category is auto-created on theme activation
(idempotent). No new build step is introduced.

## Technical Context

**Language/Version**:
- PHP 7.4+ (runtime, theme's existing constraint).
- Plain CSS, plain JavaScript (no build step — per Constitution
  Principle V).

**Primary Dependencies**:
- WordPress 6.0+ (theme's existing constraint).
- WordPress core APIs: `WP_Query`, `wp_get_recent_posts`,
  `get_the_category`, `get_the_author_meta`,
  `wp_create_category` / `category_exists`, `wp_get_post_terms`,
  `wp_list_categories`, `wp_nav_menu` (the registered "primary" menu).
- No third-party plugins required.

**Storage**:
- Files only (no new database tables).
- The "Colunistas" WordPress category (auto-created, persisted via
  the standard `wp_terms` / `wp_term_taxonomy` tables).

**Testing**:
- Manual: visit the home page and confirm the eight deferred
  sections render in the documented order; visit any single post's
  permalink; visit `/category/...`, `/?s=foo`, and a 404 URL.
- Static: confirm the rendered HTML contains the documented CSS
  class names per section (per SC-001).

**Target Platform**:
- The theme runs in any WordPress 6.0+ / PHP 7.4+ environment.
- No host-side tooling required (no Node.js, no bundler).

**Project Type**: Web application — additive work in an existing
WordPress theme; the same theme that holds the existing four v1
sections, the Customizer integration, and the layouts-html reference
documents.

**Performance Goals**:
- Each home section renders with one `WP_Query` (no nested loops);
  the home page's total query count is bounded.
- Colunistas section renders with one query (`get_posts` grouped
  by author); category-tabs section reuses the existing news-card
  template.

**Constraints**:
- All new sections consume CSS tokens at `:root` (per FR-005 and
  Constitution Principle III).
- The "Colunistas" auto-create is idempotent (per FR-004a) and
  graceful — if it fails (e.g., permissions), the section degrades
  to "hidden" via the empty-state rule.
- No custom post types; no custom taxonomies (per Assumptions).
- The package produced by `theme/package.sh` continues to be ≤ 5 MB
  (per FR-007 / SC-005).

**Scale/Scope**:
- 8 new template parts under `theme/template-parts/home/`.
- 4 page templates refined: `single.php`, `archive.php`,
  `search.php`, `404.php` (with `single.php` being the most
  extensive).
- 1 small addition to `theme/inc/bootstrap.php`-like setup
  (the Colunistas category registration).
- One new helper function or two for "Colunistas" card rendering
  in `theme/inc/template-tags.php`.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | All new sections render server-side via `WP_Query`. The single-post template, archive, search, 404 are all server-rendered PHP. |
| II. Progressive Enhancement | **Pass** | The newsletter forms are static (no JS required); the tabs section uses CSS-only toggle if implemented as tabs (or simple `<details>`-free markup). No new JS dependencies. |
| III. WP Template Hierarchy Discipline | **Pass** | `single.php`, `archive.php`, `search.php`, `404.php` are the standard WordPress template filenames; they live under `theme/` per the Constitution. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST endpoints added or changed. The existing `vbData` localization continues; no new Vue integration. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | No build step introduced. The opt-in Vite pipeline (amended at v1.1.0) is preserved at the repo root. |
| Technical Constraints | **Pass** | PHP 7.4+, WP 6.0+, Vue 3.4.x (existing layer), GPLv2+, `vue-blocks` text domain — all preserved. |
| Development Workflow | **Pass** | New code lives inside `theme/template-parts/home/`; new PHP files use the existing naming and helpers. |
| Governance | **Pass** | v1.1.0 amendment preceded this plan; no further amendment needed. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

| Principle | Status post-design | Evidence |
|---|---|---|
| I–V (unchanged) | **Pass** | Design adds no new client-side requirement; everything is server-rendered or static. |
| V (CDN-first) | **Pass** | New section HTML is shipped as-is; no Vite involvement. |
| Technical Constraints | **Pass** | New CSS additions append to `theme/style.css`'s `:root` and component rules; no new dependency. |

**Final gate verdict**: PASS.

## Project Structure

### Documentation (this feature)

```text
specs/005-home-v2-and-page-templates/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── README.md
│   ├── home-sections.contract.md
│   ├── single-post.contract.md
│   └── colunistas.contract.md
├── checklists/
│   └── requirements.md
└── spec.md              # already created
```

### Source Code (additions / changes inside `theme/`)

```text
theme/
├── functions.php                          # +Colunistas auto-create (FR-004a)
├── template-parts/
│   ├── home/                              # NEW directory
│   │   ├── newsletter-compact.php         # NEW
│   │   ├── segurado.php                   # NEW
│   │   ├── newsletter-grande.php          # NEW
│   │   ├── analise.php                    # NEW
│   │   ├── category-tabs.php              # NEW
│   │   ├── mais-lidas.php                 # NEW
│   │   ├── boletim.php                    # NEW
│   │   └── colunistas.php                 # NEW
│   ├── content.php                        # existing; still used by single.php
│   ├── content-page.php                   # existing; refined
│   └── content-none.php                   # existing; unchanged
├── single.php                             # REWRITE to full editorial layout (per Q2)
├── archive.php                            # REFINE to Safe Mídia news grid
├── search.php                             # REFINE to Safe Mídia search results
├── 404.php                                # REFINE to Safe Mídia 404 message
├── front-page.php                         # MODIFY — compose the new sections into the home
├── inc/
│   └── template-tags.php                  # +helper for Colunista cards
└── ...                                    # everything else unchanged
```

**Structure Decision**: Additive only for new sections (a new
`template-parts/home/` directory); refinements to existing page
templates are in-place edits. The four v1 sections continue to be
rendered by their existing template parts; the new sections compose
into `theme/front-page.php` between them in the documented order.

**Repo root** (no changes needed):
- The Docker harness continues to mount `./theme` → WP theme dir.
- `theme/package.sh` continues to package the theme (new section
  files ship inside the package; FR-007 / SC-005).

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.