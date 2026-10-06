# Implementation Plan: Vite Build Pipeline for Vue Blocks

**Branch**: `002-vite-build-pipeline` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/002-vite-build-pipeline/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Add an **opt-in** Vite-based build pipeline to the Vue Blocks WordPress
theme that emits a compiled JS bundle and a compiled CSS file into a
gitignored `dist/` directory, alongside a JSON manifest of hashed
filenames. The build is opt-in only: the existing CDN-first default
(CDN Vue global + un-built `assets/js/app.js` + `style.css`) MUST
remain the default enqueue behavior of `functions.php`, and the
presence of the build configuration alone MUST NOT flip the switch.

The opt-in switch (a single boolean-style toggle in `functions.php`)
redirects `wp_enqueue_script` / `wp_enqueue_style` to read the hashed
filenames from the manifest and enqueue those artifacts. The site
works without `node_modules/`, without `dist/`, and without ever
running the build — preserving the drop-in installation promise
enshrined in Constitution Principle V (v1.1.0).

## Technical Context

**Language/Version**:
- PHP 7.4+ (existing theme constraint, unchanged).
- Node.js — declared in the build manifest's `engines.node` field; the
  plan is agnostic to the exact minimum, but the contributor-facing
  baseline is the active LTS line at the time of implementation.

**Primary Dependencies**:
- **Vite** (latest 5.x or 6.x stable) — build tool, owns entry
  resolution, bundling, hashing, manifest emission, and source-map
  generation.
- **WordPress / `wp_enqueue_*`** — runtime asset loading.
- **PHP `json_decode`** (built-in) — manifest reader in
  `functions.php`; no third-party PHP dependency required.

**Storage**: Files only (`dist/` directory and a single
`dist/manifest.json`). No database changes. No new tables or options.

**Testing**:
- Manual smoke: `npm install && npm run build` then inspect `dist/`
  contents.
- Manifest contract check: parse `dist/manifest.json` and verify
  expected logical entries (`app`, `style`) are present with hashed
  filenames and source-map companions.
- `functions.php` opt-in check: with the switch on, verify the
  enqueued URLs resolve via the manifest; with the switch off,
  verify the CDN Vue + un-built `app.js` paths are used.
- CDN-first test: with only tracked theme files (no `dist/`, no
  `node_modules/`), verify the site loads and is interactive.

**Target Platform**:
- Build: any host with Node.js available (Linux, macOS, WSL2).
- Runtime: WordPress 6.0+ on PHP 7.4+ (unchanged).

**Project Type**: Web application — WordPress theme with build
tooling layered in. The build artifacts are an **additive** output
layer over the existing theme; the theme's runtime is unchanged.

**Performance Goals**:
- Build: cold ≤ 2 minutes, warm ≤ 30 seconds (per spec SC-001).
- Runtime: no measurable impact on time-to-interactive for the
  CDN-first path; for the opt-in path, hash-based filenames improve
  long-term caching of static assets.

**Constraints**:
- CDN-first default MUST remain untouched for site owners who do
  not opt in (Constitution Principle V v1.1.0).
- `dist/` MUST be gitignored.
- No Node.js, no `node_modules/`, no `dist/` required at runtime.
- The opt-in switch MUST be explicit; the presence of `package.json`
  or any build configuration MUST NOT cause `functions.php` to
  redirect enqueues automatically.

**Scale/Scope**:
- Single theme, two source entries, two-three emitted artifacts
  (JS, CSS, source maps). No multi-target builds. No monorepo.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Each Constitution principle is evaluated against the planned
