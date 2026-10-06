# Tasks: Vite Build Pipeline for Vue Blocks

**Input**: Design documents from `/specs/002-vite-build-pipeline/`
- `plan.md` (required)
- `spec.md` (required; user stories with priorities)
- `data-model.md` (5 entities: Build Configuration, Build Artifacts, Source Files, Default Enqueue, Opt-in Switch)
- `contracts/manifest.schema.json` (interface contract for `dist/manifest.json`)
- `research.md` (10 decisions: R1–R10)
- `quickstart.md` (6 validation scenarios: A–F)

**Tests**: Test tasks are **not** generated for this feature — the
spec does not request TDD; verification flows through the manual
scenarios in `quickstart.md` and through the manifest schema check.

**Organization**: Tasks are grouped by user story to enable
independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

Single-project layout — the theme is monolithic by design, so all
paths are at the theme root or under `inc/`, `assets/`, or `dist/`.
No backend/frontend split.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization for the build tooling. After this
phase, the theme can `npm install` and `npm run build` (but the
opt-in switch is still off).

- [X] T001 Update `.gitignore` to exclude `dist/` and `node_modules/`
- [X] T002 Create `package.json` at theme root with `devDependencies.vite` and `scripts.build` per FR-004
- [X] T003 [P] Create `vite.config.js` at theme root with `assets/js/app.js` and `style.css` entries, `dist/` output, and `build.sourcemap = true` per FR-012

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build configuration and manifest contract — the
**foundation** every user story depends on. Without this phase the
opt-in switch has nothing to read and the build produces no
verifiable artifacts.

**⚠️ CRITICAL**: No user story work can begin until this phase is
complete.

- [X] T004 Configure manifest emission in `vite.config.js` so the emitted `dist/manifest.json` exposes logical aliases `app` and `style` per `contracts/manifest.schema.json`
- [X] T005 Run `npm install && npm run build` and verify `dist/manifest.json` parses against `contracts/manifest.schema.json` (warm build ≤ 30 s per SC-001)

**Checkpoint**: Foundation ready — `npm run build` produces a
schema-valid manifest; user story work can now begin.

---

## Phase 3: User Story 1 - Contributor runs an opt-in production build (Priority: P1) 🎯 MVP

**Goal**: A contributor can flip a single PHP constant and have
`functions.php` redirect the asset enqueue to the bundled artifacts
read from `dist/manifest.json`.

**Independent Test**: Run quickstart Scenario C end-to-end:
1. `npm run build` produces `dist/`.
2. Set `VB_USE_BUNDLED_ASSETS = true` in `functions.php`.
3. Load the site; verify HTML/`<link>`/`<script>` tags reference the
   hashed artifacts from `dist/manifest.json`, not the CDN Vue or
   `assets/js/app.js` paths.

### Implementation for User Story 1

- [X] T006 [US1] Create `inc/build-manifest.php` with three helpers: `vb_read_build_manifest()` (JSON decode + `is_readable` guard), `vb_loud_fail_missing_manifest()` (`trigger_error` with actionable message per FR-011), and `vb_enqueue_bundled_assets()` (reads manifest, enqueues JS + CSS via `wp_enqueue_script`/`wp_enqueue_style`, never enqueues `.map` files per FR-012)
- [X] T007 [US1] Modify `functions.php` to define `VB_USE_BUNDLED_ASSETS` constant (default `false`) and dispatch in `vb_enqueue_assets()` to `vb_enqueue_bundled_assets()` when the switch is true (else keep the existing CDN-first enqueue unchanged) per FR-005
- [X] T008 [US1] Run quickstart Scenario C (opt-in switch redirect) and confirm hashed artifact URLs replace the CDN Vue and un-built `app.js` paths

**Checkpoint**: At this point, User Story 1 is fully functional and
independently testable — the build path works; site owners do not yet
have a documented default.

---

## Phase 4: User Story 2 - Site owners are unaffected (Priority: P1)

**Goal**: A fresh checkout (no `node_modules/`, no `dist/`) yields a
fully working site identical to today's behavior. The opt-in is
off by default and stays off.

**Independent Test**: Run quickstart Scenarios A and D; both must pass.

### Implementation for User Story 2

- [X] T009 [US2] Run quickstart Scenario A (CDN-first regression guard) and confirm the network panel shows the unpkg Vue request + un-built `app.js` request, with **zero** requests under `dist/`
- [X] T010 [P] [US2] Add a "Default behavior is CDN-first" note to `readme.txt` so site owners immediately see the opt-in is not required
- [X] T011 [US2] Run quickstart Scenario D (loud-failure when manifest is missing) and confirm the failure is **not silent** — `trigger_error` fires naming `dist/manifest.json` (depends on T006/T007 implementing the loud-failure helper)

---

## Phase 5: User Story 3 - Source files remain the source of truth (Priority: P2)

**Goal**: Edits to `assets/js/app.js` and `style.css` flow into the
build artifacts; source maps are emitted alongside; CSS custom
properties at `:root` survive the build unchanged.

