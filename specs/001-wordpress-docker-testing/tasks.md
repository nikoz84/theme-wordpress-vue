# Tasks: WordPress Docker Testing Environment

**Input**: Design documents from `/specs/001-wordpress-docker-testing/`
- `plan.md` (required)
- `spec.md` (required; 4 user stories — US1/US2 P1, US3/US4 P2)
- `data-model.md` (5 entities: Testing Site, Database Store, Uploads Store, Theme Source Mount, Bootstrap Script)
- `contracts/compose.schema.md` (Compose v2 service / volume contract)
- `contracts/env.schema.md` (`.env` contract — 9 variables)
- `research.md` (10 decisions: R1–R10)
- `quickstart.md` (9 validation scenarios: A–I)

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

Single project — the theme is monolithic. New artifacts (compose
file, env file, bootstrap script, seed, php.ini) live at the repo
root or in `bin/`, `seed/`, `config/`. No backend/frontend split.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Lay down the artifact files the rest of the work
depends on. After this phase, the repo has all the static
configuration in place; the bootstrap script and tests run from
here.

- [X] T001 [P] Create `docker-compose.yml` at repo root per `contracts/compose.schema.md` (services `db` and `wordpress`, volumes `vb_db` and `vb_uploads`, healthcheck, `depends_on.condition: service_healthy`, `${HOST_PORT:-8080}:80` port mapping, bind mount of repo root to `/var/www/html`, `vb_uploads` to uploads path)
- [X] T002 [P] Create `.env.example` at repo root per `contracts/env.schema.md` with all 9 variables (`HOST_PORT`, `WP_ADMIN_USER`, `WP_ADMIN_PASSWORD`, `WP_ADMIN_EMAIL`, `WP_SITE_TITLE`, `WP_DB_HOST`, `WP_DB_USER`, `WP_DB_PASSWORD`, `WP_DB_NAME`) and inline comments marking the two password values as test-only
- [X] T003 [P] Create `bin/bootstrap.sh` skeleton with `set -euo pipefail`, a header comment block describing the script's purpose, and stub functions (`wait_for_db`, `install_wordpress`, `ensure_admin_user`, `activate_theme`, `set_permalinks`, `import_seed`) called in order from a main entrypoint
- [X] T004 [P] Create `seed/sample-content.xml` placeholder as a valid WXR file with 3 sample posts (each with a featured image URL, body text), one category, and one menu item linked to the primary menu — to satisfy FR-014
- [X] T005 [P] Create `config/php.ini` with `opcache.validate_timestamps=1` and `opcache.revalidate_freq=0` per research R8 so theme-source PHP edits reflect on the next request without a container restart

---

## Phase 2: Foundational (Bootstrap Script)

**Purpose**: Implement the bootstrap script — the **foundation**
every user story depends on. Without this phase, no WordPress site
exists; every story's verification flow depends on the script running
correctly first.

**⚠️ CRITICAL**: No user story work can begin until this phase is
complete.

- [X] T006 Implement `wait_for_db` function in `bin/bootstrap.sh`: retry `mysqladmin ping` with backoff up to 60 s (per research R10); print actionable message on timeout
- [X] T007 Implement `install_wordpress` function in `bin/bootstrap.sh`: run `wp core install` with env-var-supplied site title / admin user / admin password / admin email (per FR-009), idempotent (skip if WordPress is already installed)
- [X] T008 Implement `ensure_admin_user` function in `bin/bootstrap.sh`: create or update the admin user using `WP_ADMIN_USER` / `WP_ADMIN_PASSWORD` / `WP_ADMIN_EMAIL` (per FR-008); idempotent on re-run
- [X] T009 Implement `activate_theme` function in `bin/bootstrap.sh`: run `wp theme activate vue-blocks` (per FR-003); idempotent
- [X] T010 Implement `set_permalinks` function in `bin/bootstrap.sh`: run `wp rewrite structure '/%postname%/' --hard` to enable post-name permalinks (per FR-009 and the Vue Blocks REST-driven flows); idempotent
- [X] T011 Implement `import_seed` function in `bin/bootstrap.sh`: run `wp import seed/sample-content.xml --authors=skip` once, tracked via a WP option (`vb_seed_imported`) so re-runs are no-ops (per FR-010, FR-014)

