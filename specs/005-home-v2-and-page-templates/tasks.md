# Tasks: Home-Page Sections v2 & Page Templates

**Input**: Design documents from `/specs/005-home-v2-and-page-templates/`
- `plan.md` (required)
- `spec.md` (required; 4 user stories — US1/US2 P1, US3/US4 P2)
- `data-model.md` (4 entities: Newsletter Subscription deferred, Colunista Card derived, Related Post derived, Page Template entities)
- `contracts/home-sections.contract.md` (8 sections × data source × empty state × CSS class names)
- `contracts/single-post.contract.md` (full editorial layout per Q2)
- `contracts/colunistas.contract.md` (auto-create per Q3)
- `research.md` (9 decisions: R1–R9)
- `quickstart.md` (5 validation scenarios: A–E)

**Tests**: Test tasks are **not** generated for this feature — the
spec does not request TDD; verification flows through the manual
scenarios in `quickstart.md`.

**Organization**: Tasks are grouped by user story to enable
independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1–US4)
- Include exact file paths in descriptions

## Path Conventions

Single project. New template parts live under
`theme/template-parts/home/`; refinements to existing page templates
are in-place edits inside `theme/`. The Docker harness at the repo
root is unchanged.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Lay down the new `template-parts/home/` directory tree
so the user-story implementation phases can drop files into it.

- [ ] T001 [P] Create `theme/template-parts/home/` directory at the repo root (mkdir -p; no other files inside)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add the Colunistas category auto-create (per FR-004a and
Clarification Q3) and the helper that the Colunistas template part
will need. No user story work can begin until this phase is done.

**⚠️ CRITICAL**: T003 is required by the Colunistas section in US1.

- [ ] T002 Add `theme/functions.php` `vb_register_colunistas_category()` callback registered on the `after_setup_theme` hook per `contracts/colunistas.contract.md` (checks `category_exists('colunistas')`, calls `wp_create_category('Colunistas')` if missing; idempotent)
- [ ] T003 [P] Add `vb_get_colunista_cards(int $limit = 3): array` helper to `theme/inc/template-tags.php` (returns up to N cards per the data-model E2 — distinct authors in the "colunistas" category with their latest post; empty array when no posts exist)

---

## Phase 3: User Story 1 - Full Safe Mídia home page renders all sections (Priority: P1) 🎯 MVP

**Goal**: With seeded content, the home page renders all 12 Safe
Mídia sections (4 v1 + 8 deferred) in the documented order; per
Clarification Q1, all 8 deferred sections land in v2.

**Independent Test**: Visit the home page; the rendered HTML
contains each of the eight new section class names
(`.nl-compact-block`, `.segurado-block`, `.nl-block`,
`.analise-layout`, `.tabs-section`, `.maislist-section`,
`.boletim-section`, `.colunistas-section`); the section order
matches `contracts/home-sections.contract.md`.

### Implementation for User Story 1

- [ ] T004 [US1] Create `theme/template-parts/home/newsletter-compact.php` per `contracts/home-sections.contract.md` § 4 (Safe Mídia compact newsletter form, no data source, always rendered)
- [ ] T005 [US1] Create `theme/template-parts/home/segurado.php` per `contracts/home-sections.contract.md` § 5 (4 most-recent posts in `.segurado-block` / `.segurado-grid`; section hidden if 0 posts)
- [ ] T006 [US1] Create `theme/template-parts/home/newsletter-grande.php` per `contracts/home-sections.contract.md` § 6 (large newsletter section with perks list in `.nl-block` / `.nl-perks`)
- [ ] T007 [US1] Create `theme/template-parts/home/analise.php` per `contracts/home-sections.contract.md` § 7 (1 featured + 4-list from category `analise` in `.analise-layout` / `.analise-main` / `.analise-list`)
- [ ] T008 [US1] Create `theme/template-parts/home/category-tabs.php` per `contracts/home-sections.contract.md` § 8 (4 most-populated categories in `.tabs-section` / `.tabs-nav` / `.tabs-grid`; section hidden if fewer than 2 categories exist)
- [ ] T009 [US1] Create `theme/template-parts/home/mais-lidas.php` per `contracts/home-sections.contract.md` § 9 (8 most-recent posts in `.maislist-section` / `.mais-layout` / `.mais-item`)
- [ ] T010 [US1] Create `theme/template-parts/home/boletim.php` per `contracts/home-sections.contract.md` § 10 (posts in category `regulatorio` in `.boletim-section` / `.boletim-item` / `.boletim-tipo`)
- [ ] T011 [US1] Create `theme/template-parts/home/colunistas.php` per `contracts/home-sections.contract.md` § 11 using the `vb_get_colunista_cards()` helper from T003 (renders one card per distinct author in `.colunistas-section` / `.col-grid` / `.col-card`; hidden when empty array)
- [ ] T012 [US1] Update `theme/front-page.php` to compose the 8 new sections between the existing news grid (`template-parts/news/news-grid`) and the existing footer; the section order MUST match `contracts/home-sections.contract.md`

