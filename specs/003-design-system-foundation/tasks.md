# Tasks: Design System & Theme Package Foundation

**Input**: Design documents from `/specs/003-design-system-foundation/`
- `plan.md` (required)
- `spec.md` (required; 3 user stories — US1/US2 P1, US3 P2)
- `data-model.md` (5 entities: Design Token, Component, Section, Theme Package, Test Harness)
- `contracts/design-tokens.schema.md` (22 design tokens transcribed from Safe Mídia)
- `contracts/theme-package.manifest.md` (zip contents contract)
- `research.md` (10 decisions: R1–R10)
- `quickstart.md` (6 validation scenarios: A–F)

**Tests**: Test tasks are **not** generated for this feature — the
spec does not request TDD; verification flows through the manual
scenarios in `quickstart.md`.

**Organization**: Tasks are grouped by user story to enable
independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US3)
- Include exact file paths in descriptions

## Path Conventions

Single project. Theme runtime files live at the repo root in
`theme/`; the test harness and `DESIGN.md` live at the repo root.
The packaged deliverable is `theme/` zipped into a single archive.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Lay down the new directory tree and the documentation
skeleton before any file moves or rewrites happen.

- [X] T001 [P] Create `theme/` directory structure with empty subdirectories: `theme/assets/js/`, `theme/template-parts/header/`, `theme/template-parts/hero/`, `theme/template-parts/news/`, `theme/template-parts/footer/`, `theme/inc/`, `theme/languages/`
- [X] T002 [P] Create `DESIGN.md` skeleton at the repo root with five empty sections: `## Design tokens`, `## Components`, `## Layout patterns`, `## Responsive breakpoints`, `## Maintenance`
- [X] T003 [P] Create `theme/PACKAGE.md` describing the packaged deliverable (one-paragraph purpose + scope link to `contracts/theme-package.manifest.md`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Physically move the theme files and update the harness
so every subsequent user story can build on top of the new layout.
No user story work can begin until this phase is complete.

**⚠️ CRITICAL**: This phase touches the theme's runtime location.
It MUST be done atomically — partial moves break the Docker harness
mid-development.

- [X] T004 Move existing theme files from the repo root into `theme/` (filesystem move): `style.css`, `functions.php`, `index.php`, `front-page.php`, `header.php`, `footer.php`, `sidebar.php`, `comments.php`, `search.php`, `searchform.php`, `404.php`, `page.php`, `archive.php`, `single.php`, `screenshot.png`, `readme.txt`, `assets/`, `template-parts/`, `inc/`, `languages/`. After the move, verify the repo root has no stray theme files left behind.
- [X] T005 Update `docker-compose.yml` bind-mount path so the harness continues to mount the theme into `wp-content/themes/vue-blocks` (replace the existing path with `./theme:/var/www/html/wp-content/themes/vue-blocks`)
- [X] T006 Update `.gitignore` if any new packaging artifacts need to be excluded (e.g., `*.zip` at the repo root)

---

## Phase 3: User Story 1 - DESIGN.md is the single source of truth (Priority: P1)

**Goal**: `DESIGN.md` documents the theme's design tokens, components,
layout patterns, responsive breakpoints, and maintenance workflow.

**Independent Test**: Open `DESIGN.md` at the repo root and confirm
each of the five sections is present, the token catalog lists at least
20 tokens, and the components section documents at least 8 components
matching the Safe Mídia reference's vocabulary.

### Implementation for User Story 1

- [X] T007 [US1] Fill `## Design tokens` section of `DESIGN.md` with the catalog from `contracts/design-tokens.schema.md` (13 colors + 2 typography + 2 spacing + 3 radii + 2 shadows = 22 tokens; one row per token with name, value, and usage note)
- [X] T008 [US1] Fill `## Components` section of `DESIGN.md` per `data-model.md` E2 (8 components: navbar, hero-main, hero-aside, news-card, site-footer, logo, button, social — each with name, purpose, when-to-use, when-not, consumed tokens, and the `template-parts/` path that implements it)
- [X] T009 [US1] Fill `## Layout patterns`, `## Responsive breakpoints`, and `## Maintenance` sections of `DESIGN.md` (layout = the four core home sections in order; breakpoints = tablet ≤1024, mobile ≤768, small-mobile ≤420; maintenance = the canonical-repo-vs-in-theme-vs-style.css sync rule)
- [X] T010 [US1] Generate `theme/DESIGN.md` from the repo-root `DESIGN.md` (initial sync; the `theme/package.sh` script will keep them in sync going forward per R7)

**Checkpoint**: At this point, US1 is fully functional — a contributor
opening the repo finds `DESIGN.md` and has the complete design reference.

---

## Phase 4: User Story 2 - The theme packages into a zip (Priority: P1) 🎯 MVP

**Goal**: A contributor can run the documented packaging command and
produce a clean `.zip` that installs into any WordPress site without
test-harness paths.

**Independent Test**: Run `bash theme/package.sh`; verify the produced
zip is ≤ 5 MB; run `unzip -l | grep -E '(node_modules|dist|docker-compose|bin/bootstrap|specs/|package\.sh|PACKAGE\.md)'` and confirm no matches.

### Implementation for User Story 2

- [X] T011 [US2] Create `theme/package.sh` per `contracts/theme-package.manifest.md`: shell script that (a) refreshes `theme/DESIGN.md` from the repo-root copy, (b) runs `zip -r ../vue-blocks-1.0.0.zip . -x` patterns matching the excluded paths list, (c) runs the post-build leakage grep and exits non-zero on a hit
- [X] T012 [US2] Run `bash theme/package.sh` from the repo root, verify the produced zip is ≤ 5 MB (SC-002), and confirm `unzip -l vue-blocks-1.0.0.zip | grep -E '(node_modules|dist|docker-compose|bin/bootstrap|specs/|package\.sh|PACKAGE\.md)'` returns no matches (SC-006)

**Checkpoint**: At this point, US2 is fully functional — the theme
deliverable is a working `.zip` that can ship to any WordPress host.

---

## Phase 5: User Story 3 - Home page renders the Safe Mídia visual structure (Priority: P2)

**Goal**: With the theme active and seeded content, the home page
matches the Safe Mídia reference's four core sections (navbar, hero
with sidebar, news grid, footer) in order and visual hierarchy.

