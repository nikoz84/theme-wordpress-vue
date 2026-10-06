# Phase 0 Research: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

This file consolidates research decisions for the design-system
extraction from `layouts-html/01 - Home/01 - safemidia-home-fixed.html`
(Safe Mídia reference) and the theme restructuring.

## R1 — Token extraction approach

**Decision**: Extract the design tokens by transcribing the `:root { … }`
block of `layouts-html/01 - Home/01 - safemidia-home-fixed.html` into
`theme/style.css`. Use the Safe Mídia variable names verbatim
(`--navy`, `--blue`, `--gold`, `--forest`, `--white`, `--off-white`,
`--gray-100`, `--gray-200`, `--gray-text`, `--dark`, etc.).

**Rationale**: The HTML file already declares the design system as
CSS custom properties (lines 13–27 of the reference). The token
names are domain-appropriate (insurance / news portal) and
disambiguated (no name collisions with the existing Vue Blocks
tokens). Re-using the names verbatim reduces the risk of drift
between `DESIGN.md` and the CSS.

**Alternatives considered**:
- *Re-namespace tokens to generic names* (`--color-primary`, etc.) —
  rejected; the reference's names are clearer in context.
- *Split tokens across multiple files* — rejected; per Constitution
  Principle III, `:root` is the single source of truth.

## R2 — Component-to-template-part mapping

**Decision**: Map each Safe Mídia section to one or more
`template-parts/` files inside `theme/`:

| Safe Mídia section | Template part |
|---|---|
| Navbar | `template-parts/header/navbar.php` |
| Hero (main featured + sidebar) | `template-parts/hero/hero.php` |
| News grid (cards) | `template-parts/news/news-grid.php`, `template-parts/news/news-card.php` |
| Footer | `template-parts/footer/site-footer.php` |

**Rationale**: One file per section keeps the templates small,
testable, and individually replaceable. The per-section directory
groups related parts (`header/`, `hero/`, `news/`, `footer/`) for
discoverability.

**Alternatives considered**:
- *One section per template-parts/ root file* — rejected; harder to
  scale when the v2 follow-up adds the seven other sections.
- *Single `home-page.php` with all four sections inlined* —
  rejected; loses the per-section composability.

## R3 — WordPress data flow for the home page

**Decision**: Source the home page content from WordPress:

| Element | WordPress source |
|---|---|
| Featured (hero) post | First sticky post, else the most recent published post (`is_sticky()` + `WP_Query`) |
| Hero sidebar posts | Next 4 most recent published posts excluding the featured |
| News grid | The next 6 most recent published posts excluding the hero + sidebar |
| Footer categories | `wp_list_categories()` |
| Footer links | A registered `footer` menu (`register_nav_menu()` in `functions.php`) |

**Rationale**: Mirrors how the existing Vue Blocks `front-page.php`
already consumes content. No new REST fields required (per FR-008:
the REST surface is unchanged).

**Alternatives considered**:
- *Custom post type for "Destaque"* — rejected; out of scope (no new
  CPT, no migration of sample data).

## R4 — Styling approach

**Decision**: Plain CSS (no SCSS, no LESS, no PostCSS). Use the
existing Vue Blocks `style.css` structure: one file at
`theme/style.css` with a `:root` token block, followed by section
rules (`.navbar`, `.hero`, `.news-grid`, `.footer`) that consume
the tokens.

**Rationale**: Per Constitution Principle V, the theme ships with
no build step. A preprocessor would require a toolchain on the
contributor's host — prohibited by the principle. CSS custom
properties give us enough power for the v1 surface.

