# Tasks: Home-Page Responsive Polish & Playwright E2E

**Input**: Design documents from `/specs/006-responsive-polish-and-e2e/`
- `plan.md` (required)
- `spec.md` (required; 3 user stories — US1/US2/US3 P1)
- `data-model.md` (2 entities: Synthetic Long-Title Post, Playwright Test Fixture)
- `contracts/e2e-suite.contract.md` (Playwright config + fixtures + test files contract)
- `research.md` (8 decisions: R1–R8)
- `quickstart.md` (6 validation scenarios: A–F)

**Tests**: The Playwright e2e suite IS the test surface for this
feature (per FR-010 / FR-011); it is captured as the implementation
deliverable rather than as separate "test" tasks, in line with the
spec's test-as-code scope.

**Organization**: Tasks are grouped by user story to enable
independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US3)
- Include exact file paths in descriptions

## Path Conventions

Single project. Theme runtime files live under `theme/`; new dev-only
test scaffolding lives at the repo root in `tests/e2e/` and
`playwright.config.ts`. CSS overrides go inside `theme/style.css`.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Lay down the directory tree for the e2e test suite so
the user-story implementation phases can drop files into it.

- [X] T001 [P] Create `tests/e2e/` directory at the repo root (mkdir -p; no other files inside)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Install Playwright, write the config, and ship the
shared fixture module. The remaining user stories depend on this.

**⚠️ CRITICAL**: No user story work can begin until this phase is done.

- [X] T002 Update `package.json` at the repo root: add `@playwright/test` to `devDependencies` (^1.48.0) and add `scripts.test:e2e = "playwright test"` per `contracts/e2e-suite.contract.md`
- [X] T003 [P] Create `playwright.config.ts` at the repo root per `contracts/e2e-suite.contract.md` (testDir `./tests/e2e`, `baseURL` from `process.env.PW_URL ?? 'http://localhost:8080'`, `headless: !process.env.PWDEBUG`, single Chromium project)
- [X] T004 [P] Create `tests/e2e/fixtures.ts` per `contracts/e2e-suite.contract.md` exporting `expectDockerHarnessUp`, `createSyntheticLongTitlePost`, `deleteSyntheticLongTitlePost` (the synthetic-post helper uses WP REST `POST /wp-json/wp/v2/posts` with admin nonce; teardown uses `DELETE`)
- [X] T005 Run `npm install && npx playwright install chromium` to verify the dependency + Chromium binary download succeed (fast sanity check; CI installs the same)

---

## Phase 3: User Story 1 - Home sections reflow across the Safe Mídia breakpoints (Priority: P1) 🎯 MVP

**Goal**: Every home section reflows without horizontal overflow at
the five test viewports; long post titles truncate cleanly.

**Independent Test**: `npm run test:e2e -- no-overflow.spec.ts`
exits 0 and `npm run test:e2e -- long-title.spec.ts` exits 0.

### Implementation for User Story 1

- [X] T006 [US1] Append a single `@media (max-width: 1024px) { ... } @media (max-width: 768px) { ... } @media (max-width: 420px) { ... }` block to `theme/style.css` (after the existing Safe Mídia section styles) covering FR-001..FR-012: navbar nav-links hidden + hamburger visible, news grid 3-col → 2-col → 2-col → 1-col, Para o Segurado 4-col → 2-col → 1-col, Mais Lidas 2-col → 1-col with no overflow, Boletim / Colunistas / newsletter / footer all stacking correctly per the Safe Mídia HTML's mobile breakpoints
- [X] T007 [US1] Apply `text-overflow: ellipsis; overflow: hidden; white-space: nowrap;` to `.ncard-title`, `.analise-main-title`, `.scard-title`, `.col-article-title`, `.tab-card-title`, `.boletim-text`, `.aitem-title`, `.related-card-title`, and the newsletter-grande `h1` (per FR-013 / research R2)
- [X] T008 [US1] Create `tests/e2e/no-overflow.spec.ts` per `contracts/e2e-suite.contract.md`: iterates the 5 viewports (1280, 1024, 768, 414, 375 × width) × 12 sections; asserts `document.documentElement.scrollWidth === document.documentElement.clientWidth` on each (per US1 / SC-001)

**Checkpoint**: At this point, US1 is fully functional — the home
page reflows cleanly at all breakpoints; long titles truncate.

---

## Phase 4: User Story 2 - Navbar brand is "Safe Mídia", decoupled from Customizer Site title (Priority: P1)

**Goal**: The navbar brand reads "Safe Mídia" regardless of the
Customizer's Site title; the Customizer's Site title drives only the
document `<title>` tag and `bloginfo('name')`.

**Independent Test**: `npm run test:e2e -- navbar-brand.spec.ts` and
`title-decoupling.spec.ts` both exit 0.

### Implementation for User Story 2

- [X] T009 [US2] Edit `theme/template-parts/header/navbar.php`: change the `<span class="logo-text">Vue <span>Blocks</span></span>` markup to `<span class="logo-text">Safe <span>Mídia</span></span>` (per FR-008 / research R3)
- [X] T010 [P] [US2] Create `tests/e2e/navbar-brand.spec.ts` per `contracts/e2e-suite.contract.md`: loads `/`, reads `.logo-text`'s `textContent`, asserts it's exactly "Safe Mídia" (per US2 acceptance 1)
- [X] T011 [P] [US2] Create `tests/e2e/title-decoupling.spec.ts` per `contracts/e2e-suite.contract.md`: defaults to `<title>Safe Mídia`; mutates `blogname` to "Anything Here"; reloads `/`; asserts `<title>` is "Anything Here" AND `.logo-text` is still "Safe Mídia" (per US2 acceptance 2 / FR-009)