implementation:

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | Build does not alter PHP rendering paths; `functions.php` still emits HTML through the same template hierarchy. |
| II. Progressive Enhancement | **Pass** | Build only changes the runtime asset URL; the no-JS fallback (search form, pagination) is unchanged. |
| III. WP Template Hierarchy Discipline | **Pass** | Build does not add, remove, or rename any template files. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | Build does not change REST endpoints, nonces, or `vbData` semantics. The un-built `assets/js/app.js` continues to call the same REST endpoints with the same nonce header. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | The plan explicitly preserves the default; FR-005, FR-011 (peer), FR-012 (peer) and the new FR-011 in the spec enforce this. The opt-in switch is explicit (not auto-derived from build artifacts). |
| Technical Constraints | **Pass** | PHP, WordPress, Vue versions unchanged. CSS tokens still live at `:root` in `style.css` (the un-built file); the build compiles that file as-is. Theme version bump rule (`VB_VERSION` ↔ `Version:` header) still applies. |
| Development Workflow | **Pass** | File ownership preserved: server-side in PHP, client-side in `assets/js/app.js`. Helpers stay in `inc/`. New build manifest file lives in `dist/` (gitignored). |
| Governance | **Pass** | Amendment to Principle V was ratified at v1.1.0 before this plan was written; the plan operates within the amended principle. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

After generating `research.md`, `data-model.md`, `contracts/`, and
`quickstart.md`, the Constitution Check is re-evaluated:

| Principle | Status post-design | Evidence |
|---|---|---|
| I. Server-First Rendering | **Pass** | Design adds zero PHP rendering changes. |
| II. Progressive Enhancement | **Pass** | No new client-side behaviors introduced; existing no-JS paths are unchanged. |
| III. WP Template Hierarchy | **Pass** | No new template files; `functions.php` edits are additive. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | Build is asset-only; no REST surface area touched. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | Loud-failure rule on missing manifest (FR-011) is the key enforcement: the opt-in switch cannot silently fall back to CDN-first when the manifest is unreadable — the contributor either fixes the build or turns the switch off. The Default Enqueue entity (E4) is the unchanged baseline. |
| Technical Constraints | **Pass** | PHP/WordPress/Vue versions untouched. `style.css`'s `:root` tokens flow through the build unchanged (per data-model E3). Theme version bump rule unchanged. |
| Development Workflow | **Pass** | File ownership preserved. `VB_*` / `vb_` / `vb-` naming applies to the new opt-in switch constant. |
| Governance | **Pass** | v1.1.0 amendment preceded this plan; no further amendment needed. |

**Final gate verdict**: PASS — design is fully compliant with the
Constitution as amended to v1.1.0.

## Project Structure

### Documentation (this feature)

```text
specs/002-vite-build-pipeline/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
│   └── manifest.schema.json
├── checklists/
│   └── requirements.md
└── spec.md              # already created by /speckit.specify
```

### Source Code (repository root)

```text
# Additive changes only. Existing theme layout is preserved.

vue-wp-theme/
├── assets/
│   └── js/
│       └── app.js              # source of truth (unchanged as authoring surface)
├── style.css                   # source of truth (unchanged as authoring surface)
├── functions.php               # +opt-in switch + manifest reader
├── header.php                  # unchanged
├── footer.php                  # unchanged
├── index.php                   # unchanged
├── front-page.php              # unchanged
├── single.php                  # unchanged
├── page.php                    # unchanged
├── archive.php                 # unchanged
├── search.php                  # unchanged
├── 404.php                     # unchanged
├── searchform.php              # unchanged
├── comments.php                # unchanged
├── sidebar.php                 # unchanged
├── template-parts/              # unchanged
├── inc/
│   └── template-tags.php        # unchanged
├── languages/                  # unchanged
├── readme.txt                  # +documents the build path
├── package.json                # NEW (build dependencies, scripts)
├── vite.config.js              # NEW (build configuration)
├── .gitignore                  # +dist/, +node_modules/
└── dist/                       # NEW, gitignored (build outputs)
    ├── manifest.json
    ├── assets/
    │   ├── app.[hash].js
    │   ├── app.[hash].js.map
    │   ├── style.[hash].css
    │   └── style.[hash].css.map
```

**Structure Decision**: Single-project layout — the build is added at
the theme root, alongside the existing theme files. No backend /
frontend split (the theme is monolithic by design). No monorepo.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.