**Independent Test**: With at least 6 seeded published posts and a
configured primary menu, load the homepage and visually compare to
the Safe Mídia reference; confirm the four core sections render in
the documented order.

### Implementation for User Story 3

- [X] T013 [US3] Re-tokenize `theme/style.css`'s `:root` block with the Safe Mídia palette from `contracts/design-tokens.schema.md` (replace the existing Vue Blocks tokens; preserve the WordPress theme header comment at the top of the file unchanged)
- [X] T014 [US3] Create `theme/template-parts/header/navbar.php` per the Safe Mídia navbar structure (logo + tag-line, primary nav links, socials, hamburger toggle, subscribe CTA button)
- [X] T015 [US3] Create `theme/template-parts/hero/hero.php` with featured sticky-post (else most-recent) on the left and a 4-post sidebar on the right (sourcing content via `WP_Query` per research R3)
- [X] T016 [US3] Create `theme/template-parts/news/news-grid.php` (3-column grid wrapper, 6 latest posts after the hero + sidebar)
- [X] T017 [US3] Create `theme/template-parts/news/news-card.php` (single news card markup matching the Safe Mídia `.ncard` pattern: image, category chip, link wrapper)
- [X] T018 [US3] Create `theme/template-parts/footer/site-footer.php` (logo + tagline, columns of links, newsletter form, copyright bottom bar)
- [X] T019 [US3] Rewrite `theme/front-page.php` to compose the four core sections in order via `get_template_part()`: navbar (via header), hero, news grid, footer (via footer)
- [X] T020 [US3] Add `theme/inc/nav-walker.php` with a `Walker_Nav_Menu` subclass that emits the Safe Mídia navbar's expected `<a class="active">` markup and wraps each item with the documented CSS class names

**Checkpoint**: At this point, US3 is fully functional — the home
page renders the four core sections matching the Safe Mídia reference.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Documentation that surfaces DESIGN.md and the packaging
workflow to contributors; run the quickstart validation scenarios.

