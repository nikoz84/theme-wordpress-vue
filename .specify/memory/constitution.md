<!-- Sync Impact Report
- Version change: 1.0.0 → 1.1.0 (MINOR — material expansion of Principle V
  guidance; default CDN-first behavior is preserved for end users).
- Principle V redefined (renamed + content materially expanded):
  - V. Zero Build Step → V. CDN-First Distribution with Optional Build Path
- Other principles unchanged: I, II, III, IV.
- Sections unaffected: Technical Constraints, Development Workflow, Governance.
- No sections added or removed.
- Deferred TODOs: none.
- Rationale: Preserve the drop-in installation promise (zero extra tooling
  for site owners) while permitting contributors who want compiled JS/CSS
  to maintain an opt-in build pipeline (e.g., Vite) under `dist/`. The
  CDN-first assets (`assets/js/app.js`, `style.css`, the CDN Vue
  reference in `functions.php`) remain the source of truth and must
  continue to work on a plain install with no `node_modules/`, no `dist/`,
  and no build step.
-->

<!-- Sync Impact Report
- Version change: template (no version) → 1.0.0 (initial ratification).
- Principles newly defined (template placeholders removed):
  - [PRINCIPLE_1_NAME] → I. Server-First Rendering (PHP)
  - [PRINCIPLE_2_NAME] → II. Progressive Enhancement
  - [PRINCIPLE_3_NAME] → III. WordPress Template Hierarchy Discipline
  - [PRINCIPLE_4_NAME] → IV. REST API as the PHP↔Vue Bridge
  - [PRINCIPLE_5_NAME] → V. Zero Build Step
- Sections newly defined (template placeholders removed):
  - [SECTION_2_NAME] → Technical Constraints
  - [SECTION_3_NAME] → Development Workflow
- [GOVERNANCE_RULES] → concrete Governance section below.
- Template example/instruction comments (lines starting with `<!-- Example:`) removed; Sync Impact Report above replaces them.
- Removed sections: none.
- Deferred TODOs: none.
-->

# Vue Blocks Constitution

## Core Principles

### I. Server-First Rendering (PHP)
All primary HTML MUST be rendered server-side by WordPress PHP templates
that follow the official template hierarchy (`index.php`, `front-page.php`,
`single.php`, `page.php`, `archive.php`, `search.php`, `404.php`, plus
`header.php`, `footer.php`, `sidebar.php`, `template-parts/`, and `inc/`).
Vue.js MUST mount onto DOM that PHP has already produced — the
"in-DOM template" / progressive-enhancement technique used in
`assets/js/app.js` — and MUST NOT be the only source of any primary page's
markup.

**Rationale:** SEO crawlability, no-JS fallback, accessibility baseline, and
shared-hosting compatibility are non-negotiable for a WordPress theme.

### II. Progressive Enhancement
Every interactive feature MUST function without JavaScript. The search form
MUST submit natively to the WP search results template; pagination and
post links MUST continue to work via standard anchor navigation; the
mobile menu and dark-mode toggle MUST be optional or have non-JS fallbacks.
Vue layers behavior on top of working markup and MUST NOT be the only path
to core content.

**Rationale:** Resilience against CDN failures, ad-blockers, no-script user
agents, and slow networks; aligns with the WP accessibility tradition.

### III. WordPress Template Hierarchy Discipline
The theme MUST follow the official WordPress theme structure documented at
https://developer.wordpress.org/themes/core-concepts/:

- `style.css` carries the mandatory theme header and the stylesheet.
- `functions.php` handles theme setup, asset enqueue, menus, and widget
  areas.
- `index.php` is the mandatory fallback template.
- New page templates MUST be added only where the hierarchy requires; their
  filenames MUST follow WP's template lookup order.
- Helpers and template tags MUST live in `inc/` (e.g.,
  `inc/template-tags.php`); reusable markup MUST live in `template-parts/`.

**Rationale:** Predictable behavior across WP versions, plugin
compatibility, and Theme Check compliance.

### IV. REST API as the PHP↔Vue Bridge
PHP and Vue MUST communicate only through:

1. The WordPress REST API (`/wp-json/wp/v2/...`) for live data fetches
   (search suggestions, load-more posts), with the `wp_rest` nonce included
   as the `X-WP-Nonce` header on any request that requires it.
2. `wp_localize_script()` exposing a single `vbData` global for
   configuration, URLs, runtime flags, and i18n strings.

Any new REST field surfaced to Vue MUST be registered via
`register_rest_field()` (see `vb_register_rest_fields()` in
`functions.php`). Vue MUST NOT query the database directly, MUST NOT scrape
PHP-generated markup as a data source, and MUST NOT expose the nonce
outside the localized payload.

**Rationale:** Single source of truth (the WP REST schema), centralized
authorization (the nonce), and a clean separation of rendering from
interaction.

### V. CDN-First Distribution with Optional Build Path
The theme's **default distribution path** MUST remain zero-build:
Vue.js MUST load from a public CDN as a UMD/IIFE global (currently
`https://unpkg.com/vue@3.4.31/dist/vue.global.prod.js`), and the
application code in `assets/js/app.js` MUST run unmodified in current
evergreen browsers using `Vue.createApp().mount()` over server-rendered
DOM nodes. Deploying this theme from its repository MUST NOT require
`node`, `npm`, or any installer on the host.

