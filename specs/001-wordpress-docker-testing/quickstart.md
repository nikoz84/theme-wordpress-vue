# Quickstart Validation: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

This file is a runnable validation guide. It enumerates the
scenarios that prove the feature works end-to-end against the
acceptance scenarios in the spec. Implementation details live in
`tasks.md` and the source files; this file stays at the "what to
run, what to observe" level.

---

## Prerequisites

- A working Docker install — Docker Desktop 4.x+ on macOS / Windows
  (WSL2) or Docker Engine 24+ on Linux.
- A clone of the Vue Blocks repository.
- A copy of `.env.example` to `.env` with `WP_ADMIN_PASSWORD` and
  `WP_DB_PASSWORD` set (placeholders are fine for local testing).

---

## Scenario A — Cold-boot a fresh environment (US1 / SC-001, SC-005)

This is the **MVP scenario** and the most important test of the
whole feature.

### Setup

A fresh host with **no local Docker images** for `wordpress:6` or
`mysql:8.0` (use `docker rmi wordpress:6 mysql:8.0` to clear the
local cache if needed) and **no named volumes** `vb_db` or
`vb_uploads`.

```bash
git clone https://github.com/nikoz84/theme-wordpress-vue.git
cd theme-wordpress-vue
cp .env.example .env
# Edit .env and set WP_ADMIN_PASSWORD + WP_DB_PASSWORD (any non-production values).
```

### Steps

1. From the repo root, run the documented boot command:

   ```bash
   docker compose up -d
   ```

2. Watch the logs:

   ```bash
   docker compose logs -f wordpress
   ```

3. Wait for the bootstrap script to report "WordPress ready" (or
   equivalent).

4. Open `http://localhost:8080` in a browser.

### Expected outcomes

- The site loads in **under 5 minutes** (SC-001 cold-start bound).
- The homepage displays the Vue Blocks theme (posts grid rendered by
  `front-page.php` with seeded sample posts per FR-014).
- The site title is "Vue Blocks (local)" (or whatever
  `WP_SITE_TITLE` is set to).
- The `wp-admin` area at `/wp-admin` is reachable; logging in with
  the documented admin credentials works.
- "Appearance → Themes" shows **Vue Blocks** as the active theme.

---

## Scenario B — Warm-boot a populated environment (US1 / SC-002)

### Setup

The environment from Scenario A, still running.

### Steps

1. Stop the environment:

   ```bash
   docker compose down
   ```

2. Confirm the volumes persist:

   ```bash
   docker volume ls | grep vb_
   ```

4. Restart:

   ```bash
   docker compose up -d
   ```

5. Open the site again.

### Expected outcomes

- The site is reachable in **under 30 seconds** (SC-002 warm-start
  bound).
- All previously created posts and the seeded sample posts are
  visible.
- Admin login still works with the same credentials.

---

## Scenario C — Live theme edits without rebuild (US2 / SC-003)

### Setup

The environment from Scenario A running.

### Steps

1. Edit a CSS custom property in `:root` in `style.css` (e.g.,
   change `--vb-primary` to a different color).
2. Open the homepage in a browser and refresh.
3. Edit a PHP template file (e.g., `front-page.php`) — add a
   comment or change a heading.
4. Refresh the homepage.
6. Edit `assets/js/app.js` — change one of the `i18n` strings in
   the existing code path.
7. Refresh the homepage.

### Expected outcomes

- Each edit is visible on the next browser refresh (CSS, opaque,
  JS) — **no container restart required** (SC-003).
- The WordPress service container is still running with the same
  container ID throughout.

---

## Scenario D — Persistence across restarts (US3 / SC-004)

### Setup

The environment from Scenario A running.

### Steps

1. In `wp-admin`, create a new post with title "Persistence test"
   and any body text.
2. Upload a small image (e.g., a 100×100 PNG) to the media library
   and attach it to the post.
3. Configure a custom menu item in the primary menu.
4. Stop the environment:

   ```bash
   docker compose down
   ```

5. Start it again:

   ```bash
   docker compose up -d
   ```

6. Open the homepage and the new post in a browser.

### Expected outcomes

- The new post is visible on the homepage's posts grid.
- The new post's featured image is visible.
- The custom menu item is visible in the site's navigation.
- All other WP data (site title, active theme, customizer
  settings) is preserved.
- **Zero content loss** (SC-004).

---

## Scenario E — Reproducible first-run (US4)

### Setup

A second contributor (or CI runner) on a different host.

### Steps

1. Clone the repo on the second host.
2. Run `cp .env.example .env`, edit the two passwords.
3. Run `docker compose up -d`.

### Expected outcomes

- The site reaches a working state in a bounded time without
  needing to ask the theme author for help (SC-005).
- The seeded sample posts and the Vue Blocks active theme are
  identical to Scenario A's result.
- "Works on my machine" is impossible because every contributor
  starts from the same compose file.

---

## Scenario F — Re-running bootstrap is idempotent (FR-010)

### Setup

The environment from Scenario A running.

### Steps

1. Force-replay the bootstrap by restarting just the WordPress
   container (the entrypoint runs on every start):

   ```bash
   docker compose restart wordpress
   ```

2. Watch the logs:

   ```bash
   docker compose logs wordpress
   ```

3. Open the site and `wp-admin`.

### Expected outcomes

- The bootstrap script detects the existing install and **does
  NOT** wipe existing posts or recreate the admin user (per FR-010).
- Logs contain explicit "already installed, skipping" messages for
  any step it skipped.
- The site remains fully functional.

---

## Scenario G — Port collision is handled gracefully (Edge Case, FR-012)

### Setup

A second process on the host is already listening on port 8080
(e.g., another web server).

### Steps

1. Run the documented boot command:

   ```bash
   docker compose up -d
   ```

2. Observe the error.

### Expected outcomes

- Compose emits a clear message about port 8080 already being in
  use — **not** an opaque engine error (FR-012).
- The contributor's documented remediation: "stop the conflicting
  process, OR set `HOST_PORT=8081` in `.env` and retry."
- Running with `HOST_PORT=8081 docker compose up -d` succeeds.

---

## Scenario H — Missing Docker daemon (Edge Case, FR-012)

### Setup

Docker is not running on the host.

### Steps

1. Stop the Docker daemon.
2. Run the documented preflight script (if present) or the boot
   command directly.

### Expected outcomes

- A clear message: "Docker is not running. Start Docker and try
   again." (FR-012)
- The script exits non-zero with a useful exit code.

---

## Scenario I — Teardown leaves no orphaned state (US4 acceptance 2)

### Setup

The environment from Scenario A running.

### Steps

1. Run the documented teardown command:

   ```bash
   docker compose down -v
   ```

2. Inspect the host:

   ```bash
   docker ps -a
   docker volume ls
   docker network ls
   ```

### Expected outcomes

- No `vue-blocks`-related containers remain.
- `vb_db` and `vb_uploads` named volumes are removed (with `-v`).
- No orphaned networks.
- A fresh `docker compose up -d` from the same host produces a
  pristine install (same as Scenario A's expected outcomes).

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)