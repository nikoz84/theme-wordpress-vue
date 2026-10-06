# Feature Specification: Home-Page Sections v2 & Page Templates

**Feature Branch**: `005-home-v2-and-page-templates`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "home page sections, page templates first of all push"

**Reference design**: `layouts-html/` (the Safe Mídia static HTML
references, documented in `theme/DESIGN_LAYOUTS.md`).

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md`; in particular
Principle I server-first rendering, Principle III WP template
hierarchy, and the amended Principle V CDN-first distribution).

**Scope guardrail**: This feature brings the remaining Safe Mídia home
sections into the shipped theme and refines the page templates
beyond `front-page.php`. It MUST NOT remove the four v1 sections
(navbar, hero, news grid, footer), the design-system `DESIGN.md`,
the Docker harness, the Vite opt-in path, the Customizer
integration, or the `DESIGN_LAYOUTS.md` inventory.

**Push context**: The latest commit (`43ff4e4 feat(004): theme
branding and Customizer social integration`) has been pushed to
`origin/main` before this spec was authored.

## Clarifications

### Session 2026-10-06

- Q: Should v2 land all 8 deferred Safe Mídia home sections in a
  single deliverable, or split some out to a v3 follow-up? → A: v2
  ships all 8 sections (Newsletter Compact, Para o Segurado,
  Newsletter Grande, Análise de Mercado, Category tabs, Mais Lidas,
  Boletim Regulatório, Colunistas) in one feature; no v3 split.
- Q: What level of detail should the single-post template render?
  → A: Full editorial layout — hero featured image + title + byline
  + category chip + body + related-posts rail (matches the Safe Mídia
  reference's article layout).
- Q: Should the theme auto-create the "Colunistas" WordPress
  category on activation, or wait for an admin to create it
  manually? → A: Theme auto-creates the category on the
  `after_setup_theme` hook (idempotent — no-op if the category
  already exists); refines FR-004 with a new FR-004a.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Full Safe Mídia home page renders all sections (Priority: P1) 🎯 MVP

A visitor lands on the home page and sees the full Safe Mídia visual
parity with the reference layout — the four v1 sections (navbar,
hero, news grid, footer) PLUS the eight deferred sections
(newsletter compact, "Para o Segurado" rail, newsletter grande,
"Análise de Mercado", category tabs, "Mais Lidas da Semana",
"Boletim Regulatório", "Colunistas") in the documented order.

**Why this priority**: The theme already ships with four sections;
the remaining eight bring the home page to full visual fidelity
with the Safe Mídia reference. Per Clarification Q1, all eight ship
in v2 (no v3 split).

**Independent Test**: With seeded content (≥6 published posts and
one configured primary menu), load the home page and confirm
each of the eight deferred sections renders with the Safe Mídia
visual structure; the section order matches the reference.

**Acceptance Scenarios**:

1. **Given** the home page is loaded, **When** the visitor scrolls
   past the news grid, **Then** a "Para o Segurado" section with
   four featured cards is visible.
2. **Given** the home page is loaded, **When** the visitor scrolls
   past the "Para o Segurado" section, **Then** a "Newsletter"
   large section with a dark background and a perks list is
   visible.
3. **Given** the home page is loaded, **When** the visitor scrolls
   past the newsletter grande, **Then** an "Análise de Mercado"
   section with one featured analysis and a list of recent
   analyses is visible.
4. **Given** the home page is loaded, **When** the visitor scrolls
   past the Análise section, **Then** "Boletim Regulatório" and
   "Colunistas" sections are visible, each with the Safe Mídia
   card / list styling.

---

### User Story 2 - Single post page renders with the full Safe Mídia editorial layout (Priority: P1)

A reader clicks into any published article and sees the article in
the full Safe Mídia editorial layout: featured-image hero at the top,
title in Merriweather, byline, body content in Inter, category chip,
and a "Leia também" (related posts) footer rail. Per Clarification
Q2, the layout is full editorial — no trimmed-down variant for v2.

**Why this priority**: Most readers visit single articles, not the
home page; the single-post template is the highest-impact page
template after the home page.

**Independent Test**: Load any published post's permalink and
confirm the title is in Merriweather, the body in Inter, the
featured image hero renders at the top, and the related-posts rail
lists other posts from the same category.

**Acceptance Scenarios**:

1. **Given** a published post with a featured image, **When** its
   permalink is loaded, **Then** the page renders with the Safe Mídia
   featured-image hero at the top, the title in Merriweather, the
   byline (author + date), and the body in Inter.
2. **Given** a post in category "Mercado", **When** the permalink
   is loaded, **Then** a "Leia também" section at the bottom shows
   the 3 most recent posts in the same category.
3. **Given** the Safe Mídia navbar and footer, **When** the
   permalink is loaded, **Then** they wrap the article content
   identically to the home page (the template hierarchy uses
   `header.php` and `footer.php` consistently).

---

### User Story 3 - Archive, search, and 404 page templates render with the Safe Mídia visual language (Priority: P2)

A visitor lands on `/category/mercado/`, `/?s=query`, or a missing
URL; each returns a styled page using the Safe Mídia visual language
(news grid for archives; "no results" / search results card layout;
404 with a friendly message).

**Why this priority**: These templates already exist (the original
Vue Blocks theme ships `archive.php`, `search.php`, `404.php`) but
they were styled for the Vue Blocks visual language. Refining them
to the Safe Mídia language completes the brand consistency.

**Independent Test**: Visit each template's URL with seeded content
and confirm the rendering matches the Safe Mídia expectations.

**Acceptance Scenarios**:

1. **Given** a category with at least one published post, **When**
   the category archive is loaded, **Then** posts render in the
   Safe Mídia news-card grid.
2. **Given** a search query that matches no posts, **When** the
   search results page is loaded, **Then** a "no results" message
   is shown in the Safe Mídia typography.
3. **Given** a request to a non-existent URL, **When** the 404 page
   is loaded, **Then** a friendly message is shown in the Safe Mídia
   typography and the navbar/footer are present.

---

### User Story 4 - Colunistas section as a content category (Priority: P2)

The "Colunistas" section on the home page lists columnists — each
with avatar, name, role, and a recent article link. Contributors
add columnists by creating posts in the "Colunistas" category;
the section automatically populates from the most recent post per
author in that category.

**Why this priority**: Columnists are a distinctive editorial feature
of the Safe Mídia reference; without this section the page misses a
key brand element. The implementation uses standard WordPress
author taxonomy (no custom post type needed).

**Independent Test**: Create at least 3 published posts in the
"Colunistas" category, each by a different author; load the home
page and confirm the Colunistas section shows 3 cards (one per
author) with the most recent article title and the author's
display name.

**Acceptance Scenarios**:

1. **Given** at least 3 published posts in the "Colunistas"
   category by 3 different authors, **When** the home page is
   loaded, **Then** the Colunistas section shows 3 cards, one per
   author.
2. **Given** the same setup, **When** the Colunistas section is
   rendered, **Then** each card shows: avatar (or generated
   placeholder), display name, role (or short bio), and the most
   recent post title linking to that post.

---

### Edge Cases

- **No "Colunistas" content**: If the "Colunistas" category has no
  posts, the section is hidden (not rendered as "no columnists yet").
- **Empty archive / search**: A category with 0 posts, or a search
  with 0 results, renders the Safe Mídia "empty" treatment.
- **Mobile width** (≤768px): All new sections reflow per the Safe
  Mídia mobile breakpoints (most degrade to single column).
- **Post without a featured image**: A placeholder (the existing
  `vb_placeholder_image()` helper) is used; the Safe Mídia CSS
  handles the missing-image case via `object-fit: cover`.
- **Long titles**: Safe Mídia CSS uses `overflow: hidden` and
  `text-overflow: ellipsis` on cards; long titles are truncated, not
  overflowing the layout.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The eight deferred Safe Mídia sections MUST render on
  the home page in the order documented in the Safe Mídia reference
  HTML (`layouts-html/01 - Home/01 - safemidia-home-fixed.html`).
  Per Clarification Q1, all eight ship in v2.
- **FR-002**: `theme/single.php` MUST render a published post with the
  full editorial layout (per Clarification Q2): the Safe Mídia
  featured-image hero, title in Merriweather, body in Inter, byline,
  category chip, and a related-posts footer rail (3 most recent
  posts in the same category).
- **FR-003**: `theme/archive.php`, `theme/search.php`, and
  `theme/404.php` MUST render with the Safe Mídia visual language
  (news grid for archive; result card layout for search; friendly
  message for 404).
- **FR-004**: The Colunistas section MUST source from posts in the
  "Colunistas" category (one card per distinct author), and MUST be
  hidden if the category has 0 posts.
- **FR-004a**: The theme MUST auto-create the "Colunistas" category
  (slug `colunistas`, name "Colunistas") on the `after_setup_theme`
  hook if it does not already exist (per Clarification Q3). The
  registration MUST be idempotent — repeated activations and re-runs
  are no-ops.
- **FR-005**: All new sections MUST use CSS custom properties at
  `:root` (no hard-coded visual values in component rules); the
  existing Safe Mídia token catalog in `theme/style.css` is the
  source.
- **FR-006**: The eight new home-page sections MUST be implemented
  as template parts under `theme/template-parts/home/` (e.g.,
  `newsletter-compact.php`, `segurado.php`, `newsletter-grande.php`,
  `analise.php`, `category-tabs.php`, `mais-lidas.php`,
  `boletim.php`, `colunistas.php`).
- **FR-007**: The packaged theme (`theme/package.sh` output) MUST
  continue to be ≤ 5 MB after the new sections and templates land.

### Key Entities *(include if feature involves data)*

- **Newsletter Subscription**: a new optional entity (future v3) —
  for v2 the newsletter sections render a static form (no
  subscription persistence).
- **Colunista Card**: a derived entity — one per distinct author in
  the "Colunistas" category, sourced from `wp_get_recent_posts` and
  the user's `display_name` / `user_description` fields.
- **Related Post**: a derived entity — sourced from the post's
  category via `WP_Query`, returning up to 3 most recent posts
  excluding the current post.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: With seeded content, the home page renders all 12
  Safe Mídia sections (4 v1 + 8 deferred, per Clarification Q1) in
  the documented order; the rendered HTML for each section contains
  the expected CSS class names (e.g., `.newsletter-compact`,
  `.segurado-grid`, `.nl-block`, `.analise-layout`,
  `.boletim-layout`, `.col-grid`, `.tabs-grid`, `.mais-layout`).
- **SC-002**: A published post's permalink loads with the full Safe
  Mídia editorial layout (per Clarification Q2): Merriweather title,
  Inter body, featured-image hero at the top, byline, category chip,
  and a related-posts footer rail listing up to 3 posts from the
  same category.
- **SC-003**: A category archive, a search results page (with 0
  results), and a 404 page each render with the Safe Mídia
  typography and the navbar / footer from `header.php` / `footer.php`.
- **SC-004**: With at least 3 published posts in the "Colunistas"
  category by 3 different authors, the home page renders 3
  Colunistas cards; with 0 posts, the section is hidden.
- **SC-005**: `bash theme/package.sh` continues to produce a zip
  ≤ 5 MB and free of test-harness paths after this feature lands.

## Assumptions

- All eight new home sections use only the standard WordPress
  `WP_Query` / `wp_get_recent_posts` / `get_the_category()` APIs —
  no custom post types, no custom taxonomies.
- The "Colunistas" category is identified by slug `colunistas`. The
  theme auto-creates it on activation if it doesn't exist (per
  Clarification Q3); if the auto-creation ever fails (permissions,
  etc.) the section degrades gracefully to "hidden" via the FR-004
  empty-state rule.
- Single post / archive / search / 404 / page templates use
  WordPress's standard template-hierarchy file names and continue to
  load `header.php` and `footer.php` for the wrapper.
- No new build step is introduced (per Constitution Principle V).
- The "Mais Lidas da Semana" section uses WordPress's
  post-view-count meta or the simpler "most-recent posts" approach
  (the simpler approach is the default; v1 ships without comment
  view-counting).
- No new Customizer settings are added in v2; the existing social
  Customizer (from feature 004) is sufficient.
- The current `theme/front-page.php` continues to be the v1 home
  template; the new sections compose onto it.

## Out-of-Scope (deliberately omitted from v2)

- A newsletter subscription backend (saving emails); the form is
  static for v2.
- A custom post type for Colunistas (the "Colunistas" WordPress
  category is sufficient for v2).
- View-count tracking for "Mais Lidas da Semana" (uses
  most-recent-by-date; real analytics deferred).
- Author-byline / role / bio editing via the Customizer (the
  WordPress user profile is the source).
- Search-result highlighting (a side-by-side match preview deferred).
- Translation / i18n of the new section titles (uses the existing
  `vue-blocks` text domain; English text first).