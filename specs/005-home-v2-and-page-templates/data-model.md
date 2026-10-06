# Phase 1 Data Model: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

This file enumerates the entities defined in the spec's `### Key
Entities` section with attributes, validation rules, and lifecycle
notes.

---

## E1 — Newsletter Subscription (deferred)

A future v3 entity. For v2 the newsletter forms render static
HTML; there is no subscription persistence.

| Field | Type | Description |
|---|---|---|
| (none) | — | v2 ships no fields. The form element has no `action` and is guarded by `onsubmit="return false;"`. |

### Lifecycle

Not applicable in v2.

---

## E2 — Colunista Card

A derived entity. One card per distinct `post_author` in the
"Colunistas" WordPress category.

| Field | Type | Description |
|---|---|---|
| `author_id` | int | The post author's WordPress user ID. |
| `author_display_name` | string | `get_the_author_meta('display_name', $author_id)`. |
| `author_bio` | string | `get_the_author_meta('user_description', $author_id)` (or empty). |
| `avatar_url` | string | `get_avatar_url($author_id)` (or empty — the template handles via the existing `vb_placeholder_image()`). |
| `latest_post_id` | int | The most recent published post ID by this author in the "Colunistas" category. |
| `latest_post_title` | string | `get_the_title($latest_post_id)`. |
| `latest_post_permalink` | string | `get_permalink($latest_post_id)`. |
| `latest_post_thumbnail_url` | string | `get_the_post_thumbnail_url($latest_post_id, 'vb-card')` (or placeholder). |

### Validation

- Distinct authors are computed by `array_unique` over
  `WP_Query->posts[*]->post_author`.
- The card list is empty when no posts exist in "Colunistas" —
  the section is hidden per FR-004 (empty-state rule).

### Lifecycle

| State | Trigger |
|---|---|
| **Empty** | The "Colunistas" category has 0 published posts. |
| **Populated** | The category has ≥ 1 published post; cards are rendered. |

---

## E3 — Related Post (single post template)

A derived entity. Three posts at most, sourced from the current
post's category, excluding the current post.

| Field | Type | Description |
|---|---|---|
| `post_id` | int | The related post's WordPress post ID. |
| `post_title` | string | `get_the_title($post_id)`. |
| `post_permalink` | string | `get_permalink($post_id)`. |
| `post_thumbnail_url` | string | `get_the_post_thumbnail_url($post_id, 'vb-card')` (or placeholder). |
| `post_date` | DateTime | `get_post_time('U', true, $post_id)`. |

### Validation

- Sourced via `WP_Query` with `post__not_in = [current_post_id]`,
  `category__in = [current_post_categories]`,
  `posts_per_page = 3`,
  `orderby = 'date'`,
  `order = 'DESC'`.
- The list is empty when no other posts exist in the same
  category — the section is rendered with the empty message.

### Lifecycle

Stateless. Re-derived on each single-post request.

---

## E4 — Page Template entities

WordPress's standard template hierarchy. The theme ships files
for each named entity; WordPress loads the most-specific existing one
for a given request.

| Entity | File | Purpose |
|---|---|---|
| Front page | `front-page.php` | v1 home (4 sections); v2 home composes 12 sections. |
| Single post | `single.php` | Single-post view (full editorial per Q2). |
| Page | `page.php` | Static-page view; uses `template-parts/content-page.php`. |
| Category archive | `category.php` (or `archive.php`) | Category archive; uses Safe Mídia news grid. |
| Tag archive | `tag.php` (or `archive.php`) | Tag archive; uses Safe Mídia news grid. |
| Author archive | `author.php` (or `archive.php`) | Author archive; uses Safe Mídia news grid. |
| Date archive | `date.php` (or `archive.php`) | Date archive; uses Safe Mídia news grid. |
| Search | `search.php` | Search results page. |
| 404 | `404.php` | Not-found page. |

### Notes

- The existing theme ships `archive.php` as a fallback for all
  archive types; v2 refines that one file rather than creating
  per-type files.

---

## Cross-entity invariants

1. The Colunista Card (E2) and the Related Post (E3) are both
   derived (not persisted); each request re-derives them from
   `WP_Query`.
2. The Colunistas section reads E2 only if the "Colunistas" WordPress
   category exists (per FR-004a / Q3, the theme auto-creates it on
   activation); if the auto-create fails, the section falls back to
   the empty-state rule per FR-004.
3. The Single Post template (E4's `single.php`) reads E3 (related
   posts) from the current post's category.
4. No new database tables; no new custom post types; no new custom
   taxonomies (per the spec's Assumptions).

---

## Out-of-scope entities (deliberately omitted)

- **View counter** (for true "Mais Lidas" sorting) — uses
  most-recent-by-date in v2.
- **Newsletter Subscription backend** — static forms in v2.
- **Custom post types** — no `colunista`, no `analise`, etc.