# Feature Specification: Design System & Theme Package Foundation

**Feature Branch**: `003-design-system-foundation`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "@layouts-html/01 - Home/01 - safemidia-home-fixed.html help me to create a layout for a home page the ideia it's generate the layout and the componenets can you create a DESIGN.md file for structure the site and the components to fallow a design system. The other ideia it's make a better organization code of the theme to separate of the principal wordpress project to upload the finally result of the zip theme for upload."

**Reference design**: `layouts-html/01 - Home/01 - safemidia-home-fixed.html`
(Safe Mídia — Portal de Notícias do Setor de Seguros; an existing
static HTML / CSS reference at the repo root that defines the visual
language for v1.)

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md` for governing
principles; in particular Principle V's CDN-first default, Principle
III's WP template hierarchy discipline, and Principle IV's REST API as
the PHP↔Vue bridge).

**Scope guardrail**: This feature introduces a **design system + a
home-page layout** AND **reorganizes the repo so the theme can be
packaged independently of the Docker testing harness**. It MUST NOT
remove or weaken the Constitution's CDN-first guarantee or the
existing template-hierarchy discipline.

## Clarifications

### Session 2026-10-06

- Q: Which directory name should hold the packaged WordPress theme
  files after the restructuring? → A: `theme/` (commits FR-004 and
  resolves the "proposed as `theme/`" wording in Assumptions).
- Q: Where should `DESIGN.md` live so it stays in sync with the
  theme without leaking into the deliverable zip? → A: A canonical
  copy at the repo root for designers, plus a duplicate inside
  `theme/` so the design language ships with the deliverable (adds
  FR-001a).
- Q: Which sections of the Safe Mídia reference HTML should ship in
  the v1 home page? → A: Core 4 only — navbar, hero (with sidebar
  posts), news grid, footer. Other sections (newsletter compact,
  Para o Segurado, Boletim Regulatório, Colunistas, Análise de
  Mercado, Tabs, Mais Lidas, newsletter grande) are deferred to v2
  (refines FR-007 and SC-004).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - DESIGN.md is the single source of truth for the theme's visual language (Priority: P1)

A new designer / contributor opens the repo and finds a `DESIGN.md`
that documents the theme's design tokens (colors, typography, spacing,
radii, shadows), its component inventory, and its responsive
breakpoints — enough to author new screens without reading the CSS.

**Why this priority**: Without a single written reference, every
contributor re-derives the design system from the CSS, which is slow
and inconsistent. This is the foundation for the rest of the work.

**Independent Test**: Open `DESIGN.md` (or its renderable equivalent)
and confirm it lists at least: every color token from the Safe Mídia
reference, the two font families and their weight / usage rules, every
spacing / shadow / radius token, every component (with a short usage
note), and the responsive breakpoints.

**Acceptance Scenarios**:

1. **Given** the repository, **When** a contributor opens the design
   document, **Then** they can name the primary, secondary, accent,
   and surface colors without reading `style.css`.
2. **Given** the design document, **When** the contributor adds a new
   section to the home page, **Then** they know which existing
   component template (`template-parts/...`) to start from and which
   CSS custom properties to consume.

---

### User Story 2 - The theme packages into a zip and installs cleanly into any WordPress site (Priority: P1) 🎯 MVP

A contributor can `cd theme && zip -r ../vue-blocks.zip .` (or
equivalent) and the resulting archive installs cleanly into a fresh
WordPress site — **without** also packaging the Docker harness,
`DESIGN.md`, `bin/bootstrap.sh`, or any test-only artifacts.

**Why this priority**: This is the actual deliverable. Without it,
the theme can't be shipped to anyone.

**Independent Test**: Run the documented packaging command; copy the
resulting zip into a fresh WordPress site's `wp-content/themes/`
directory; activate the theme in `wp-admin → Appearance → Themes`;
confirm the theme loads and renders the home page without errors.

**Acceptance Scenarios**:

1. **Given** the repo after the restructuring, **When** the contributor
   runs the documented packaging command, **Then** the resulting
   archive contains only the theme's runtime files (`style.css`,
   `functions.php`, templates, `template-parts/`, `inc/`, `assets/`).
2. **Given** the packaged archive, **When** it is installed in a fresh
   WordPress site, **Then** the theme appears in the Themes list,
   activates without errors, and renders the home page.

---

### User Story 3 - The home page renders the Safe Mídia visual structure as a dynamic WordPress template (Priority: P2)

The WordPress home page rendered by the theme matches the structural
and stylistic parity of the Safe Mídia reference HTML — same sections
in the same order, same visual hierarchy, same components, content
sourced from WordPress posts / categories / menus.

**Why this priority**: This is the substantive "generate the layout"
deliverable. Without it the design system is documentation only.

**Independent Test**: With the theme active on a site that has at
least 6 published posts and a primary menu configured, load the
homepage in a browser and confirm visually that the navbar, hero,
news grid, "Para o Segurado" rail, newsletter compact, footer match
the Safe Mídia reference's structure and styling.

**Acceptance Scenarios**:

1. **Given** a site with the theme activated and seeded content, **When**
   the homepage is loaded, **Then** the rendered sections match the Safe
   Mídia reference's section order (navbar → hero with sidebar → news
   grid → footer). The seven other sections shown in the reference
   (newsletter compact, "Para o Segurado", "Boletim Regulatório",
   "Colunistas", "Análise de Mercado", category tabs, "Mais Lidas
   da Semana", newsletter grande) are NOT in v1 and MUST NOT be
   required for an acceptable home page.
2. **Given** a viewport narrower than the documented tablet
   breakpoint, **When** the homepage is loaded, **Then** the layout
   reflows per the design document (no horizontal scroll, no broken
   grid).

---

### Edge Cases

- **Empty content**: A fresh install with zero posts MUST render a
  graceful empty state (no broken grid).
- **Missing featured image**: Posts without a thumbnail MUST render
  with a tasteful placeholder, not an empty `<img>`.
- **Theme inside `theme/` works under the existing Docker testing
  setup**: the Docker harness MUST continue to mount the theme
  correctly after the restructuring — no path renames break the
  bind mount.
- **DESIGN.md drift**: SPEC edits visible in `style.css` are
  accompanied by a corresponding update to `DESIGN.md`.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The repo MUST contain a `DESIGN.md` (or equivalent
  Markdown file) that documents the design system used by the theme:
  colors, typography, spacing, radii, shadows, components, responsive
  breakpoints.
- **FR-001a**: A duplicate of `DESIGN.md` MUST live inside `theme/`
  so the packaged theme ships with the design-system reference.
  The two copies MUST stay in sync; the canonical source is the
  repo-root copy and the in-theme copy is generated from it (or
  symlinked, if the host OS allows).
- **FR-002**: Every color, spacing, radius, and shadow value used in
  the theme MUST be a CSS custom property declared at `:root` in
  `theme/style.css` (per Constitution Principle III's CSS-token
  rule). Hard-coded values inside component rules are NOT allowed.
- **FR-003**: Every component surfaced in the theme MUST be
  documented in `DESIGN.md` with: name, purpose, when to use, when
  not to use, key CSS custom properties it consumes, and the
  template-part file (if any) that implements it.
- **FR-004**: The theme's runtime files MUST live under a single
  subdirectory of the repo (proposed name: `theme/`). The packaging
  command MUST operate on that subdirectory only.
- **FR-005**: The Docker testing harness (compose file, bootstrap
  script, seed content, `.env.example`) MUST live at the repo root
  and MUST continue to mount the theme directory into
  `wp-content/themes/vue-blocks` after the restructuring.
- **FR-006**: A documented packaging command MUST produce a `.zip`
  archive that contains only the theme's runtime files and is ready
  to upload to a WordPress site's `wp-content/themes/` directory.
- **FR-007**: The home page MUST render the four core Safe Mídia
  sections — navbar, hero (with sidebar posts), news grid, footer —
  in that order, sourcing content from WordPress (posts,
  categories, menu). The seven other sections shown in the reference
  (newsletter compact, "Para o Segurado", "Boletim Regulatório",
  "Colunistas", "Análise de Mercado", category tabs, "Mais Lidas
  da Semana", newsletter grande) are NOT required for v1.
- **FR-008**: The theme's published behavior MUST continue to satisfy
  the Constitution: server-first rendering, progressive enhancement,
  REST API as the PHP↔Vue bridge, CDN-first distribution, etc.
- **FR-009**: `DESIGN.md` MUST reference the Safe Mídia design tokens
  (colors, type) by name so contributors can map the visual language
  back to the source file.
- **FR-010**: The packaged theme MUST continue to ship with no
  build step required (Constitution Principle V).

### Key Entities *(include if feature involves data)*

- **Design Token**: a name → CSS custom property binding (e.g.,
  `--color-primary` → `#1E50D4`). All visual values flow from the
  token catalog.
