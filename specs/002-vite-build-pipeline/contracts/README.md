# Contracts: Vite Build Pipeline for Vue Blocks

**Date**: 2026-10-06
**Spec**: `specs/002-vite-build-pipeline/spec.md`
**Branch**: `002-vite-build-pipeline`

This directory holds the interface definitions shared between the
Vite build (writer) and the theme's runtime (reader).

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `manifest.schema.json` | Build → `functions.php` | Schema for `dist/manifest.json`. The build writes this file; `functions.php` reads it when the opt-in switch is on. |

## Contract reference

### `dist/manifest.json` (per `manifest.schema.json`)

| Logical key | Source file | Required |
|---|---|---|
| `app` | `assets/js/app.js` | yes |
| `style` | `style.css` | yes |

Each entry exposes:

| Field | Type | Meaning |
|---|---|---|
| `file` | string | Path of the emitted artifact relative to the repository root. |
| `isEntry` | bool (always `true` for this theme) | Confirms the entry is at the root of its graph. |

The schema is intentionally **minimal**: Vite's default manifest
contains more fields (`imports`, `dynamicImports`, `assets`, etc.),
which are **not** required by the contract and may be present or
absent. `functions.php` MUST ignore fields it does not understand.

## Cross-contract invariants

- The keys `app` and `style` MUST be present whenever
  `VB_USE_BUNDLED_ASSETS` is enabled. A missing key MUST trigger a
  loud failure (per FR-011 in the spec).
- The `file` paths MUST live under `dist/`. Any path outside `dist/`
  MUST be rejected by `functions.php`.
- Source maps are governed by the spec (FR-012) and the data model;
  they are **not** part of this contract.

## Related but separate surfaces

These are not formal contracts but are part of the opt-in surface
and are documented in the data model:

- The `VB_USE_BUNDLED_ASSETS` constant in `functions.php` — see
  data model E5.
- The `package.json` `engines.node` declaration — see research R6.

## Out-of-scope surfaces (deliberately omitted)

- A REST endpoint for the build (no server-side rebuild API).
- A multi-tenant manifest (one theme, one manifest).
- HMR / live-reload messaging format (deferred to a future feature).