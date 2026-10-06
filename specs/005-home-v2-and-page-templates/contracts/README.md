# Contracts: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

This directory holds the interface definitions for the new home
sections, the single-post template, and the Colunistas category
auto-creation.

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `home-sections.contract.md` | Theme → Visitor | Defines the eight new sections' shape, content source, and CSS hooks |
| `single-post.contract.md` | Theme → Visitor | Defines the single-post template's structure (per Q2: full editorial layout) |
| `colunistas.contract.md` | Theme → WordPress | Defines the Colunistas category auto-creation behavior (per Q3) |

## Contract reference

### Home sections (per `home-sections.contract.md`)

The home page composes 12 sections in the documented order. Each
new section has:

- A `theme/template-parts/home/<slug>.php` file that emits the
  section's HTML
- A `data-source` (which WordPress query supplies the data)
- An `empty-state` rule (what happens when no data is available)
- The Safe Mídia CSS class names the section's HTML uses

The sections are:

| Section | Data source | Empty state |
|---|---|---|
| Newsletter Compact | none (static form) | always rendered |
| Para o Segurado | 4 most recent posts (excluding the hero + sidebar) | rendered as "loading..." if query returns 0 |
| Newsletter Grande | none (static form) | always rendered |
| Análise de Mercado | 1 featured + 4-list (category `analise`, fallback to tag) | section hidden if no posts |
| Category tabs | 4 most populated categories, each with 4 latest posts | section hidden if no categories |
| Mais Lidas da Semana | 6 most recent posts (any category) | section hidden if no posts |
| Boletim Regulatório | posts in category `regulatorio` (fallback to tag) | section hidden if no posts |
| Colunistas | distinct authors in `colunistas` category | section hidden per FR-004 |

### Single post (per `single-post-contract.md`)

`theme/single.php` composes, in order:

1. The featured-image hero (Safe Mídia `.post-hero` markup)
2. The article header (title in Merriweather, byline with author
   + date, category chip)
3. The article body (`.entry-content` body class; standard
   `the_content()` invocation)
4. The "Leia também" rail (3 related posts per E3)

### Colunistas category (per `colunistas.contract.md`)

`theme/functions.php` registers an `after_setup_theme` callback
(`vb_register_colunistas_category`) that:

- Checks `category_exists('colunistas')`.
- If false, calls `wp_create_category('Colunistas')` (with the
  default `wp_create_category` description and slug).
- Is idempotent — repeated invocations no-op.

## Cross-contract invariants

1. The home-sections contract depends on the data-model entities
   being well-defined (`WP_Query`, `wp_get_recent_posts`,
   `get_the_category`).
2. The single-post contract depends on the `get_the_post_thumbnail_url`
   for the featured image (with the existing `vb_placeholder_image()`
   fallback).
3. The colunistas contract depends on the standard WordPress
   taxonomy APIs; no custom tables.

## Out-of-scope surfaces (deliberately omitted)

- REST endpoints — none added by this feature.
- Customizer settings — none added (the social Customizer from
  feature 004 is unchanged).
- New widget areas — none added.
- New database tables — none added.