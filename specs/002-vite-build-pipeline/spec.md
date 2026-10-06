# Feature Specification: Vite Build Pipeline for Vue Blocks

**Feature Branch**: `002-vite-build-pipeline`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "— propose short name vite-build-pipeline"
(Continuation of the broader user request from earlier in the session:
"add suport for vite create a compiled js and style.css". This
invocation of `/speckit.specify` confirms the short name and requests
the formal spec for the opt-in Vite build path.)

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3
layered on top (see `.specify/memory/constitution.md` for governing
principles; Constitution Principle V as amended to v1.1.0 is the
controlling principle for this feature).

**Scope guardrail**: This feature introduces an **opt-in** build path
**alongside** the existing CDN-first default. It MUST NOT remove or
weaken the CDN-first guarantee for end users (site owners).

## Clarifications

### Session 2026-10-06

- Q: When the opt-in switch is on, how should the bundled artifacts be
  referenced so caches stay correct across rebuilds? → A: Content-hash
  filenames plus a documented manifest file that `functions.php` reads
  at enqueue time (adds FR-011).
- Q: Should the bundled artifacts ship with source maps in v1? → A: Yes
  — emit `.map` files alongside each JS/CSS artifact in the build
  output directory; the manifest MUST NOT include source-map entries
  as enqueue targets. They are governed by FR-012.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Contributor runs an opt-in production build (Priority: P1)

A theme contributor who wants compiled and bundled output runs a single
documented build command and obtains a compiled JS bundle and a
compiled CSS file in a documented output directory.

**Why this priority**: This is the core capability the feature exists
to add. Without a runnable build, the feature does not exist.

**Independent Test**: On a host with the build's required tooling
installed, the documented build command produces the two expected
artifacts within the time bounds defined in SC-001; on a host without
that tooling, the theme's tracked files alone still produce a working
site (covered by Story 2).

**Acceptance Scenarios**:

1. **Given** the repository and a working build environment, **When**
   the contributor runs the documented build command, **Then** a
   compiled JS file and a compiled CSS file appear in the documented
   output directory within the time bounds defined in SC-001.
2. **Given** a populated output directory, **When** the contributor
   enables the documented opt-in switch in `functions.php`, **Then**
   the site renders correctly using the bundled artifacts instead of
   the CDN Vue and un-built `assets/js/app.js`, with the actual
   artifact URLs resolved from the build's manifest per FR-011.

---

### User Story 2 - Site owners are unaffected (Priority: P1)

A site owner who installs only the theme's tracked files (no Node.js,
no `node_modules/`, no build output directory) gets a fully working
site identical to today's CDN-first behavior.

**Why this priority**: The Constitution's Principle V as amended
requires this; it is the primary constraint the feature MUST honor.

**Independent Test**: On a fresh host with only the tracked theme
files, the site loads and is fully interactive using the CDN Vue and
the un-built `assets/js/app.js`.

**Acceptance Scenarios**:

1. **Given** only the tracked theme files, **When** the site loads,
   **Then** Vue is loaded from the documented CDN URL and
   `assets/js/app.js` is loaded directly from the theme directory,
   with no build artifacts referenced and no build command required.
2. **Given** only the tracked theme files, **When** the site owner
   inspects the running site, **Then** the visual and interactive
   behavior is indistinguishable from the pre-feature baseline.

---

### User Story 3 - Source files remain the source of truth (Priority: P2)

A developer who edits `assets/js/app.js` or `style.css` and runs the
build sees the edit reflected in the corresponding build artifact.

**Why this priority**: Without regeneration from source, contributors
would be tempted to edit build outputs directly, which the
Constitution explicitly forbids (build outputs are not version
controlled and are not authoritative).

**Acceptance Scenarios**:

1. **Given** the build is configured with `assets/js/app.js` as the JS
   entry, **When** the developer edits `assets/js/app.js` and runs the
   build, **Then** the bundled JS artifact reflects the edit.
2. **Given** the build is configured with `style.css` as the CSS
   entry, **When** the developer edits `style.css` and runs the
   build, **Then** the compiled CSS artifact reflects the edit.

---

### Edge Cases

- **Build environment unavailable**: The repo MUST be installable and
  usable without the build tooling; the build is opt-in.
- **Stale cached outputs**: The build MUST always read from current
  source, not trust any pre-existing build output.
- **Switching the default enqueue to bundled**: The opt-in switch MUST
  be explicit and MUST NOT be triggered automatically by the build
  configuration being present.
- **Source-of-truth drift**: Editing the build output directory directly
  MUST NOT be a supported workflow; documentation MUST direct
  contributors back to `assets/js/app.js` and `style.css`.
- **Build vs. theme version mismatch**: If `VB_VERSION` is bumped in
  `functions.php` and the `Version:` header in `style.css`, the next
  build MUST reflect those version changes in the build artifacts (or
  the artifacts MUST not be cached in a way that bypasses the version
  bump).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The repo MUST contain a build configuration file that
  declares `assets/js/app.js` as the JS entry and `style.css` as the
  CSS entry, with outputs to a documented directory.