**Independent Test**: Run quickstart Scenario E plus the source-map
and CSS-token verifications below.

### Implementation for User Story 3

- [X] T012 [US3] Run quickstart Scenario E (source edits flow into build) and confirm both hashes change after the edits
- [X] T013 [P] [US3] Verify source maps are emitted alongside JS and CSS artifacts in `dist/` per FR-012 (no `.map` references in `dist/manifest.json`'s enqueue-target keys)
- [X] T014 [P] [US3] Verify the un-built `style.css`'s `:root` token block is byte-equal in the emitted CSS artifact (token names and values preserved)

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that touch multiple user stories and
document the contributor-facing surface.

- [X] T015 Add `clean` script to `package.json#scripts` (`rm -rf dist`) so contributors can rebuild from scratch
- [X] T016 [P] Update `readme.txt` with full "Building the theme (optional)" section per research R8 (install, build, opt-in switch, gitignore expectations)
- [X] T017 Run quickstart Scenario F (fresh clone with no build) and confirm regression guard passes end-to-end
- [X] T018 Verify FR-008: confirm the theme's runtime (`functions.php`, template files, REST fields) contains no reference to `node_modules` or any hard-coded `dist/` path other than the opt-in switch's manifest file lookup

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (Phase 1) — **BLOCKS** all user stories.
- **User Stories (Phases 3, 4, 5)**: All depend on Foundational completion.
  - US1 (P1) first — establishes the opt-in switch that US2 verifies.
  - US2 (P1) depends on US1 (must verify a switch exists and is off).
  - US3 (P2) depends on US1 (the build artifacts must exist).
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational (Phase 2). No dependencies on other stories.
- **US2 (P1)**: Can start after US1 completes. Verification, not implementation.
- **US3 (P2)**: Can start after US1 completes. Verification of source-flow invariants.

### Within Each User Story

- Implementation tasks MUST run in dependency order (helpers before consumer).
- Verification tasks (Scenario A/C/D/E/F) MUST run after the implementation they verify.
- Each user story is complete and independently testable before moving to the next priority.

### Parallel Opportunities

- **Phase 1**: T002 and T003 can run in parallel (different files); T001 must precede T002 (gitignore should be in place before `dist/` is ever created).
- **Phase 2**: Sequential (T004 configures; T005 verifies).
- **Phase 3**: Sequential within `functions.php` (T006 creates the helper file; T007 includes it from `functions.php`; T008 verifies).
- **Phase 4**: T010 can run in parallel with T009 (different files); T011 depends on T006/T007.
- **Phase 5**: T013 and T014 can run in parallel with T012 (different files).
- **Phase 6**: T016 can run in parallel with T015, T017, T018 (different files).

---

## Parallel Example: User Story 1

```bash
# Sequential within US1 (same file dependencies):
Task: "T006 [US1] Create inc/build-manifest.php with three helpers"
Task: "T007 [US1] Modify functions.php to define VB_USE_BUNDLED_ASSETS and dispatch"
Task: "T008 [US1] Run quickstart Scenario C (opt-in switch redirect)"
```

## Parallel Example: User Stories 2 and 3 (after US1 done)

```bash
# T009 (regression test) and T010 (readme note) can run in parallel:
Task: "T009 [US2] Run quickstart Scenario A"
Task: "T010 [P] [US2] Add CDN-first note to readme.txt"

# T011, T012, T013, T014 are mostly independent verifications:
Task: "T011 [US2] Run quickstart Scenario D"
Task: "T012 [US3] Run quickstart Scenario E"
Task: "T013 [P] [US3] Verify source maps per FR-012"
Task: "T014 [P] [US3] Verify :root token block survives build"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Run quickstart Scenario C end-to-end.
5. Demo: a contributor can `npm install && npm run build`, flip
   `VB_USE_BUNDLED_ASSETS = true`, and see hashed filenames in the
   enqueue.

### Incremental Delivery

1. Setup + Foundational → Build pipeline is runnable; no opt-in
   switch yet.
2. US1 → Opt-in switch works; CDN-first still the default.
3. US2 → Regression guard runs; documentation note added.
4. US3 → Source-flow invariants verified; source-map and CSS-token
   checks pass.
5. Polish → `readme.txt` final; `clean` script added; FR-008
   verified end-to-end.

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together.
2. Once Foundational is done:
   - Developer A: US1 (opt-in switch + helpers).
   - Developer B: US2 regression tests + readme note (waits on US1).
   - Developer C: US3 source-flow verifications (waits on US1).
3. Polish phase is shared work; small enough for one developer.

---

## Notes

- [P] tasks touch different files with no read/write conflicts.
- [Story] labels map tasks to specific user stories for traceability.
- Each user story is independently completable and testable.
- Verification flows through the manual scenarios in `quickstart.md`
  — no dedicated test suites are required by this spec.
- Commit after each task or logical group (e.g., after each user
  story phase).
- Stop at any checkpoint to validate the story independently
  before moving on.
- Avoid: vague tasks, same-file conflicts marked `[P]`, cross-story
  dependencies that break independence.