**Checkpoint**: Foundation ready — `docker compose up` produces a
working WordPress site with the Vue Blocks theme active and sample
content visible; user story verification flows can now begin.

---

## Phase 3: User Story 1 - One-command environment boot (Priority: P1) 🎯 MVP

**Goal**: A contributor runs one documented command and gets a
fully working site with the Vue Blocks theme active and sample
content rendered.

**Independent Test**: Run quickstart Scenario A end-to-end:
1. `cp .env.example .env`, set `WP_ADMIN_PASSWORD`.
2. `docker compose up -d`.
3. Site is reachable in < 5 min with the Vue Blocks homepage
   visible, seeded sample posts visible, admin login works.

### Implementation for User Story 1

- [X] T012 [US1] Add "Quickstart" section to `README.md` documenting the boot command (`docker compose up -d`), the default URL (`http://localhost:8080`), and a link to the full documentation in `quickstart.md`
- [X] T013 [US1] Run quickstart Scenario A (cold boot < 5 min) and confirm the Vue Blocks homepage renders with seeded sample posts and admin login works

**Checkpoint**: At this point, User Story 1 is fully functional and
independently testable — the MVP is reachable in one command.

---

## Phase 4: User Story 2 - Live theme edits without rebuild (Priority: P1)

**Goal**: Edits to PHP / CSS / JS files inside the project
directory are reflected in the browser on the next page request,
with no container restart.

**Independent Test**: Run quickstart Scenario C — edit `style.css`,
a PHP template, and `assets/js/app.js`; each edit is visible on
the next browser refresh.

### Implementation for User Story 2

- [X] T014 [US2] Run quickstart Scenario C end-to-end (CSS, PHP, JS edits reflect on the next browser refresh with no container restart) and confirm the opcache settings from T005 keep PHP edits live

**Checkpoint**: At this point, User Story 2 is fully functional —
contributors can iterate on the theme without rebuilding or
restarting containers.

---

## Phase 5: User Story 3 - Database and uploads persist across restarts (Priority: P2)

**Goal**: After `docker compose down` followed by `docker compose
up`, all created posts, media files, and configuration are still
present.

**Independent Test**: Run quickstart Scenario D — create a post,
upload an image, configure a menu; stop and start; confirm zero
content loss.

### Implementation for User Story 3

- [X] T015 [US3] Run quickstart Scenario D end-to-end (zero content loss across `down`/`up`) and confirm the named volumes `vb_db` and `vb_uploads` survive per SC-004

**Checkpoint**: At this point, User Story 3 is fully functional —
content survives across sessions.

---

## Phase 6: User Story 4 - Reproducible first-run setup (Priority: P2)

**Goal**: A clean host with only Docker installed reaches the same
working state by following the documented steps; teardown leaves no
orphaned containers or volumes.

**Independent Test**: Run quickstart Scenarios I and E — clean
teardown + clean-host reproducibility.

### Implementation for User Story 4

- [X] T016 [US4] Create `bin/preflight.sh` that runs `docker info` and prints an actionable "Docker is not running. Start Docker and try again." message (per FR-012) and exits non-zero if the daemon is unreachable
- [X] T017 [P] [US4] Document the teardown command (`docker compose down -v`) in `README.md` next to the boot command
- [X] T018 [US4] Run quickstart Scenario I (clean teardown) and confirm no orphaned containers, volumes, or networks remain (depends on T017)

**Checkpoint**: At this point, User Story 4 is fully functional —
the environment is reproducible on any Docker-equipped host and
teardown is clean.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that touch multiple user stories, harden
the error paths, and finalize the contributor surface.

