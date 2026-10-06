# Phase 0 Research: Vite Build Pipeline for Vue Blocks

**Date**: 2026-10-06
**Spec**: `specs/002-vite-build-pipeline/spec.md`
**Branch**: `002-vite-build-pipeline`

This file consolidates the research needed to resolve every
`NEEDS CLARIFICATION` item and every technology-choice question that
emerged during planning. The format follows the project's planning
convention: **Decision** / **Rationale** / **Alternatives considered**.

## R1 — Build tool selection

**Decision**: Use **Vite** (latest stable, 5.x or 6.x).

**Rationale**: The user explicitly asked for Vite ("add support for
vite"). Vite is the de facto standard for modern JS/CSS bundling and
has first-class support for plain JS + plain CSS with no configuration,
which is exactly what the existing source files require. It emits
content-hashed filenames and an `assets`-shaped manifest out of the
box (or with the standard `vite-plugin-manifest` for a custom shape),
and it generates source maps by default in development and on
explicit configuration in production.

**Alternatives considered**:
- *Rollup* — Vite uses Rollup under the hood, so we get Rollup's
  maturity "for free" without owning a Rollup config directly.
- *esbuild* standalone — would require writing our own manifest
  emission and source-map wrapping; net loss of time.
- *webpack* — significantly more configuration overhead for a
  two-entry build; rejected.

## R2 — Output directory and manifest shape

**Decision**: Emit to `dist/` (per Constitution Principle V). The
manifest MUST be `dist/manifest.json` with logical-name → hashed-file
mappings keyed by the source entry's basename (e.g., `app`,
`style`). The contract is formalized in
[`contracts/manifest.schema.json`](contracts/manifest.schema.json).

**Rationale**: `dist/` is the conventional Vite output location and
is gitignored by default. A flat, two-level manifest shape makes the
PHP reader trivial: `decode → look up key → enqueue`. Vite's default
manifest uses relative paths keyed by source-relative paths
(e.g., `assets/js/app.js`); for our two-entry case, we expose a
**logical alias** (`app`, `style`) so the PHP side is decoupled from
the build's source-relative key naming.

**Alternatives considered**:
- *Vite's default `manifest.json` without an alias* — works but
  forces `functions.php` to know the source-relative path
  (`assets/js/app.js`), which leaks build-tool internals into the
  theme runtime. Rejected.
- *Emitting each entry as its own file and reading them via
  sidecar index* — more files, more failure modes. Rejected.

## R3 — Opt-in switch mechanism in `functions.php`

**Decision**: Implement the opt-in as a single PHP boolean constant
`VB_USE_BUNDLED_ASSETS`, defaulting to `false`. When `false`, the
existing CDN-first enqueue runs unchanged. When `true`,
`vb_enqueue_bundled_assets()` reads `dist/manifest.json` via
`file_get_contents` (with a `is_readable` guard) and enqueues each
hashed artifact via `wp_enqueue_script` / `wp_enqueue_style`,
failing loudly if the manifest is missing (no silent degradation — per
FR-011). The switch is consulted exactly once per request.

**Rationale**: A constant is the standard WordPress extension surface
(matches e.g., `WP_DEBUG`, `SCRIPT_DEBUG`). It is overridable in a
child theme's `wp-config.php`-adjacent setup or via a `phpcs`
filter — without bringing in a full filter API surface that would
add complexity. Loud-failure behavior on a missing manifest is
required by FR-011 and prevents the "site loads but features
silently vanish" failure mode.

**Alternatives considered**:
- *Filter (`apply_filters`)* — more powerful but adds a callback
  chain for a single binary decision; rejected as overkill.
- *Database option* — adds a settings UI for a contributor-facing
  flag; rejected (the build is opt-in for contributors, not
  something site owners configure).
- *Auto-derived from build artifact presence* — explicitly
  forbidden by FR-005 ("MUST NOT be triggered by the presence of
  the build configuration alone"). Rejected.

## R4 — Cache-busting (already resolved during clarify)

**Decision**: Content-hash in filename + manifest read at enqueue
time. Established in `/speckit.clarify` Q1; formalized here for
reference. The hash strategy is Vite's default (Rollup's
`[name].[contenthash].js`). The opt-in switch in `functions.php`
appends the manifest-resolved filename to the existing
`get_theme_file_uri()` calls; no separate `?ver=` query string is
needed (the hash IS the version).