**Checkpoint**: At this point, US2 is fully functional — the brand
text is hardcoded to "Safe Mídia"; the Customizer's Site title
controls only the document `<title>`.

---

## Phase 5: User Story 3 - Playwright e2e suite covers spacing, overflow, breakpoints (Priority: P1)

**Goal**: A long-title Playwright spec verifies the synthetic-post
truncation assertion end-to-end; the suite catches future
regressions at all three breakpoints.

**Independent Test**: `npm run test:e2e` exits 0; the long-title
test creates a synthetic draft post, queries the home page, and
deletes the post in teardown.

### Implementation for User Story 3

- [X] T012 [US3] Create `tests/e2e/long-title.spec.ts` per `contracts/e2e-suite.contract.md`: in `beforeEach`, calls `createSyntheticLongTitlePost` (100 × `'A'` title); loads `/`; asserts the synthetic post's title element's `offsetHeight` is non-zero (placeholder rendered, not truncated to 0); in `afterEach`, calls `deleteSyntheticLongTitlePost` with the captured ID (per US3 acceptance 4 / FR-013's referenced check)

**Checkpoint**: At this point, US3 is fully functional — the
suite catches responsive regressions and the navbar / `<title>`
distinction.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Run the quickstart validation scenarios end-to-end,
verify the packaged theme is unchanged, commit, and push.

- [X] T013 [P] Run quickstart Scenarios A–F (per `quickstart.md`): breakpoint reflow curl + `no-overflow.spec.ts`; navbar brand `grep` + `navbar-brand.spec.ts`; `<title>`/Site-title decoupling `curl + title-decoupling.spec.ts`; long-title `long-title.spec.ts`; theme-name preservation `grep`; `:root` byte-identical sha256sum check (FR-013 / SC-005)
- [X] T014 Run `bash theme/package.sh`; verify `vue-blocks-1.0.0.zip` is ≤ 5 MB and free of test-harness paths per SC-005 (Playwright files live at the repo root, NOT in `theme/`)
- [X] T015 Git commit with a `feat(006)` message and `git push origin main` to publish the new CSS overrides, navbar brand text, Playwright config, fixtures, and four test specs

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Depends on Setup (T001).
- **US1 (Phase 3)**: Depends on Foundational (CSS can be edited without Playwright; tests need the config).
- **US2 (Phase 4)**: Depends on Foundational (the test files need the fixture).
- **US3 (Phase 5)**: Depends on Foundational (the long-title spec needs the fixture).
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1, MVP)**: Can start after Phase 2 — independent of US2 / US3.
- **US2 (P1)**: Can start after Phase 2 — independent of US1 / US3.
- **US3 (P1)**: Can start after Phase 2 — independent of US1 / US2.

### Within Each User Story

- US1: T006 (CSS) and T008 (test) touch different files; can run in parallel after Phase 2. T007 (long-title CSS) is part of T006.
- US2: T009 (PHP) and T010–T011 (tests) touch different files; can run in parallel.
- US3: standalone.

---

## Parallel Example: US1 + US2 + US3 (after Phase 2)

```bash
# US1 — CSS overrides + no-overflow spec:
Task: "T006 [US1] Append breakpoint media queries to theme/style.css"
Task: "T007 [US1] Add ellipsis long-title styles"
Task: "T008 [US1] Create tests/e2e/no-overflow.spec.ts"

# US2 — navbar brand text + 2 specs:
Task: "T009 [US2] Edit theme/template-parts/header/navbar.php brand text"
Task: "T010 [P] [US2] Create tests/e2e/navbar-brand.spec.ts"
Task: "T011 [P] [US2] Create tests/e2e/title-decoupling.spec.ts"

# US3 — long-title spec:
Task: "T012 [US3] Create tests/e2e/long-title.spec.ts"
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. Complete Phase 1 (T001).
2. Complete Phase 2 (T002–T005).
4. Complete US1 (T006–T008) — CSS overrides + the no-overflow spec.
5. **STOP and VALIDATE**: Run quickstart Scenarios A (breakpoint curl loop) + the no-overflow Playwright spec.
6. Demo: the home page renders without horizontal scrollbar at all five test viewports.

### Incremental Delivery

1. Setup → Foundational → US1 (responsive polish) → MVP demo.
2. US2 (navbar brand) → verify quickstart B + C.
3. US3 (long-title spec) → verify quickstart D.
4. Polish → quickstart Scenarios E (theme-name) + F (tokens byte-identical) + package size + commit + push.

---

## Notes

- [P] tasks touch different files with no read/write conflicts.
- [Story] labels map tasks to specific user stories for traceability.
- Each user story is independently completable and testable.
- The Playwright suite IS the test surface for this feature — no
  separate "test" tasks beyond the four spec files (T008, T010, T011,
  T012).
- Commit after each user story phase (so each story's PR is reviewable
  in isolation).
- Stop at any checkpoint to validate the story independently before
  moving on.
- Avoid: vague tasks, same-file conflicts marked `[P]`, cross-story
  dependencies that break independence.