- **Component**: a reusable UI block documented in `DESIGN.md` and
  implemented as a `template-parts/` file (PHP) and / or a small
  JS widget in `assets/js/app.js`.
- **Home Page Layout**: the section-by-section arrangement of the
  home page, mirroring the Safe Mídia reference.
- **Theme Package**: the zip archive deliverable, produced from the
  `theme/` subdirectory.
- **Test Harness**: the Docker compose stack that mounts the theme
  for local testing — NOT part of the theme package.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: `DESIGN.md` is published at the repo root with at
  least 20 documented design tokens, 8+ documented components, and
  2+ documented breakpoints.
- **SC-002**: A contributor can run the documented packaging command
  and produce a `<= 5 MB` `.zip` archive in under 30 seconds.
- **SC-003**: The zip installs into a fresh WordPress 6.0+ / PHP 7.4+
  site and the theme activates without PHP errors, warnings, or
  missing-file notices in the browser console.
- **SC-004**: With at least 6 seeded published posts and a configured
  primary menu, the rendered home page matches the Safe Mídia
  reference's **four core** sections (navbar, hero with sidebar,
  news grid, footer) in order and visual hierarchy (verified by a
  contributor visually comparing the live page to the reference).
- **SC-005**: The existing Docker testing harness (`docker compose up
  -d`) continues to bring up the theme unchanged after the
  restructuring — no path edits required in the harness, no breakage
  in the bind mount.