**Rationale**: Hash-stable filenames make the bundled assets
"forever-cacheable" by browsers and CDNs; only re-issuance of the
hash triggers a new fetch.

## R5 — Source maps (already resolved during clarify)

**Decision**: Emit `.map` files alongside each artifact (per
`/speckit.clarify` Q2). The manifest MUST NOT list them as
enqueue targets — they live in `dist/` for production debugging but
are not loaded by the site by default.

**Rationale**: Production debugging benefits exceed the small disk
overhead. The host may choose to serve or block `.map` requests at
the HTTP layer; the theme itself never enqueues them.

## R6 — Build dependencies declaration

**Decision**: Add `package.json` at the theme root with `devDependencies`
listing `vite` (and only a small companion like `vite-plugin-manifest`
if a custom manifest shape is needed; otherwise zero additional
plugins). Declare `engines.node` to the active LTS line at the time
of implementation. Add `.gitignore` entries for `dist/` and
`node_modules/`.

**Rationale**: Standard Node.js project layout; manifest shape
matched to the contract; explicit Node.js range prevents CI /
contributor surprises.

**Alternatives considered**:
- *Single-file build via `npx vite build`* with no
  `package.json` — works for a one-off but loses the engines
  declaration and the script ergonomics (`npm run build`). Rejected.

## R7 — Source-file scope

**Decision**: Two entries: `assets/js/app.js` (JS entry) and
`style.css` (CSS entry). No other files are added to the build.

**Rationale**: The spec's Story 3 ties the build to those two
canonical sources. The `layouts-html/` directory observed in the
repository is design exploration and is correctly excluded from the
deployed theme's source tree.

## R8 — Documentation

**Decision**: Update `readme.txt` with a "Building the theme (optional)"
section that documents:
1. The CDN-first default and why it exists.
2. How to install build deps and produce `dist/`.
3. How to enable `VB_USE_BUNDLED_ASSETS` (constant in `wp-config.php`
   or theme's `functions.php`).
4. The gitignore expectations (`dist/`, `node_modules/`).

**Rationale**: The opt-in path is a contributor workflow; the
documentation MUST live alongside the theme's existing
`readme.txt` so that any site owner browsing the theme's README
sees the CDN-first default first.

## R9 — Build determinism

**Decision**: Rely on Vite's default determinism (Rollup content
hashes are deterministic given identical source). No additional
hash-stabilization plugins required for v1.

**Rationale**: Vite + Rollup produce stable hashes from
content alone; rebuilding the same source produces the same
hashed filenames. The `dist/` content is therefore diffable
across rebuilds for unchanged source.

## R10 — Failure modes handled by the implementation

| Scenario | Handling |
|---|---|
| `VB_USE_BUNDLED_ASSETS = true` and `dist/` missing | `wp_die()` or `trigger_error()` with an actionable message (no silent fallback). |
| `dist/manifest.json` missing | Same loud-failure as above. |
| Manifest missing a logical key (`app`, `style`) | Loud failure naming the missing key. |
| `package.json` corrupted / missing | Build fails at `npm install` with the npm tool's standard error — out of theme's scope. |
| CDN unpkg.com unreachable | Unaffected — CDN-first path doesn't use it for build; only the runtime CDN enqueue uses it (and the Constitution Principle II guarantees a no-JS fallback exists for every interactive feature). |

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified during
`/speckit.clarify` and all defaults documented in the spec's
`## Assumptions` section hold.

## Deferred to `/speckit.tasks`

- Exact Node.js version baseline (decided at task creation time
  from the active LTS).
- Manifest plugin selection (only needed if a custom shape is
  desired; default to no plugin).
- `.gitignore` wording for `dist/` and `node_modules/`.
- `package.json` script names (`build`, `dev` if added).
- Style.css custom-property preservation across the build (Vite
  passes CSS custom properties through; this is a verification step
  rather than a design step).