**Checkpoint**: At this point, US1 is fully functional — the home
page renders all 12 Safe Mídia sections.

---

## Phase 4: User Story 2 - Single post page renders with the full Safe Mídia editorial layout (Priority: P1)

**Goal**: A published post's permalink renders with the full
editorial layout (per Clarification Q2): featured-image hero,
Merriweather typography, category chip, byline, body, related-posts
rail.

**Independent Test**: Fetch any published post's permalink; confirm
the markup contains `.post-hero`, `.entry-content`, and
`.related-posts` (per `contracts/single-post.contract.md`).

### Implementation for User Story 2

- [ ] T013 [US2] Refine `theme/template-parts/content.php` to add the Safe Mídia single-post layout per `contracts/single-post.contract.md` (post-hero with featured image, post-meta with category chip + byline, post-title in Merriweather typography, .entry-content body; the archive-loop variant is preserved by checking `is_singular('post')`)
- [ ] T014 [US2] Rewrite `theme/single.php` to compose the refined content from T013 plus a "Leia também" related-posts rail at the bottom (3 most recent posts in the same category, excluding the current post) per `contracts/single-post.contract.md`

**Checkpoint**: At this point, US2 is fully functional — single
posts render with the full Safe Mídia editorial layout.

---

## Phase 5: User Story 3 - Archive, search, and 404 page templates (Priority: P2)

**Goal**: The archive / search / 404 page templates render with the
Safe Mídia visual language.

**Independent Test**: Visit `/category/<slug>/`, `/?s=<query>`, and
a 404 URL; each returns a Safe Mídia-styled page (per SC-003).

### Implementation for User Story 3

- [ ] T015 [P] [US3] Refine `theme/archive.php` to use the Safe Mídia `.ncard` markup (image + category chip + title) for each post; the existing archive loop's `template_part` call continues to use `template-parts/content.php` but the wrapper markup switches to the Safe Mídia news grid
- [ ] T016 [P] [US3] Refine `theme/search.php` to render Safe Mídia search results (news-card markup for results, "no results" message in Safe Mídia typography when zero matches)
- [ ] T017 [P] [US3] Refine `theme/404.php` to render a Safe Mídia-styled "página não encontrada" message (navbar + footer present; message centered with Safe Mídia typography)

**Checkpoint**: At this point, US3 is fully functional — the
remaining non-home page templates are Safe Mídia-consistent.

---

## Phase 6: User Story 4 - Colunistas section as a content category (Priority: P2)

**Goal**: The "Colunistas" section renders columnists sourced from a
"colunistas" WordPress category that is auto-created on activation.

**Independent Test**: With ≥ 3 published posts in "colunistas" by 3
different authors, the home page renders 3 Colunistas cards; with
0 posts, the section is hidden (per FR-004 / SC-004).

### Implementation for User Story 4

- [ ] T018 [US4] Verify the Colunistas category is auto-created and idempotent per FR-004a (via `contracts/colunistas.contract.md`): activate the theme twice; confirm only one "colunistas" category exists in `wp term list category`. Also confirm the section shows the correct number of cards for ≥ 3 seeded posts by distinct authors

**Checkpoint**: At this point, US4 is fully functional — the
Colunistas section is bootstrapped and populates correctly.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Run the quickstart validation scenarios end-to-end,
verify the packaged theme, commit, and push.

- [ ] T019 [P] Run quickstart Scenario A (full home page — 8 new section class names present + section order matches the contract)
- [ ] T020 [P] Run quickstart Scenario B (single post — `.post-hero`, `.entry-content`, `.related-posts` all present)
- [ ] T021 [P] Run quickstart Scenarios C, D, E (Colunistas category auto-create, archive/search/404 rendering, package size and exclusion)
- [ ] T022 Verify `bash theme/package.sh` produces `vue-blocks-1.0.0.zip` ≤ 5 MB and free of test-harness paths per SC-005
- [ ] T023 Git commit with a `feat(005)` message and `git push origin main` to publish the new template parts, refinements, and category registration

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (T001).
- **User Stories (Phases 3, 4, 5)**: All depend on Foundational completion.
  - US1 (Phase 3) — needs the `template-parts/home/` directory (T001) and the Colunistas helper (T003) for T011.
  - US2 (Phase 4) — needs T013 first (refined content.php) before T014.
  - US3 (Phase 5) — independent of US1 / US2; can run in parallel.
