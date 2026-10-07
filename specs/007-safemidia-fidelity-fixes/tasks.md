# Tasks: Safe Mídia Visual Fidelity Fixes

**Input**: Design documents from `/specs/007-safemidia-fidelity-fixes/`
- `plan.md` (required)
- `spec.md` (required; 3 user stories — US1/US2/US3 P1)
- `data-model.md` (2 entities: Para o Segurado Section Header, Footer Brand String)
- `research.md` (5 decisions: R1–R5)
- `quickstart.md` (4 validation scenarios: A–D)

**Tests**: The Playwright e2e suite IS the test surface for this
feature (US3). The new specs are captured as implementation
deliverables.

**Organization**: Tasks are grouped by user story to enable
independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US3)
- Include exact file paths in descriptions

## Path Conventions

Single project. Theme runtime files live under `theme/`; new
Playwright specs live at `tests/e2e/`. No scaffolding required
(Syntax `.gitignore`, `.dockerignore`, `playwright.config.ts` are
already in place from feature 006).

## Phase 1: User Story 1 - "Para o Segurado" section renders the Safe Mídia structure (Priority: P1) 🎯 MVP

**Goal**: The "Para o Segurado" template part emits a `.segurado-hd`
wrapper containing the heading, the Safe Mídia subtitle, and a
"Ver todos" link — matching the Safe Mídia reference HTML.

**Independent test**: `grep -q 'class="segurado-hd"' /tmp/h.html`
confirms the wrapper is present; the Playwright
`segurado-markup.spec.ts` exits 0.

### Implementation for User Story 1

- [X] T001 [US1] Edit `theme/template-parts/home/segurado.php`: add a `<div class="segurado-hd">` block above the existing `.segurado-grid` block, containing (in order) `<h2 class="segurado-title">Para o Segurado</h2>`, `<span class="segurado-sub">Direitos, dicas e orientações para quem já tem ou quer contratar um seguro</span>`, and `<a class="segurado-btn-all" href="<archive-url>">Ver todos &rarr;</a>` per research R2 (link target: category archive if `segurado` exists, else posts archive fallback) (per FR-001..FR-003)

---

## Phase 2: User Story 2 - Footer copyright brand is "Safe Mídia", decoupled from Site title (Priority: P1)

**Goal**: The footer-bottom span reads "© YYYY Safe Mídia"
regardless of the Customizer's Site title; the brand is
hardcoded.

**Independent test**: With Customizer Site title mutated to
"Anything Here", `footer-bottom` STILL contains "Safe Mídia".

### Implementation for User Story 2

- [X] T002 [US2] Edit `theme/template-parts/footer/site-footer.php`: replace the `esc_html( $vb_site_title )` echo in the `footer-bottom` span with the literal string `'Safe Mídia'` (per FR-004 / FR-005)

---

## Phase 3: User Story 3 - E2E suite covers the fidelity fixes (Priority: P1)

**Goal**: New Playwright spec asserts the "Para o Segurado"
markup; the existing `title-decoupling.spec.ts` is extended with a
footer-brand assertion.

**Independent test**: `npm run test:e2e` exits 0 with the new
spec(s) included.

### Implementation for User Story 3

- [X] T003 [US3] Create `tests/e2e/segurado-markup.spec.ts`: a Playwright test (`chromium`) that loads `/`, asserts `.segurado-hd` is present AND contains `.segurado-title` reading "Para o Segurado" AND `.segurado-sub` containing the Safe Mídia subtitle AND `.segurado-btn-all` with a non-`#` href (per SC-001)
- [X] T004 [US3] Extend `tests/e2e/title-decoupling.spec.ts`: after the existing `blogname` mutation assertion (`.logo-text` remains "Safe Mídia"), add an additional assertion that the `.footer-bottom` textContent contains "Safe Mídia" both before and after the mutation (per SC-002)

---

## Phase 4: Polish & Cross-Cutting Concerns

**Purpose**: Run the quickstart scenarios end-to-end, verify the
packaged theme is unchanged, commit, and push.

- [X] T005 [P] Run quickstart Scenarios A–D (per `quickstart.md`): grep for `.segurado-hd` markup; mutate blogname via the shim and confirm `footer-bottom` retains "Safe Mídia"; `npm run test:e2e` exits 0; sha256sum of `theme/style.css`'s `:root` block and `DESIGN.md` are unchanged (per SC-004)
- [X] T006 Run `bash theme/package.sh`; confirm `vue-blocks-1.0.0.zip` is ≤ 5 MB and free of test-harness paths (per SC-005-style from features 005/006; the new spec file lives at the repo root, NOT in `theme/`)
- [X] T007 Git commit with a `feat(007)` message and `git push origin main` to publish the two template-part edits and the new spec

---

## Dependencies & Execution Order

### Phase Dependencies

- **US1 (Phase 1)**: No dependency — independent edits on different
  parts of the theme.
- **US2 (Phase 2)**: No dependency — independent edits.
- **US3 (Phase 3)**: T003 is independent; both specs can run in
  parallel after Phase 1 or 2.
- **Polish (Phase 4)**: Depends on Phases 1, 2, 3 being complete.

### User Story Dependencies

- **US1 (P1, MVP)**: Can start immediately; no dependencies.
- **US2 (P1)**: Can start immediately; no dependencies.
- **US3 (P1)**: T003 starts immediately. T004 extends an existing
  file (the title-decoupling test is already in `tests/e2e/`).

### Parallel Opportunities

- **US1 + US2 + US3** (T001, T002, T003): different files; can
  run in parallel after Phase 0.
- **T003 + T004** (US3): different files; can run in parallel.
- **T005 + T006** (Polish): different verification steps.

---

## Parallel Example: US1 + US2 + US3 (after Phase 0)

```bash
# US1 — template-part markup
Task: "T001 [US1] Edit theme/template-parts/home/segurado.php"

# US2 — footer brand
Task: "T002 [US2] Edit theme/template-parts/footer/site-footer.php"

# US3 — Playwright specs
Task: "T003 [US3] Create tests/e2e/segurado-markup.spec.ts"
Task: "T004 [US3] Extend tests/e2e/title-decoupling.spec.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. Complete US1 (T001) — add the `.segurado-hd` wrapper.
2. **STOP and VALIDATE**: Run quickstart Scenario A (grep + Playwright).
3. Demo: the "Para o Segurado" section now has the Safe Mídia structure.

### Full Feature

1. Setup → Foundational (no scaffolding needed).
2. US1 → US2 → US3 (can be parallelized; small changes).
3. Polish → commit → push.

---

## Notes

- [P] tasks touch different files with no read/write conflicts.
- [Story] labels map tasks to specific user stories for traceability.
- Each user story is independently completable and testable.
- The CSS classes already exist in `theme/style.css`; no CSS
  changes are part of this feature (per FR-007 / SC-004).
- Commit after each user story phase (so each story's PR is reviewable
  in isolation).
- Stop at any checkpoint to validate the story independently before
  moving on.
- Avoid: vague tasks, same-file conflicts marked `[P]`, cross-story
  dependencies that break independence.