**Alternatives considered**:
- *SCSS with a Vite build* — rejected (would require the Vite opt-in
  path to be the default, which it isn't).
- *CSS-in-JS / runtime* — rejected (would contradict server-first
  rendering and progressive enhancement).

## R5 — DESIGN.md format

**Decision**: Markdown file with five sections:

1. **Design tokens** (colors, typography, spacing, radii, shadows) —
   one table per category; each row gives the token name, the
   computed value, and where it's used.
2. **Components** — one sub-section per component: name, purpose,
   when to use / when not, CSS custom properties it consumes, the
   `template-parts/` file that implements it.
3. **Layout patterns** — the four home-page sections described in
   section order, with a short note for each (one paragraph each;
   full visual structure lives in the Safe Mídia reference HTML).
4. **Responsive breakpoints** — tablet (≤1024px), mobile (≤768px),
   small mobile (≤420px); per-breakpoint behavior summary.
5. **Maintenance** — how to keep `DESIGN.md` and `theme/style.css`
   in sync; which file is canonical (the repo-root `DESIGN.md`).
   Includes a one-line note linking the repo root copy to the
   `theme/DESIGN.md` duplicate.

**Rationale**: Five sections is enough to be useful without being
overwhelming. Each section has a clear owner (tokens → CSS; components
→ template-parts; layout → home page; responsive → `style.css` media
queries; maintenance → contribution workflow).

**Alternatives considered**:
- *A single long page with no sections* — rejected; harder to scan.
- *Per-component dedicated docs in `theme/docs/components/`* —
  rejected; over-engineered for the v1 surface.

## R6 — Packaging approach

**Decision**: A shell script `theme/package.sh` that:

1. Validates that the current directory is `theme/` (or accepts an
   explicit `--from` argument).
2. Produces `<theme-slug>-<version>.zip` in the parent directory
   (default name: `vue-blocks-1.0.0.zip`).
3. Excludes test-harness paths via `zip -x` (`.git`, `.gitignore`,
   `package.sh`, `PACKAGE.md`, `DESIGN.md` … wait — keep
   `DESIGN.md` per FR-001a).
4. Excludes Vite output paths (`dist/`, `node_modules/`) for safety.

**Rationale**: A small shell script is the lightest-weight packaging
mechanism; no Node tooling required; works in any contributor's
shell. The `zip -x` flag excludes the script itself and meta files
that should not ship.

**Alternatives considered**:
- *Plain documented `zip -r` command* — rejected; FR-006 says
  "documented packaging command" and the implicit assumption is
  repeatable. A script gives repeatable behavior.
- *Vite-driven bundling* — rejected; out of scope per FR-010 and the
  no-build principle.

## R7 — DESIGN.md duplication strategy

**Decision**: The repo-root `DESIGN.md` is the canonical source. The
in-theme copy is generated at packaging time: `theme/package.sh`
copies the repo-root `DESIGN.md` into `theme/DESIGN.md` if the
in-theme copy is missing or older than the source.

**Rationale**: One canonical source prevents drift. The
in-packaging-time copy keeps the workflow simple (no extra script,
no watcher).

**Alternatives considered**:
- *Symlink the in-theme copy to the repo root* — rejected; many
  zip tools don't follow symlinks reliably.
- *Manually keep two files* — rejected; drift is the failure mode.

## R8 — Verifying no test-harness leakage (SC-006)

**Decision**: A `grep`-based smoke test in `theme/package.sh`'s
post-build hook:

```sh
if unzip -l "${ZIP_PATH}" | grep -E '(node_modules|dist|docker-compose\.yml|\.env$|bin/bootstrap\.sh)'; then
  fail "Package contains test-harness paths; aborting."
fi
```

**Rationale**: SC-006 is a static check on the produced archive. The
simplest correct implementation is a `grep` over `unzip -l`'s listing.
It runs in milliseconds and catches the regression directly.

**Alternatives considered**:
- *A dedicated test harness* — overkill for one grep rule.

## R9 — Theme activation flow post-restructuring

**Decision**: The Docker harness's bind mount is updated from
`./` (current state, the entire repo root) to `./theme` (the new
home of the theme files). All other harness configuration
(compose, env, bootstrap, seed) is unchanged.

**Rationale**: Per SC-005, the harness must keep working. Updating
the bind mount is a one-line edit; everything else cascades from
that.

**Alternatives considered**:
- *Update the bind mount to `./theme:/var/www/html/wp-content/themes/vue-blocks`*
  — this is the chosen path; the existing mount is already
  `.:/var/www/html` (whole theme into WP). Wait, looking at the
  current `docker-compose.yml`, the mount is `.:/var/www/html/wp-content/themes/vue-blocks`
  (repo root into theme subdir of WP). The new mount will be
  `./theme:/var/www/html/wp-content/themes/vue-blocks` — same
  shape, just the host path updated.

## R10 — Migration approach (existing theme → `theme/`)

**Decision**: Move the existing theme files from the repo root into
`theme/` via the git mv-equivalent (file system move, since the
repo is not yet under git). The Docker harness's bind-mount path
is updated in the same change.

**Rationale**: This is the simplest physical move; no import work is
needed because the theme files are not duplicated elsewhere.

**Alternatives considered**:
- *Symbolic link `theme` → repo root* — rejected; breaks the
  packaging workflow (zip follows symlinks differently per host OS).

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified during
`/speckit.clarify` (theme dir name `theme/`, DESIGN.md duplication
strategy, home page scope = 4 core sections) and all defaults
documented in the spec's `## Assumptions` section hold.

## Deferred to `/speckit.tasks`

- Exact CSS class names per component (mostly transcribed from the
  Safe Mídia HTML).
- The final token catalog (will be transcribed 1:1 from the
  reference's `:root` block).
- The packaging script's exact `zip -x` patterns.