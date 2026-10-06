# Phase 1 Data Model: Vite Build Pipeline for Vue Blocks

**Date**: 2026-10-06
**Spec**: `specs/002-vite-build-pipeline/spec.md`
**Branch**: `002-vite-build-pipeline`

This file enumerates the entities defined in the spec's
`### Key Entities` section, attributes each one, and notes the
validation rules and lifecycle / state transitions that the
implementation MUST honor.

---

## E1 — Build Configuration

A single file (the build tool's configuration) that declares the JS
and CSS entries, the output directory, and the manifest emission.

| Field | Type | Required | Description |
|---|---|---|---|
| `root` | path | yes | Repository root (i.e., the theme root). |
| `entries` | array<EntrySpec> | yes | One entry per emitted bundle (currently exactly two: `app` JS and `style` CSS). |
| `outDir` | path | yes | MUST be `dist/` (per Constitution Principle V). |
| `manifest` | ManifestSpec | yes | Where to emit the manifest and what shape to use. |
| `sourceMap` | bool | yes | Whether to emit `.map` files alongside each artifact. MUST be `true` per FR-012. |
| `minify` | bool | no | Whether to minify the JS/CSS output. Defaults to `true` (Vite's standard for production builds). |

### EntrySpec

| Field | Type | Description |
|---|---|---|
| `logicalName` | string | Stable name used by `functions.php` (e.g., `app`, `style`). MUST be unique per build. |
| `source` | path | Path to the source file, relative to repo root. Currently `assets/js/app.js` or `style.css`. |

### ManifestSpec

| Field | Type | Description |
|---|---|---|
| `path` | path | MUST be `dist/manifest.json`. |
| `shape` | enum | Either `vite-default` (Vite's stock manifest) or `logical-alias` (the shape documented in [`contracts/manifest.schema.json`](contracts/manifest.schema.json)). |

### Validation

- `logicalName` MUST match `^[a-z][a-z0-9-]{0,16}$` (lowercase, kebab/
  digits only; matches the project's `vb-` / `vb_` / `VB_` style).
- `source` MUST exist at repo time.
- `outDir` MUST be gitignored.
- Exactly two entries: one JS, one CSS.

### Lifecycle

The Build Configuration is **authored once** and **read at every
build invocation**. It is **never read at runtime** — `functions.php`
reads only the manifest emitted by this configuration.

---

## E2 — Build Artifacts

Files emitted into `dist/` by the build.

| Artifact | Path pattern | Required | Source |
|---|---|---|---|
| JS bundle | `dist/assets/app.[contenthash].js` | yes | `assets/js/app.js` |
| JS source map | `dist/assets/app.[contenthash].js.map` | yes | the JS bundle |
| CSS bundle | `dist/assets/style.[contenthash].css` | yes | `style.css` |
| CSS source map | `dist/assets/style.[contenthash].css.map` | yes | the CSS bundle |
| Manifest | `dist/manifest.json` | yes | the build |

### Validation

- Every emitted file MUST live under `dist/`.
- The `[contenthash]` MUST be Vite's content hash (stable across
  rebuilds of unchanged source).
- The `.map` files MUST NOT appear in `manifest.json`'s
  enqueue-target keys (per FR-012).
- The whole `dist/` tree MUST be gitignored (per FR-003).

### Lifecycle

| State | Trigger |
|---|---|
| **Absent** | Fresh checkout, build not yet run. CDN-first path is the only working option. |
| **Populated, current** | Build has run; `dist/manifest.json` references match the emitted artifacts. |
| **Stale** | Source files changed but `dist/` was not rebuilt. Hash mismatch between manifest and actual files — `functions.php` MUST treat as loud failure if the opt-in switch is on. |
| **Removed** | `npm run clean` or manual `rm -rf dist/`. CDN-first path resumes as the only working option. |

---

## E3 — Source Files

The canonical authoring surface for the bundled output.

| File | Role |
|---|---|
| `assets/js/app.js` | JS source; edits drive the JS bundle. |
| `style.css` | CSS source; edits drive the CSS bundle. |

### Validation

- Both MUST exist for the build to succeed.
- `assets/js/app.js` MUST remain a plain JavaScript file (no
  TypeScript / JSX) per FR-010.
- `style.css` MUST remain a plain CSS file (no SCSS / LESS) per
  FR-009; its `:root` token block MUST survive the build unchanged.

### Lifecycle

Source files are **never modified by the build**. Edits here drive
rebuilds; rebuilds drive new artifacts; the cycle continues. Source
files have no Versioned-Type — they are version-controlled like any
other source.

---

## E4 — Default Enqueue (CDN-first)

The behavior of `functions.php` that loads Vue from CDN and the
un-built `assets/js/app.js` from the theme directory.

### Validation

- MUST be the **default** (the opt-in switch starts `false`).
- MUST enqueue `vue-js` (CDN) and `vue-blocks-app` (theme's un-built
  `assets/js/app.js`) — exact handle names from
  `vb_enqueue_assets()` in `functions.php`.
- MUST NOT load any `dist/*` file.
- MUST be functionally identical to today's behavior; visual and
  interactive parity is part of SC-002.

### State transitions

| Trigger | Result |
|---|---|
| `$VB_USE_BUNDLED_ASSETS === false` (default) | Default Enqueue runs. |
| `$VB_USE_BUNDLED_ASSETS === true` and `dist/manifest.json` present | Default Enqueue is skipped; Opt-in Enqueue runs instead. |
| `$VB_USE_BUNDLED_ASSETS === true` and `dist/manifest.json` missing | Loud failure per FR-011. |

---

## E5 — Opt-in Switch

The boolean-style toggle that redirects `functions.php` to the
bundled artifacts.

| Field | Type | Default | Description |
|---|---|---|---|
| `value` | bool | `false` | When `true`, enqueue bundled artifacts; when `false`, enqueue CDN-first. |

### Validation

- The switch MUST be consultable via a single, documented PHP
  constant (`VB_USE_BUNDLED_ASSETS`).
- The default MUST be `false` (per FR-005 verbatim).
- The switch MUST be checked **exactly once per request**, after the
  manifest has been read (or attempted to be read).
- Reading the manifest MUST be guarded by `is_readable()` to avoid
  PHP warnings when `dist/` is absent.
- If the manifest is unreadable or missing while the switch is on,
  the implementation MUST `trigger_error()` with an actionable
  message naming the missing file (per FR-011); silent fallback to
  the CDN-first path is NOT allowed.

### State transitions

The switch's value is **set by the contributor** in `wp-config.php`
or a child theme; the theme itself never writes to it.

---

## Cross-entity invariants

1. The Default Enqueue and the Opt-in Enqueue are **mutually
   exclusive** for any given asset (Vue, apparel) per request.
2. The Build Configuration and the Source Files are **read-only at
   runtime**; only the build itself writes to the Build Artifacts.
3. The Opt-in Switch's value is the **only** runtime input that
   selects between the two enqueue paths.
4. The Constitution's Principle V is the **governing invariant** for
   this whole graph: the Default Enqueue MUST always be reachable
   from a fresh checkout with no build step.

---

## Out-of-scope entities (deliberately omitted)

- **CI configuration** — not part of this feature; the spec marks
  CI as out of scope in Assumptions.
- **Multi-theme / multisite manifests** — the manifest is per-repo;
  no merging or federation.
- **HMR / dev server** — not part of v1; Assumptions defer watch
  mode.