- **SC-006**: A grep of the packaged archive shows no `node_modules/`,
  `dist/`, `bin/`, `seed/`, `docker-compose.yml`, or other
  test-infrastructure paths present inside it.

## Assumptions

- The Safe Mídia reference at `layouts-html/01 - Home/01 -
  safemidia-home-fixed.html` is the authoritative source for the
  v1 design tokens and component inventory.
- The Vue Blocks theme's current `style.css` will be re-purposed
  (renamed / re-tokenized) to the Safe Mídia design system in this
  feature — this is a design-system migration, not an additive
  layer.
- The subdirectory for the packaged theme is `theme/` (confirmed
  in Clarification Session 2026-10-06).
- Only the home page (`front-page.php`) is in scope for v1.
  Single-post, archive, search, and other page templates are out of
  scope and will be addressed in future features.
- The Constitution is not amended by this feature. All five
  principles continue to apply unchanged.
- No build step is added — the theme ships as plain PHP / plain JS
  + plain CSS, exactly as the Constitution requires.
- `DESIGN.md` is the human-readable reference; the machine-readable
  reference is `theme/style.css`'s `:root` block. They MUST stay in
  sync (a comment in `style.css` notes the link to `DESIGN.md`).
- The repo-root `DESIGN.md` is the canonical source; the in-theme
  copy is generated from it and ships with the deliverable.

## Out-of-Scope (deliberately omitted from v1)

- Single-post, archive, search, 404, and other page templates
  (will be addressed in a future feature once the home-page design
  system is ratified).
- The other eleven layouts in `layouts-html/` (contact, who-we-are,
  privacy, glossary, terms-of-use, etc.). The home page is in scope;
  the others remain design references.
- The seven non-core Safe Mídia sections: newsletter compact,
  "Para o Segurado", "Boletim Regulatório", "Colunistas", "Análise
  de Mercado", category tabs, "Mais Lidas da Semana", newsletter
  grande. Deferred to a v2 follow-up once the four core sections
  are ratified.
- Section-level translation of the Safe Mídia HTML for non-PT-BR
  locales (the theme's text domain `vue-blocks` remains
  English-first for v1).
- Replacing the Docker testing harness with a different testing
  approach (the harness stays as-is).