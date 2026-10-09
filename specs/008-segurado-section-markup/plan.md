# Implementation Plan: Segurado Section Markup

**Branch**: `008-segurado-section-markup` | **Date**: 2026-10-07 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/008-segurado-section-markup/spec.md`

## Summary

Create a structured markup section for displaying and editing segurado (insured/policyholder) information in the WordPress theme. The feature follows server-first rendering (PHP template parts), progressive enhancement (Vue.js inline editing), and WordPress REST API conventions. All markup is semantic, accessible, and works without JavaScript.

## Technical Context

**Language/Version**: PHP 7.4+, JavaScript (ES2015+), Vue.js 3.4.x

**Primary Dependencies**: WordPress 6.0+, Vue.js 3.4.31 (CDN), WordPress REST API

**Storage**: WordPress post meta (segurado fields stored as `vb_segurado_*` meta keys on `post` post type)

**Testing**: Playwright (e2e), WordPress Theme Check, manual validation

**Target Platform**: Web (WordPress theme, modern browsers)

**Project Type**: Web application (WordPress theme with Vue.js progressive enhancement)

**Performance Goals**: Display page loads in <1.5s on 3G; inline edit auto-save in <500ms median

**Constraints**: No build step required (CDN-first); all output escaped; CSS tokens only; `vb-` prefix for all classes/functions

**Scale/Scope**: Single theme; segurado data for policy posts; no multi-user or multi-site requirements

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|---|---|---|
| I. Server-First Rendering (PHP) | ✅ Pass | FR-001 mandates PHP server-side markup; Vue is enhancement only |
| II. Progressive Enhancement | ✅ Pass | FR-003 requires non-JS edit form; FR-006 adds Vue enhancement |
| III. Template Hierarchy Discipline | ✅ Pass | Uses `template-parts/` for reusable markup; follows WP structure |
| IV. REST API as PHP↔Vue Bridge | ✅ Pass | FR-002, FR-005 use WP REST API with nonce via `vbData` |
| V. CDN-First Distribution | ✅ Pass | Vue loads from CDN; no build step required; SC-007 verifies |

**No violations.** All design decisions align with the Constitution.

## Project Structure

### Documentation (this feature)

```text
specs/008-segurado-section-markup/
├── spec.md              # Feature specification
├── plan.md              # This file (implementation plan)
├── research.md          # Phase 0 output (research findings)
├── data-model.md        # Phase 1 output (entity definitions)
├── quickstart.md        # Phase 1 output (validation guide)
├── contracts/           # Phase 1 output (interface contracts)
│   └── segurado-rest-api.contract.md
├── checklists/          # Quality validation
│   └── requirements.md
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
theme/
├── functions.php                    # Extend vb_register_rest_fields(), add segurado meta registration
├── template-parts/
│   └── segurado/
│       ├── segurado-display.php     # Server-rendered display markup (semantic HTML)
│       └── segurado-edit.php        # Server-rendered edit form (non-JS compatible)
├── inc/
│   └── segurado.php                 # Helper functions: validation, sanitization, template tags
├── assets/
│   └── js/
│       └── app.js                   # Add Vue app for inline editing (mount on .vb-segurado-edit)
└── style.css                        # Add segurado section styles using CSS custom properties
```

**Structure Decision**: Extends the existing theme structure. New template parts live in `template-parts/segurado/`, helper functions in `inc/segurado.php`, and the Vue app is added to the existing `assets/js/app.js`. No new top-level directories or build tools introduced.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No violations — this section is not applicable.

---

## Phase 0: Research

See [research.md](./research.md) for full research findings.

Key decisions:
- **R1**: Post meta storage (not separate CPT)
- **R2**: Extend `vb_register_rest_fields()` + `register_meta()`
- **R3**: Vue app in `app.js` with `data-*` attributes for initial values
- **R4**: Two-layer validation (PHP server-side + Vue client-side)
- **R5**: New CSS custom properties for segurado tokens
- **R6**: `vue-blocks` text domain for all i18n strings

## Phase 1: Design & Contracts

### Data Model

See [data-model.md](./data-model.md) for full entity definitions.

Key entities:
- **E1 — Segurado**: 11 fields stored as post meta, exposed via REST API
- **E2 — Policy**: Post with segurado meta (1:1 relationship)

### Contracts

See [contracts/segurado-rest-api.contract.md](./contracts/segurado-rest-api.contract.md) for the REST API contract.

Key endpoints:
- `GET /wp-json/wp/v2/posts/{id}` — returns `vb_segurado` field
- `POST /wp-json/wp/v2/posts/{id}` — updates segurado meta (requires nonce)

### Quickstart

See [quickstart.md](./quickstart.md) for validation scenarios.

7 validation scenarios covering display, edit, Vue enhancement, REST API, non-JS fallback, Theme Check, and zero-build compatibility.