A **separate, opt-in asset compilation path** (e.g., a Vite-driven build
that bundles `assets/js/app.js` into a single JS file and compiles
`style.css` into a processed stylesheet under a `dist/` directory) MAY
be provided as an alternative to the CDN-first path. When such a path
exists:

- The compiled outputs MUST live in `dist/` (or an equivalent directory
  that is gitignored or otherwise outside the theme's required files)
  and MUST NOT be required for the theme to function on a plain install.
- The CDN-first assets (`assets/js/app.js`, `style.css`, the CDN Vue
  reference in `functions.php`) MUST remain the source of truth and MUST
  continue to work when the build is not run.
- `functions.php` MUST continue to enqueue the CDN Vue global and the
  un-built `assets/js/app.js` by default; switching to the bundled
  artifacts MUST be an explicit configuration choice, not a side effect
  of the build tools being installed.
- The `package.json`, `vite.config.*`, and any build dependencies MUST
  be ignored by the theme's distribution story: a host that has only
  the theme files (no `node_modules/`, no `dist/`) MUST still get a
  working site.

**Rationale:** Preserve the drop-in installation promise for theme
users while allowing contributors who want bundled output (for
analysis, production hardening, or downstream pipelines) to opt in.
CDN-first also keeps the theme resilient against CDN failures for
hosts that do not opt in.

## Technical Constraints

- **PHP runtime:** 7.4 or newer (`Requires PHP: 7.4`).
- **WordPress:** 6.0 minimum; tested up to 6.7.
- **Vue.js:** 3.4.x, global production build via CDN (unpkg.com). MIT
  licensed as a separately-distributed dependency.
- **License:** GPLv2 or later (matching WordPress core).
- **Text Domain:** `vue-blocks` for all i18n strings; translation files
  live in `/languages`.
- **I18n in Vue:** User-facing strings passed to Vue MUST come from
  `vbData.i18n`, populated from PHP via `__()` / `_e()` so translators see
  them in the generated `.pot` catalog.
- **CSS tokens:** Visual tokens (colors, radii, shadows, spacing) MUST
  live as CSS custom properties at `:root` in `style.css` (e.g. `--vb-bg`,
  `--vb-primary`, `--vb-radius`). Hard-coded color or spacing values
  inside component rules are NOT allowed outside the `:root` token block.
- **Theme version:** `VB_VERSION` (in `functions.php`) and the `Version:`
  field in the `style.css` header MUST be bumped together; cache busting
  for enqueued assets depends on them.

## Development Workflow

- **File ownership:** Server-side concerns (templates, template-parts,
  REST fields, enqueue, widget areas) live in PHP files. Client-side
  interactivity lives in `assets/js/app.js`. Helper/template tags live in
  `inc/`.
- **Adding a REST field consumed by Vue:** Add it to
  `vb_register_rest_fields()` in `functions.php` (or to a module loaded
  from there). Do not invent ad-hoc endpoints outside the WP REST API.
- **Adding a Vue behavior:** Create or extend a small
  `Vue.createApp().mount()` block in `assets/js/app.js` over markup
  already emitted by PHP. If new data is needed, add it to `vbData` or
  expose a new REST field — do not embed data in markup attributes for
  Vue to parse.
- **Adding a page template:** Create the file under the theme root using
  the WordPress template-hierarchy filename; ensure `index.php` remains the
  fallback.
- **PHP file guard:** Every PHP file MUST start with
  `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- **Naming:** All theme functions MUST be prefixed `vb_`; all constants
  MUST be prefixed `VB_`; all CSS classes MUST be prefixed `vb-`.
- **Escaping:** Output MUST be escaped at the point of emission
  (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` as appropriate). Data
  sourced from `vbData` and consumed via `v-text` / `:attr` MUST be
  treated as already-escaped by PHP and MUST NOT be re-escaped in Vue.
- **Nonce usage:** Any state-changing or authenticated REST request
  initiated from Vue MUST include the `X-WP-Nonce` header sourced from
  `vbData.nonce`.
- **Quality bar:** Code MUST pass Theme Check–style hygiene. Silent
  fallbacks that hide Vue errors from the console are NOT allowed.

## Governance

This Constitution supersedes ad-hoc practices, READMEs, and conversational
conventions for this theme. Amendments MUST be documented (rationale +
diff) and the version incremented per the policy below before they bind
any new work.

- **Versioning policy:** SemVer `MAJOR.MINOR.PATCH`.
  - **MAJOR** — backward-incompatible governance change (principle
    removed or redefined, mandatory section added or removed).
  - **MINOR** — new principle or section added, or existing guidance
    materially expanded.
  - **PATCH** — clarifications, typo fixes, wording refinements, no
    semantic change.
- **Amendment procedure:** (1) Edit this file; (2) prepend a Sync Impact
  Report comment summarizing the change; (3) update the `Version`,
  `Ratified`, and `Last Amended` lines; (4) the amendment takes effect on
  save.
- **Compliance review:** Every change to template files, `functions.php`,
  `assets/js/app.js`, or `style.css` MUST be checked against the Core
  Principles and Technical Constraints above. A change that violates a
  principle requires either an amendment to this Constitution or a
  documented exception with rationale recorded in the same commit.
- **Runtime guidance:** Day-to-day conventions (naming, escaping, file
  layout) live in the code itself and in `readme.txt`; this Constitution
  is the source of truth only for governance and principles.

**Version**: 1.1.0 | **Ratified**: 2026-10-06 | **Last Amended**: 2026-10-06