- [X] T021 [P] Update repo-root `README.md` with two new sections: a one-paragraph pointer to `DESIGN.md` at the top, and a "Packaging" subsection that documents `bash theme/package.sh` and where the produced zip lands
- [X] T022 [P] Update `theme/readme.txt` with the new packaging workflow (one short subsection under "Building the theme (optional)" that points to `theme/package.sh` and the contracts)
- [X] T023 Run quickstart Scenario A (open `DESIGN.md`, confirm the documented shape: 5 sections, ≥ 20 tokens, ≥ 8 components, ≥ 2 breakpoints)
- [X] T024 Run quickstart Scenario B (`docker compose down -v && docker compose up -d`; confirm the bootstrap reaches "Vue Blocks bootstrap complete." and the home page renders the Safe Mídia visual language; per SC-005 the harness keeps working)
- [X] T025 Run quickstart Scenario D (with seeded posts + primary menu, load the homepage; confirm the four core sections render in the documented order; per FR-007 the seven non-core sections must not appear)
- [X] T026 Run quickstart Scenario F (re-run the leakage grep on the produced zip; confirm the grep stays clean after packaging)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (Phase 1) — **BLOCKS** all user stories. Without this phase, the theme files are unreachable mid-scaffolding.
- **User Stories (Phases 3, 4, 5)**: All depend on Foundational completion.
  - US1 (P1): can start immediately after Phase 2.
  - US2 (P1): can start in parallel with US1; both modify different files.
  - US3 (P2): depends on US1 (the token catalog feeds the style.css re-tokenization) — sequential after US1.
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1)**: Can start after Phase 2. No dependencies on other stories. Produces `DESIGN.md` and the in-theme `DESIGN.md` duplicate.
- **US2 (P1)**: Can start after Phase 2 (or in parallel with US1; US2's `package.sh` can be authored before the in-theme `DESIGN.md` duplicate exists, then refreshed later).
- **US3 (P2)**: Can start after US1 (the token catalog from `DESIGN.md` is the source for the style.css re-tokenization in T013). Depends on US1's T007–T010.

### Within Each User Story

- Implementation tasks MUST run in dependency order (helpers before consumers).
- Verification tasks MUST run after the implementation they verify.
- Each user story is complete and independently testable before moving to the next priority.

### Parallel Opportunities

- **Phase 1**: T001, T002, T003 — different files; all parallel.
- **Phase 3 vs Phase 4**: US1's `DESIGN.md` work and US2's `package.sh` work can run in parallel after Phase 2 (different files; US2 references the token catalog from `contracts/` which doesn't change with US1's edits).
- **Phase 6**: T021, T022, T026 can run in parallel; T023, T024, T025 depend on the prior phases.

---

## Parallel Example: User Story 1

```bash
# T007, T008, T009 all edit DESIGN.md but at different sections — run
# them sequentially to avoid merge conflicts on the same file.
Task: "T007 [US1] Fill ## Design tokens section of DESIGN.md"
Task: "T008 [US1] Fill ## Components section of DESIGN.md"
Task: "T009 [US1] Fill Layout, Breakpoints, and Maintenance sections"

# T010 (generate theme/DESIGN.md) depends on T007–T009.
```

## Parallel Example: User Story 2 + Family

```bash
# US1 + US2 can both run after Phase 2 in parallel:
Task: "T007-T010 [US1] DESIGN.md sections + in-theme copy"
Task: "T011 [US2] theme/package.sh with zip exclusions + leakage grep"

# T012 (run package.sh) depends on T010 (theme/DESIGN.md must exist) + T011.
```

---

## Implementation Strategy

### MVP First (User Stories 1 + 2 — both P1)

1. Complete Phase 1: Setup (3 files in parallel)
2. Complete Phase 2: Foundational (move files + bind-mount fix)
3. Complete US1 (DESIGN.md)
4. Complete US2 (package.sh + run it)
5. **STOP and VALIDATE**: Run quickstart Scenario B (harness still works after Phase 2) and Scenario F (zip leakage grep is clean).
6. Demo: the theme ships as a clean `.zip` AND the documentation is complete.

### Incremental Delivery

1. Setup + Foundational → repo reorganized; harness still boots.
2. US1 → DESIGN.md complete; `theme/DESIGN.md` synchronized.
3. US2 → packaging script ready; first zip produced.
4. US3 → home page renders the four core Safe Mídia sections.
5. Polish → docs refreshed; quickstart scenarios green.

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together (the file moves must be atomic; one developer).
2. Once Foundational is done:
   - Developer A: US1 (DESIGN.md sections).
   - Developer B: US2 (package.sh script).
   - Developer C: US3 (home page template parts + style.css re-tokenization).
3. US3 needs the token catalog from US1; if Developer C starts before US1 lands, they can scaffold the template parts using placeholder tokens and replace them after T007 lands.
4. Polish is small; one developer.

---

## Notes

- [P] tasks touch different files with no read/write conflicts.
- [Story] labels map tasks to specific user stories for traceability.
- Each user story is independently completable and testable.
- Verification flows through the manual scenarios in `quickstart.md`.
- Commit after each task or logical group (e.g., after each user story phase).
- Stop at any checkpoint to validate the story independently before moving on.
- Avoid: vague tasks, same-file conflicts marked `[P]`, cross-story dependencies that break independence.