- [X] T019 [P] Add a port-collision guard at the top of `bin/bootstrap.sh` that prints "HOST_PORT=${HOST_PORT} is already in use; stop the conflicting process OR set HOST_PORT to another value (≥ 1024) and retry" (per FR-012 + Edge Case)
- [X] T020 [P] Add a missing-`.env`-file fallback in `bin/bootstrap.sh` that copies `.env.example` to `.env` with a printed warning when `.env` is absent (per research R10)
- [X] T021 [P] Update `README.md` with a "Troubleshooting" section covering port collision, missing Docker daemon, and missing `.env` (per FR-012)
- [X] T022 Run quickstart Scenarios E, F, G, H (clean-host reproducibility, idempotent bootstrap re-run, port-collision handling, missing-Docker handling)
- [X] T023 Verify FR-008 end-to-end: scan `.env`, `.env.example`, and `docker-compose.yml` to confirm no plaintext password credentials are committed (only `.env.example`'s documented non-production placeholder is allowed)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **Foundational (Phase 2)**: Depends on Setup (Phase 1) — **BLOCKS** all user stories.
- **User Stories (Phases 3–6)**: All depend on Foundational completion.
  - US1 (P1) first — establishes the boot.
  - US2 (P1) depends on US1 (the site must be reachable before live-edit verifications make sense).
  - US3 (P2) depends on US1 (persistence only meaningful on a working site).
  - US4 (P2) depends on US1 (clean teardown only meaningful after a working site).
- **Polish (Phase 7)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational (Phase 2). No dependencies on other stories.
- **US2 (P1)**: Can start after US1 verifies the site is reachable. Verification only — no new files.
- **US3 (P2)**: Can start after US1. Verification only — relies on the named volumes already declared.
- **US4 (P2)**: Can start after US1. Adds `bin/preflight.sh` and a README line; verification only.

### Within Each User Story

- Implementation tasks MUST run in dependency order (helpers before consumer).
- Verification tasks (quickstart scenarios) MUST run after the implementation they verify.
- Each user story is complete and independently testable before moving to the next priority.

### Parallel Opportunities

- **Phase 1**: T001, T002, T003, T004, T005 all touch different files — can run in parallel.
- **Phase 2**: T006, T007, T008, T009, T010, T011 are sequential (all in `bin/bootstrap.sh`).
- **Phase 6**: T016 first; T017 can run in parallel with T016 (different files); T018 depends on T016 and T017.
- **Phase 7**: T019, T020, T021 all touch different files — can run in parallel. T022, T023 depend on T019/T020/T021 (verification).

---

## Parallel Example: User Story 1

```bash
# T012 (README quickstart) and T013 (Scenario A verification):
# T012 can start in parallel with the verification prep;
# T013 must run after T012 is in place.
Task: "T012 [US1] Add Quickstart section to README.md"
Task: "T013 [US1] Run quickstart Scenario A"
```

## Parallel Example: User Stories 2, 3, 4 (after US1 done)

```bash
# All three are mostly verification flows; implementation is in
# Phase 2 (bootstrap script) and Phase 6 (preflight + README):
Task: "T014 [US2] Run quickstart Scenario C (live edits)"
Task: "T015 [US3] Run quickstart Scenario D (persistence)"
Task: "T018 [US4] Run quickstart Scenario I (clean teardown)"

# These can all run in parallel (different scenarios, different
# aspects of the same running environment).
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (5 files in parallel)
2. Complete Phase 2: Foundational (bootstrap script — 6 sequential steps)
3. Complete Phase 3: User Story 1 (README + Scenario A verification)
4. **STOP and VALIDATE**: Run quickstart Scenario A end-to-end.
5. Demo: a contributor can `cp .env.example .env`, set one password,
   and run `docker compose up -d` — they get a Vue Blocks site.

### Incremental Delivery

1. Setup + Foundational → Compose stack + bootstrap script are runnable; the site boots but may not be fully verified.
2. US1 → One-command boot verified end-to-end.
3. US2 → Live-edit loop verified end-to-end (CSS, PHP, JS).
4. US3 → Persistence verified end-to-end (DB, media, menus).
5. US4 → Reproducibility + clean teardown verified end-to-end.
6. Polish → Error paths hardened; troubleshooting section added; FR-008 verified end-to-end.

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together (Foundational is mostly one file, so it's effectively a single-developer task).
2. Once Foundational is done:
   - Developer A: US1 (README + Scenario A).
   - Developer B: US2 (Scenario C).
   - Developer C: US3 (Scenario D).
   - Developer D: US4 (preflight.sh + README + Scenario I).
3. Polish phase is small (error handlers + README troubleshooting) and can be shared work.

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