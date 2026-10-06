# Tasks: Theme Branding & Customizer Integration

**Input**: Design documents from `/specs/004-theme-customizer-and-branding/`
- `plan.md` (required)
- `spec.md` (required; 4 user stories — US1/US2/US3 P1, US4 P2)
- `data-model.md` (3 entities: Site Title, Social URL, Layout)
- `contracts/fonts.contract.md` (Google Fonts `<link>` tags)
- `contracts/customizer.contract.md` (Customizer section shape + storage)
- `research.md` (6 decisions: R1–R6)
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

Single project. Theme runtime files live under `theme/`; the test
harness at the repo root is unchanged. The Customizer is built into
WordPress core (no new dependency).

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Land the `.env` reference defaults that other phases
consume.

- [X] T001 [P] Update `.env.example` at the repo root: change `WP_SITE_TITLE=Vue Blocks (local)` to `WP_SITE_TITLE=Safe Mídia` (per clarification Q1)

---

## Phase 2: User Story 1 - Site title reflects the Safe Mídia brand (Priority: P1)

**Goal**: A fresh install's `blogname` is "Safe Mídia" (per FR-001);
the theme name in `style.css` remains "Vue Blocks" (per FR-002).

**Independent Test**: `wp option get blogname --allow-root` prints
exactly `Safe Mídia` after a fresh install.

### Implementation for User Story 1

- [X] T002 [US1] Update `bin/bootstrap.sh` `--title="${WP_SITE_TITLE:-Vue Blocks (local)}"` to `--title="${WP_SITE_TITLE:-Safe Mídia}"` (defense-in-depth default if `.env` is missing)
- [X] T003 [US1] Run `docker compose down -v && docker compose up -d` and verify `wp option get blogname --allow-root` returns `Safe Mídia` (per SC-001); confirm `wp-admin → Appearance → Themes` still lists the theme as "Vue Blocks" (per FR-002 / SC-001 acceptance 2)

**Checkpoint**: At this point, US1 is fully functional — a fresh install
lands on the Safe Mídia–branded site identity.

---

## Phase 3: User Story 2 - Merriweather and Inter fonts load on every page (Priority: P1)

**Goal**: `<head>` on every front-end page contains the three Google
Fonts `<link>` tags per `contracts/fonts.contract.md`.

**Independent Test**: `curl -s http://localhost:8080/ | grep -oE '<link[^>]+googleapis[^>]+>' | sort -u` returns exactly three lines (per SC-002).

### Implementation for User Story 2

- [X] T004 [US2] Edit `theme/functions.php` to add (a) a `wp_enqueue_style` for the Google Fonts URL `https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Inter:wght@300;400;500;600&display=swap` with handle `vb-google-fonts`, and (b) two `wp_resource_hint` calls adding `preconnect` to `fonts.googleapis.com` and `fonts.gstatic.com` (the latter with `crossorigin`), registered on `wp_enqueue_scripts` / `wp_resource_hint_h` per `contracts/fonts.contract.md`
- [X] T005 [US2] Verify (per SC-002): `curl -s http://localhost:8080/ | grep -oE '<link[^>]+googleapis[^>]+>' | sort -u` returns exactly the three expected `<link>` tags; verify a second page (e.g., a published post) returns the same three `<link>` tags

**Checkpoint**: At this point, US2 is fully functional — the Safe Mídia
typography renders correctly.

---

## Phase 4: User Story 3 - Social media URLs configurable from the Customizer (Priority: P1) 🎯 MVP

**Goal**: An admin enters four social URLs in `wp-admin → Appearance
→ Customize`; the navbar and footer social icons link to those URLs
(icons with empty URLs are hidden).

**Independent Test**: Set a Facebook URL in the Customizer; the
navbar and footer both contain exactly one Facebook icon with the
configured `href`; clear the URL; both icons disappear (per SC-003).

### Implementation for User Story 3

- [X] T006 [US3] Edit `theme/functions.php` to add a `customize_register` callback that registers: one section (`vb_social`, title "Social media", description "URLs for the navbar and footer social icons. Leave a field empty to hide its icon."); four settings (`vb_social_facebook`, `vb_social_instagram`, `vb_social_x`, `vb_social_linkedin`); four URL controls (one per setting, default `''`, sanitize `esc_url_raw`, transport `refresh`)
- [X] T007 [US3] Edit `theme/template-parts/header/navbar.php` to read the four social theme_mods via `get_theme_mod` and conditionally render each `<a class="soc">` element only when its URL is non-empty (per FR-005)
- [X] T008 [US3] Edit `theme/template-parts/footer/site-footer.php` to read the four social theme_mods via `get_theme_mod` and conditionally render each `<a class="footer-soc">` element only when its URL is non-empty (per FR-006)

**Checkpoint**: At this point, US3 is fully functional — the social
URLs are end-to-end configurable.

---

## Phase 5: User Story 4 - Layouts inventory document (Priority: P2)

