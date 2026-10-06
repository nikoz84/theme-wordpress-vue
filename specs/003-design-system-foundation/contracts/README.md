# Contracts: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

This directory holds the interface definitions for the design system
and the packaged theme.

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `design-tokens.schema.md` | Design → `theme/style.css` + `DESIGN.md` | Defines the design-token catalog's required fields and validation rules |
| `theme-package.manifest.md` | Build → `.zip` | Defines what the produced zip MUST contain and what it MUST NOT contain |

## Contract reference

### Design tokens (per `design-tokens.schema.md`)

The design-token catalog is a Markdown table at `DESIGN.md` § "Design
tokens". Each row has:

- **Token** — kebab-case name matching the CSS custom property in
  `theme/style.css`'s `:root` block.
- **Value** — the computed value (color, font, spacing, radius, or
  shadow).
- **Used in** — one-line description of where the token is
  consumed.

Validation: every token in `theme/style.css` MUST appear in
`DESIGN.md`, and vice versa.

### Theme package (per `theme-package.manifest.md`)

The packaged zip is a JSON-or-Markdown manifest that names the files
that MUST be present (e.g. `style.css`, `functions.php`,
`template-parts/...`) and the paths that MUST NOT appear (e.g.
`node_modules/`, `dist/`, `docker-compose.yml`, `.env`).

Validation: a post-build `grep` over `unzip -l` MUST succeed.

## Cross-contract invariants

- The Design Token catalog MUST stay in sync with `theme/style.css`
  (a contributor changes one MUST update the other; the
  `package.sh` script can warn if it detects a mismatch at
  packaging time).
- The Theme Package MUST NOT include any Test Harness entity
  (per E5 in the data model).

## Related but separate surfaces

These are part of the contract surface but documented in the data
model:

- Component slugs, paths, and consumed tokens (E2 in `data-model.md`).
- Section order and the components each section is composed of
  (E3 in `data-model.md`).

## Out-of-scope surfaces (deliberately omitted)

- The packaged theme's UI behavior contract (server-rendered vs.
  Vue-enhanced) — already covered by the Constitution; the packaged
  theme inherits the same guarantees as the in-repo theme.
- A formal API contract for the WordPress REST fields surfaced to
  Vue — unchanged by this feature (FR-008 of the original spec).