- **US4 (Phase 6)**: Depends on US1 (T012 has the colunistas section wired into front-page.php).
- **Polish (Phase 7)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1, MVP)**: Can start after Phase 2 (T003 needed for T011).
- **US2 (P1)**: Can start after Phase 2; independent of US1 / US3.
- **US3 (P2)**: Can start after Phase 2; independent of US1 / US2 / US4.
- **US4 (P2)**: Can start after US1 (T012 wires the section into front-page.php).

### Within Each User Story

- US1: T004–T010 touch different files; can run in parallel after Phase 2. T011 depends on T003 (helper). T012 depends on T004–T011.
- US2: T013 first (refines shared content.php); T014 depends on T013.
- US3: T015, T016, T017 touch different files; can run in parallel after Phase 2.

### Parallel Opportunities

- **Phase 1**: T001 standalone.
- **Phase 2**: T002 and T003 touch different files; can run in parallel.
- **Phase 3 (US1)**: T004, T005, T006, T007, T008, T009, T010 — different files, parallel. T011 after T003. T012 after T004–T011.
- **Phase 4 (US2)**: T013 first; T014 after.
- **Phase 5 (US3)**: T015, T016, T017 — different files, parallel.
- **Phases 3–5**: US1, US2, US3 can run in parallel after Phase 2 (different files).
- **Phase 7**: T019, T020, T021 — different scenarios; can run in parallel.

---

## Parallel Example: User Story 1 (after Phase 2)

```bash
# Eight template-part files can be written in parallel:
Task: "T004 [US1] Create theme/template-parts/home/newsletter-compact.php"
Task: "T005 [US1] Create theme/template-parts/home/segurado.php"
Task: "T006 [US1] Create theme/template-parts/home/newsletter-grande.php"
Task: "T007 [US1] Create theme/template-parts/home/analise.php"
Task: "T008 [US1] Create theme/template-parts/home/category-tabs.php"
Task: "T009 [US1] Create theme/template-parts/home/mais-lidas.php"
Task: "T010 [US1] Create theme/template-parts/home/boletim.php"

# T011 (colunistas.php) follows once T003 (helper) is done:
Task: "T011 [US1] Create theme/template-parts/home/colunistas.php"

# T012 (front-page.php composition) follows once T004–T011 are done:
Task: "T012 [US1] Update theme/front-page.php"
```

## Parallel Example: User Stories 1 + 2 + 3 (after Phase 2)

```bash
# US1 — eight template parts + front-page.php:
Task: "T004–T010 [US1] Create 7 template parts (different files)"
Task: "T011 [US1] Create colunistas.php"
Task: "T012 [US1] Update front-page.php"

# US2 — content.php + single.php (different files):
Task: "T013 [US2] Refine theme/template-parts/content.php"
Task: "T014 [US2] Rewrite theme/single.php"

# US3 — archive.php, search.php, 404.php (different files):
Task: "T015 [US3] Refine theme/archive.php"
Task: "T016 [US3] Refine theme/search.php"
Task: "T017 [US3] Refine theme/404.php"
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. Complete Phase 1 (T001) — single mkdir.
2. Complete Phase 2 (T002 + T003) — Colunistas auto-create + helper.
3. Complete Phase 3 (T004 → T012) — 8 template parts + front-page.php composition.
4. **STOP and VALIDATE**: Run quickstart Scenario A.
5. Demo: the home page now renders all 12 Safe Mídia sections.

### Incremental Delivery

1. Setup → Foundational → US1 (template parts + front-page) → MVP demo.
2. US2 (single post) → verify quickstart B.
3. US3 (page templates) → verify quickstart D.
4. US4 (Colunistas auto-create) → verify quickstart C.
5. Polish → quickstart E (package) → commit + push.

### Parallel Team Strategy

With multiple developers after Phase 2:

- Developer A: US1 (8 template parts + front-page.php).
- Developer B: US2 (content.php + single.php).
- Developer C: US3 (archive.php + search.php + 404.php).
- Developer D: US4 (the verification step is small; after US1 lands).

---

## Notes

- [P] tasks touch different files with no read/write conflicts.
- [Story] labels map tasks to specific user stories for traceability.
- Each user story is independently completable and testable.
- Verification flows through the manual scenarios in `quickstart.md`.
- Commit after each task or logical group (e.g., after each user
  story phase).
- Stop at any checkpoint to validate the story independently
  before moving on.
- Avoid: vague tasks, same-file conflicts marked `[P]`, cross-story
  dependencies that break independence.