- **FR-002**: A single documented build command MUST produce both a
  compiled JS bundle and a compiled CSS file in the documented output
  directory.
- **FR-003**: The build output directory MUST be excluded from the
  tracked tree (gitignored) so the theme ships without build
  artifacts.
- **FR-004**: Build dependencies MUST be declared in a manifest file
  (e.g., `package.json`) so contributors can install them, but
  installing them MUST be optional for end users.
- **FR-005**: `functions.php` MUST continue to enqueue the CDN Vue
  global and the un-built `assets/js/app.js` by default; switching to
  the bundled artifacts MUST be an explicit configuration choice (a
  documented constant or filter) and MUST NOT be triggered by the
  presence of the build configuration alone.
- **FR-006**: The theme's documentation MUST describe both paths:
  CDN-first (default, no build required) and bundled artifacts
  (opt-in via the documented switch).
- **FR-007**: The build MUST be deterministic: a clean run on
  unchanged source MUST produce stable outputs (byte-identical or
  hash-stable within the build tool's defaults).
- **FR-008**: The build MUST NOT introduce a runtime dependency on
  any build-tool file or `node_modules/` for end users; the site MUST
  load and work without them.
- **FR-009**: The build MUST handle the existing plain-CSS source
  `style.css` without requiring a preprocessor (SCSS, LESS, etc.).
- **FR-010**: The build MUST handle the existing plain-JS source
  `assets/js/app.js` without requiring a transpiler or extra
  type-checking step beyond the build tool's defaults.
- **FR-011**: When the opt-in switch is enabled, `functions.php` MUST
  reference the bundled JS and CSS by reading their hashed filenames
  from a documented manifest file emitted into the build output
  directory (e.g., `dist/manifest.json`), NOT from a fixed path; the
  manifest MUST list one entry per emitted asset with a stable logical
  name. If the manifest is missing or unreadable while the switch is
  enabled, the site MUST fail loudly (no silent fallback to a stale or
  empty `dist/`).
- **FR-012**: The build MUST emit a `.map` file alongside each
  generated JS and CSS artifact; source-map files MUST live in the
  same gitignored output directory as their artifacts and MUST NOT be
  served by default. The manifest (FR-011) MUST list only the main
  JS and CSS artifacts as enqueue targets — never the `.map` files.

### Key Entities *(include if feature involves data)*

- **Build Configuration**: The build tool's configuration file
  declaring JS and CSS entry points and the output directory.
- **Build Artifacts**: The compiled JS and CSS files emitted by the
  build into the output directory; excluded from version control.
- **Source Files**: `assets/js/app.js` and `style.css`; the canonical
  authoring surface — edits here drive the next build.
- **Default Enqueue**: The behavior of `functions.php` that loads
  Vue from CDN and `assets/js/app.js` from the theme directory; this
  MUST remain the default after the feature is added.
- **Opt-in Switch**: The configuration mechanism in `functions.php`
  that, when enabled, redirects the enqueue to the bundled artifacts
  instead of the CDN/asset paths.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: On a host with the build's required tooling installed,
  the documented build command produces both artifacts in **under 2
  minutes** cold (dependencies not yet installed) and **under 30
  seconds** warm (dependencies already present).
- **SC-002**: On a fresh host with only the theme's tracked files,
  the WordPress site loads and is fully interactive without the
  contributor running any build command and without any build-tool
  configuration being required at runtime.
- **SC-003**: After editing `assets/js/app.js` and running the build,
  the edit appears in the bundled JS artifact; after editing
  `style.css` and running the build, the edit appears in the compiled
  CSS artifact.
- **SC-004**: The build output directory contains no files that are
  tracked by version control; running the documented "is this ignored?"
  check returns the expected result for the output directory.
- **SC-005**: The opt-in switch in `functions.php` is **off by
  default** — a fresh clone of the repository continues to enqueue the
  CDN Vue global and the un-built `assets/js/app.js`.
- **SC-006**: The build produces no console errors or warnings on a
  clean run against the current source tree.

## Assumptions

- Target users of the build are **theme contributors**, not site
  owners. Site owners are not expected to build; they get CDN-first
  behavior automatically without any build step.
- The build does NOT introduce a CSS preprocessor (SCSS/LESS). The
  existing `style.css` is plain CSS and the build treats it as such.
- The build does NOT introduce TypeScript or JSX. `assets/js/app.js`
  is plain JavaScript and stays that way.
- The build is run on demand; a continuous watch mode is out of scope
  for v1.
- The build emits outputs to `dist/` at the repository root, per
  Constitution Principle V.
- The opt-in mechanism in `functions.php` is implemented as a single
  documented boolean-style switch that the contributor enables when
  they want to serve the bundled artifacts; the default is "off."
- Source maps, minification knobs, and advanced cache-busting
  decisions are deferred to `/speckit.plan`.
- The repository will add the manifest and the build configuration at
  the repository root (no sub-directory nesting for v1).
- The build's required tooling version (e.g., a Node.js engine range)
  is declared in the manifest and respected by the build commands.