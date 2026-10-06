# Feature Specification: Theme Branding & Customizer Integration

**Feature Branch**: `004-theme-customizer-and-branding`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "help me to upload add Site Title Safemidia but still has Vue Blocks .logo-text {
    font-family: 'Merriweather', serif;
    font-size: 21px;
    font-weight: 700;
    color: var(--navy);
    letter-spacing: -0.4px;
} and the text not has the same font on a @layouts/ help me to list all the functionalities on the @layouts-html files, the social midia must be has a layout customization in wordpress theme customization inside of wordpress admin page"

**Reference design**: `layouts-html/` (9 layout HTML mockups; the
"01 - Home" reference drives the visual language currently used in
the home page template parts).

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md` for governing
principles; in particular Principle I server-first rendering,
Principle II progressive enhancement, and the amended Principle V
CDN-first distribution with optional build path).

**Scope guardrail**: This feature polishes the Safe Mídia integration
(branding, fonts, Customizer) and documents the layouts inventory.
It MUST NOT remove the existing template parts, the design-system
`DESIGN.md`, the Docker harness, or the Vite opt-in path.

## Clarifications

### Session 2026-10-06

- Q: What exact string should the fresh-install WordPress site title
  default to? → A: Just "Safe Mídia". The `blogname` option is set
  to the brand name alone; the tagline (`blogdescription`) is left
  empty for the admin to fill in via the Customizer.
- Q: Where should the `layouts-html/` inventory document live so it
  ships with the design reference but stays out of the packaged
  theme? → A: Inside the theme at `theme/DESIGN_LAYOUTS.md` so it
  travels with the deliverable (refines FR-007).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Site title reflects the Safe Mídia brand on a fresh install (Priority: P1)

A contributor brings up the Docker testing harness from scratch and
the WordPress site's title (in the browser tab, the header bar, and
`wp-admin → Settings → General`) defaults to "Safe Mídia". The
theme's name (visible in `wp-admin → Appearance → Themes`) remains
"Vue Blocks" — the theme identity and the site identity are
separate.

**Why this priority**: The home page template parts already render
the Safe Mídia navbar, hero, news grid, and footer, but the WordPress
site title at the top of the browser and the header bar still reads
"Vue Blocks (local)", which breaks the visual identity.

**Independent Test**: After `docker compose down -v && docker compose up
-d`, the freshly installed site's `blogname` option is "Safe Mídia",
and the `<title>` element on the home page matches.

**Acceptance Scenarios**:

1. **Given** a fresh install (volumes wiped), **When** the bootstrap
   completes, **Then** the WordPress `blogname` option is "Safe Mídia".
2. **Given** the same install, **When** an admin views `wp-admin →
   Appearance → Themes`, **Then** the theme's display name remains
   "Vue Blocks" (the theme's `style.css` WP header is unchanged).

---

### User Story 2 - The Merriweather and Inter fonts load on every page (Priority: P1)

A visitor loads any page of the Safe Mídia site and the `.logo-text`
and other Merriweather-styled headings render in the actual
Merriweather typeface, not a generic serif fallback. Body text
renders in Inter (or its system fallback). The font load does not
block first paint and uses `display=swap` to avoid FOIT.

**Why this priority**: Without the fonts, the Safe Mídia design
degrades to a generic serif, breaking visual fidelity to the
reference layout. The CSS in `theme/style.css` references the
font families but no `@font-face` or `<link rel="stylesheet">` is
emitted by `functions.php`.

**Independent Test**: Load the home page in a browser; in the
network panel confirm `fonts.googleapis.com` requests for Merriweather
and Inter; in the computed styles confirm `.logo-text` resolves to a
`Merriweather` family.

**Acceptance Scenarios**:

1. **Given** the theme is active, **When** any page loads, **Then** a
   `<link rel="preconnect">` to `fonts.googleapis.com` and
   `fonts.gstatic.com` is present in `<head>`.
2. **Given** the theme is active, **When** any page loads, **Then** a
   single `<link rel="stylesheet">` to Google Fonts with the
   `?family=Merriweather:wght@700;900&family=Inter:wght@300;400;500;600&display=swap`
   query string is present.
3. **Given** the font CSS loaded successfully, **When** the browser
   renders `.logo-text`, **Then** the computed `font-family` starts
   with `'Merriweather'`.

---

### User Story 3 - Social media URLs are configurable from the WordPress Customizer (Priority: P1) 🎯 MVP

A site admin opens `wp-admin → Appearance → Customize`, sees a new
"Social media" panel under the Safe Mídia theme, and enters URLs for
Facebook, Instagram, X, and LinkedIn. After publishing, the navbar
and footer social icons link to those URLs (icons whose URL is empty
are hidden).

**Why this priority**: The navbar and footer render hard-coded `href="#"`
placeholders for social icons. A real site needs to point them at
real profiles without editing theme files.

**Independent Test**: Open the Customizer, fill in a Facebook URL,
publish, and confirm the navbar's Facebook icon now links to that
URL; clear the URL, republish, and confirm the icon hides.

**Acceptance Scenarios**:

1. **Given** the theme is active, **When** an admin opens
   `wp-admin → Appearance → Customize`, **Then** a new panel titled
   "Social media" appears under the theme's sections, with four URL
   fields (Facebook, Instagram, X, LinkedIn) and a description.
2. **Given** the admin has filled in URLs and clicked Publish, **When**
   the front-end page loads, **Then** the navbar's four social icons
   link to the configured URLs and the footer's four footer-soc icons
   link to the same URLs.
3. **Given** the admin leaves an URL empty and clicks Publish, **When**
   the front-end page loads, **Then** the corresponding social icon
   is not rendered (hidden in both navbar and footer).

---

### User Story 4 - A layouts inventory documents what each `layouts-html/` file contains (Priority: P2)

A contributor opens a single Markdown file and learns: which
`layouts-html/` entries are designed, which are referenced by the
theme's template parts, and which are deferred for v1. For each
layout, the inventory lists its sections and the corresponding Safe
Mídia visual language.

**Why this priority**: The `layouts-html/` directory has 9 entries,
but only the home page (entry 01) drives the current theme. A
contributor needs a single document to know what's there without
opening each file.

**Independent Test**: Open the layouts inventory document; confirm
it lists every entry under `layouts-html/` with: title, which theme
template parts (if any) it informs, sections present, and v1
status (in scope / deferred / reference only).

**Acceptance Scenarios**:

1. **Given** the inventory document exists at the documented path,
   **When** a contributor opens it, **Then** every entry under
   `layouts-html/` is listed with its title and v1 disposition.
2. **Given** the inventory document, **When** the contributor looks
   for the home page, **Then** the home page entry explicitly lists
   the four core Safe Mídia sections (navbar, hero, news grid,
   footer) currently implemented in the theme.

---

### Edge Cases

- **Empty Customizer URL**: An empty URL MUST hide the icon (per
  US3 acceptance 3), not render an `<a>` with an empty href.
- **Customizer field cleared by user**: removing the URL republishes
  empty; icon disappears — same as never-set.
- **Site title override**: A site owner MAY change the site title from
  the Customizer's "Site Identity" section. The bootstrap default is
  only the initial value.
- **Font loading blocked**: If `fonts.googleapis.com` is unreachable,
  the site's CSS uses the documented fallback (`Georgia, serif` for
  Merriweather; `system-ui, sans-serif` for Inter). The page still
  renders correctly.
- **WordPress version drift**: the Customizer API used MUST be
  compatible with WordPress 6.0+ (the theme's documented minimum).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The Docker bootstrap MUST set the WordPress site title
  (`blogname` option) to "Safe Mídia" so a fresh install lands with
  the brand identity. The tagline (`blogdescription`) is left empty
  by default; an admin MAY fill it in via the Customizer's Site
  Identity section. The brand string MUST be overridable via the
  `WP_SITE_TITLE` env var and documented in `.env.example`.
- **FR-002**: The theme name in `style.css`'s WP header MUST remain
  "Vue Blocks" — unchanged by this feature.
- **FR-003**: `theme/functions.php` MUST enqueue Google Fonts with the
  Merriweather (weights 700, 900) and Inter (weights 300, 400, 500,
  600) families, with `display=swap` and `<link rel="preconnect">` to
  both `fonts.googleapis.com` and `fonts.gstatic.com`.
- **FR-004**: A WordPress Customizer section titled "Social media"
  MUST expose four URL fields (Facebook, Instagram, X, LinkedIn),
  persisted via the `theme_mod` API under the existing theme name.
- **FR-005**: `theme/template-parts/header/navbar.php` MUST read the
  four social URL settings; if a URL is empty, the corresponding
  `<a class="soc">` element MUST NOT be rendered.
- **FR-006**: `theme/template-parts/footer/site-footer.php` MUST do the
  same as FR-005 for its `<a class="footer-soc">` elements.
- **FR-007**: A `theme/DESIGN_LAYOUTS.md` file MUST document every
  entry under `layouts-html/` with: title, v1 status (in scope /
  deferred / reference), and the Safe Mídia sections present in each
  layout. The file ships with the packaged theme (it's contributor
  documentation for downstream maintainers).

### Key Entities *(include if feature involves data)*

- **Site Title**: a single string option, persisted by WordPress's
  `blogname` option. Initial value set by the bootstrap; editable
  via Customizer's "Site Identity" section.
- **Social URL**: a URL string per platform (Facebook, Instagram, X,
  LinkedIn). Four values total. Persisted via `theme_mod` keyed by
  the theme name.
- **Layout**: one of the 9 entries under `layouts-html/`. Each has a
  directory name and an HTML file documenting its sections.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A fresh install (volume wipe + `docker compose up -d`)
  leaves `get_option('blogname')` equal to "Safe Mídia", verified by
  `wp option get blogname --allow-root`.
- **SC-002**: With the theme active, every front-end page renders
  exactly one `<link rel="preconnect">` to `fonts.googleapis.com`,
  exactly one `<link rel="preconnect">` to `fonts.gstatic.com`, and
  exactly one `<link rel="stylesheet">` to
  `fonts.googleapis.com/css2?...&family=Merriweather...&family=Inter...&display=swap`.
- **SC-003**: With a Facebook URL set in the Customizer, the rendered
  HTML contains exactly one `<a class="soc" title="Facebook">` and
  exactly one `<a class="footer-soc" aria-label="Facebook">`, both
  with the configured `href`. With the URL cleared, both are absent
  from the rendered HTML.
- **SC-004**: The layouts inventory document enumerates all 9
  `layouts-html/` entries and the home page entry explicitly lists
  the four core Safe Mídia sections.
- **SC-005**: The package produced by `theme/package.sh` continues
  to be ≤ 5 MB and free of test-harness paths after this feature
  lands.

## Assumptions

- The site title default is exactly "Safe Mídia", documented in
  `.env.example` as `WP_SITE_TITLE="Safe Mídia"` (or the contributor's
  override). The bootstrap passes it to `wp core install --title`.
  The tagline (`blogdescription`) is NOT set by the bootstrap.
- The four social URL fields share a single Customizer panel; no
  per-platform panel is needed for v1.
- The font-loading approach uses `<link rel="preconnect">` plus
  `<link rel="stylesheet">` to `fonts.googleapis.com`. No
  self-hosted fonts in v1.
- The layouts inventory document lives at `theme/DESIGN_LAYOUTS.md`
  so it travels with the packaged theme (per Clarification Session
  2026-10-06).
- No additional Customizer settings beyond the four social URLs in
  v1 (e.g., the subscribe CTA URL, footer column copy, etc., are
  deferred to a future feature).
- The theme name "Vue Blocks" is preserved per FR-002.

## Out-of-Scope (deliberately omitted from v1)

- The seven non-core Safe Mídia sections on the home page
  (newsletter compact, Para o Segurado, Boletim Regulatório,
  Colunistas, Análise de Mercado, category tabs, Mais Lidas,
  newsletter grande) — already documented as deferred in
  `specs/003-design-system-foundation/spec.md`.
- Per-platform Customizer panels, subscribe-CTA URL settings,
  footer-column content editors — deferred.
- Page templates beyond the home page (single post, archive, search,
  404) — deferred.
- Self-hosted fonts or a font-loading plugin — out of scope; CDN
  fonts are sufficient for the Safe Mídia visual language.