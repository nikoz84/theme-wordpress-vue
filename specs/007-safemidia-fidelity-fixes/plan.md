# Implementation Plan: Safe Mídia Visual Fidelity Fixes

**Branch**: `007-safemidia-fidelity-fixes` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/007-safemidia-fidelity-fixes/spec.md`

## Summary

Two targeted edits to bring the shipped theme's Safe Mídia visual
parity back to the reference:

1. **`theme/template-parts/home/segurado.php`** — wrap the
   "Para o Segurado" heading in the full Safe Mídia structure
   (`.segurado-hd` + `.segurado-title` + `.segurado-sub` +
   `.segurado-btn-all` with the Safe Mídia subtitle text and a "Ver
   todos" link to the "Para o Segurado" category archive).
2. **`theme/template-parts/footer/site-footer.php`** — replace the
   footer-bottom `esc_html( $vb_site_title )` echo with the
   hardcoded brand string "Safe Mídia" so the copyright is decoupled
   from `bloginfo('name')` (and therefore from the Customizer's Site
   title).

Plus 2 Playwright specs covering both fixes (extending or adding to
the existing `tests/e2e/` suite).

The design-system tokens at `theme/style.css`'s `:root` and
`DESIGN.md` are unchanged (per FR-007 / SC-004).

## Technical Context

**Language/Version**:
- PHP 7.4+ (runtime, theme's existing constraint).
- Plain CSS, plain JavaScript (no build step — per Constitution
  Principle V).
- TypeScript (the Playwright test file is `.ts`).

**Primary Dependencies**:
- The existing Playwright infrastructure from feature 006
  (`@playwright/test`, `playwright.config.ts`, the `VB_TEST_SHIM`
  env var on the Docker harness).
- WordPress 6.0+ (the Docker harness the suite tests against).
- The existing Safe Mídia CSS classes (`.segurado-hd`,
  `.segurado-title`, `.segurado-sub`, `.segurado-btn-all`,
  `.footer-bottom`) — all already declared in `theme/style.css`'s
  Safe Mídia section; no new styles needed.

**Storage**:
- Files only (template part edits, one footer-line text change, two
  test files).

**Testing**:
- Visual: open `http://localhost:8080/` in a browser at the five
  test viewports; confirm `.segurado-hd` exists with the four
  children AND the footer-bottom contains "Safe Mídia" regardless
  of the Customizer's Site title.
- E2e: `npm run test:e2e` exits 0 (per SC-003). The new specs (or
  extensions of existing specs) cover both fixes.

**Target Platform**:
- Same as feature 006 — the existing Docker stack at
  `http://localhost:8080`.

**Project Type**: Web application — additive template part edits +
  test coverage.

**Performance Goals**:
- Negligible — one template-part markup change (adds 4 elements)
  + one footer-line text change.

**Constraints**:
- No new build step in the packaged theme (Principle V).
- The design-system tokens at `theme/style.css`'s `:root` and
  `DESIGN.md` MUST NOT change (per FR-007 / SC-004).
- The Customizer's Site title continues to drive the document
  `<title>` tag and other `bloginfo('name')` consumers (preserved
  from feature 006's US2).
- The `theme/package.sh` output remains ≤ 5 MB after this feature
  lands (the test files are repo-root dev-only — NOT inside
  `theme/`).

**Scale/Scope**:
- 1 template part edit (segurado.php — markup restructure).
- 1 template part edit (site-footer.php — 1-line text change).
- 2 Playwright spec files (or 1 new spec + 1 extension of the
  existing `title-decoupling.spec.ts`).
- 0 new CSS rules.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | Both edits are server-side template-part markup; no client-side rendering involved. |
| II. Progressive Enhancement | **Pass** | The new `.segurado-hd` markup works without JS; the "Ver todos" link is a plain anchor (no JS required). |
| III. WP Template Hierarchy Discipline | **Pass** | No template-file structure changes; only content edits inside existing template parts. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST endpoints added. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | No build step introduced. The packaged theme is unaffected (test files live at the repo root, NOT in `theme/`). |
| Technical Constraints | **Pass** | PHP 7.4+, WP 6.0+, Vue 3.4.x, `vue-blocks` text domain — all preserved. |
| Development Workflow | **Pass** | File ownership preserved; new files at `tests/e2e/` only. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

| Principle | Status post-design | Evidence |
|---|---|---|
| I–V (unchanged) | **Pass** | Both edits are localized to two template-part files + two test files. |
| V (CDN-first) | **Pass** | Test files live at the repo root; `theme/package.sh`'s exclusion patterns cover them. |
| Technical Constraints | **Pass** | The design tokens at `:root` and `DESIGN.md` are unchanged. |

**Final gate verdict**: PASS.

## Project Structure

### Documentation (this feature)

```text
specs/007-safemidia-fidelity-fixes/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── checklists/
│   └── requirements.md
└── spec.md              # already created
```

### Source Code (modifications)

```text
theme/
├── template-parts/
│   ├── home/
│   │   └── segurado.php             # MODIFY — add .segurado-hd wrapper with .segurado-title / .segurado-sub / .segurado-btn-all
│   └── footer/
│       └── site-footer.php          # MODIFY — replace $vb_site_title echo with hardcoded "Safe Mídia"
└── style.css                        # UNCHANGED — the Safe Mídia classes already exist

tests/
└── e2e/
    ├── fixtures.ts                   # UNCHANGED — existing shim helpers
    ├── segurado-markup.spec.ts       # NEW — asserts .segurado-hd structure (US3 / SC-001)
    └── (existing title-decoupling.spec.ts) EXTEND — footer-bottom must contain "Safe Mídia" even after blogname mutation
```

**Structure Decision**: Two template-part edits + one new spec file +
extend an existing spec. No new CSS rules, no new REST endpoints, no
new build surface.

**Migration of EXISTING content**: 
- `theme/template-parts/home/segurado.php` keeps its WP_Query loop (FR-004 empty-state
  rule preserved); the new `.segurado-hd` wrapper goes ABOVE the
  loop, between `<section class="segurado-section"><div class="segurado-inner vb-container">`
  and `<div class="segurado-block">`.
- `theme/template-parts/footer/site-footer.php` keeps its `</footer>` close
  and the rest of the columns; only the `footer-bottom` line's
  `$vb_site_title` is replaced with the hardcoded brand string.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.