**Goal**: `theme/DESIGN_LAYOUTS.md` documents every entry under
`layouts-html/` with title, v1 status, and the Safe Mídia sections
present (per FR-007).

**Independent Test**: Open the file and confirm every `layouts-html/`
entry is listed; the home page entry explicitly lists navbar, hero,
news grid, footer.

### Implementation for User Story 4

- [X] T009 [US4] Create `theme/DESIGN_LAYOUTS.md` with one section per `layouts-html/` entry (01 - Home through 09 - Termos de Uso); each section lists the Safe Mídia sections present in the layout HTML and an explicit v1 disposition (in scope / deferred / reference only) per `research.md` R6

**Checkpoint**: At this point, US4 is fully functional — contributors
have a single document explaining what's in `layouts-html/`.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Run the quickstart scenarios end-to-end and verify the
packaged deliverable.

- [X] T010 [P] Verify the theme name in `theme/style.css`'s WP header is still `Vue Blocks` (per FR-002) — `grep -E '^Theme Name:' theme/style.css` returns "Theme Name: Vue Blocks"
- [X] T011 Run quickstart Scenario A (fresh install → `wp option get blogname` = "Safe Mídia")
- [X] T012 Run quickstart Scenarios B + C (Google Fonts load + Customizer social URLs)
- [X] T013 Run quickstart Scenarios D + E (layouts inventory present in the package; zip ≤ 5 MB and no test-harness leakage per SC-005)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **US1 (Phase 2)**: Depends on Setup (T001) for the env-var default.
- **US2 (Phase 3)**: Depends on Setup only — independent of US1.
- **US3 (Phase 4)**: Depends on Setup only — independent of US1 and US2.
- **US4 (Phase 5)**: Depends on Setup only — independent of all other stories.
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1)**: Can start after Phase 1 (T001). No dependencies on other stories.
- **US2 (P1)**: Can start after Phase 1 — independent of US1 and US3.
- **US3 (P1, MVP)**: Can start after Phase 1 — independent of US1 and US2.
- **US4 (P2)**: Can start after Phase 1 — independent of all other stories.

### Within Each User Story

- US1: T002 (bootstrap default) must precede T003 (verification) — sequential within the same file and command flow.
- US2: T004 (enqueue) precedes T005 (verify) — sequential.
- US3: T006 (Customizer registration) precedes T007 + T008 (template parts read theme_mods) — T007 and T008 touch different files and can run in parallel after T006.

### Parallel Opportunities

- **Phases 2–5** (US1 vs US2 vs US3 vs US4): different files; can run in parallel after Phase 1.
- **T007 + T008** (US3): different template-part files; can run in parallel after T006.
- **T010 + T011 + T012 + T013** (Polish): mostly sequential (each verifies a preceding step).

---

## Parallel Example: User Story 1 + 2 + 3 + 4 (after Phase 1)

```bash
# US1 — Site title (different file: bin/bootstrap.sh):
Task: "T002 [US1] Update bin/bootstrap.sh --title default fallback"
# US2 — Fonts (different file: theme/functions.php section 1):
Task: "T004 [US2] Add Google Fonts enqueue to theme/functions.php"
# US3 — Customizer (different file: theme/functions.php section 2):
Task: "T006 [US3] Register Customizer section + 4 settings + 4 controls in theme/functions.php"
# US4 — Layouts inventory (different file: theme/DESIGN_LAYOUTS.md):
Task: "T009 [US4] Create theme/DESIGN_LAYOUTS.md"

# T007 (navbar) and T008 (footer) can run after T006 (same wave as US1/US2/US4 above, since the file they touch is navbar.php / site-footer.php, NOT functions.php):
Task: "T007 [US3] Update theme/template-parts/header/navbar.php"
Task: "T008 [US3] Update theme/template-parts/footer/site-footer.php"
```

---

## Implementation Strategy

### MVP First (User Story 3 — Social Customizer)

The clearest "minimum value" of the feature is the Customizer
integration: a site owner can set real social URLs and the icons
reflect them. That requires US3 only (and the Setup env tweak).

```
Phase 1 (T001) → US3 (T006 → T007 + T008 in parallel) → Polish (T011 + T012)
```

If everything else is deferred, the Customizer still works and the
spec's MVP criterion (the social URLs flow end-to-end through the
Customizer → navbar/footer → icon href) is satisfied.

### Incremental Delivery

1. Setup (T001) — lands the env defaults.
2. US1 (T002 → T003) — site title fixed.
3. US2 (T004 → T005) — fonts load.
4. US3 (T006 → T007 + T008) — Customizer end-to-end.
5. US4 (T009) — layouts inventory documented.
6. Polish (T010 → T011 → T012 → T013) — verification end-to-end.

### Parallel Team Strategy

With multiple developers after Phase 1:

- Developer A: US1 (one-line bootstrap edit + verification).
- Developer B: US2 (functions.php enqueue + verification).
- Developer C: US3 (Customizer + two template-part edits + verification).
- Developer D: US4 (documentation file).

Each story has its own files and self-contained verification.

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