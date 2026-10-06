# Feature Specification: WordPress Docker Testing Environment

**Feature Branch**: `001-wordpress-docker-testing`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "create a wordpress dockerfile and docker compose for testing the project git remote add origin https://github.com/nikoz84/theme-wordpress-vue.git
git branch -M main
git push -u origin main and add this repository or echo \"# theme-wordpress-vue\" >> README.md
git init
git add README.md
git commit -m \"first commit\"
git branch -M main
git remote add origin https://github.com/nikoz84/theme-wordpress-vue.git
git push -u origin main"

**Intended repository**: `https://github.com/nikoz84/theme-wordpress-vue.git`
(remote-name form; git push/deployment commands were mentioned alongside the
feature description and are tracked separately as deferred project-setup
intents, not as requirements of this feature.)

**Project**: Vue Blocks — a classic WordPress PHP theme with Vue.js 3 layered
on top (see `.specify/memory/constitution.md` for governing principles).

## Clarifications

### Session 2026-10-06

- Q: What's an acceptable time-to-working-site for a developer running the
  boot command from a cold state? → A: 5 min cold start / 30 s warm start
  (becomes SC-001 and SC-002).
- Q: How should the default admin password be handled? → A: Sourced from
  a documented environment variable with a non-production default in
  `.env.example`; never hardcoded in the compose file (refines FR-008).
- Q: Should the environment seed sample content for first-run demo? → A:
  Yes — bootstrap creates a handful of published posts with featured
  images and configures a primary menu so the Vue Blocks homepage renders
  its post grid immediately (adds FR-014).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - One-command environment boot (Priority: P1)

A theme developer who has just cloned (or received) the project wants a
running WordPress site with the Vue Blocks theme installed, activated, and
fully usable, produced from a single documented command.

**Why this priority**: Without a one-shot boot, every new contributor (and
every clean CI run) repeats the same manual WordPress + DB + theme setup,
which is the entire pain this feature exists to remove. This is the
minimum viable slice of value.

**Independent Test**: On a clean Linux/macOS/WSL2 host with only Docker
installed, the documented command brings up a WordPress site reachable in
a browser, with the Vue Blocks theme visible on the public site and an
admin login that works.

**Acceptance Scenarios**:

1. **Given** the project repository and an idle Docker daemon, **When** the
   developer runs the documented boot command, **Then** the site is
   reachable at the documented local URL within the time bounds defined
   in SC-001/SC-002 and displays a page rendered by the Vue Blocks theme
   (not a stock theme).
3. **Given** a fresh boot, **When** the developer logs into the WP admin
   area, **Then** the Vue Blocks theme is shown as the active theme and
   the site title / admin user exist (no manual installation wizard is
   presented).
2. **Given** a fresh boot, **When** the developer inspects the source, the
   seeded sample posts and the Vue Blocks homepage render with the
   theme's PHP templates (per FR-014).

---

### User Story 2 - Live theme edits without rebuild (Priority: P1)

While the environment is running, the developer edits PHP, CSS, or
`assets/js/app.js` files inside the project directory and expects those
edits to appear in the browser without rebuilding images or restarting
containers.

**Why this priority**: Editing a theme without a working live-reload loop
defeats the point of a local testing setup — every fix requires a restart
and breaks the natural feedback cycle.

**Independent Test**: Edit any CSS variable at `:root` in `style.css` (or
any template file), refresh the browser, and observe the change without
having restarted any container.

**Acceptance Scenarios**:

1. **Given** a running environment, **When** the developer changes a CSS
   custom property in `style.css`, **Then** the change is visible on the
   next browser refresh with no container restart.
2. **Given** a running environment, **When** the developer edits a PHP
   template file, **Then** the next page request reflects the edit (PHP
   does not need a full restart, but opcache is cleared as required).
3. **Given** a running environment, **When** the developer edits
   `assets/js/app.js`, **Then** the next browser refresh loads the new
   script without re-running the boot command.

---

### User Story 3 - Database and uploads persist across restarts (Priority: P2)

The developer writes posts, configures menus, and uploads images, then
stops the environment. When the developer starts it back up, everything they
created is still there.

**Why this priority**: Without persistence, every `docker compose down`
wipes content and the environment is unusable for any flow that takes more
than one session. This is the difference between "demo" and "real test."

**Acceptance Scenarios**:

1. **Given** the environment is running with at least one published post
   and one uploaded media file, **When** the developer stops and starts
   the environment, **Then** the post, media file, and any menu or
   widget configuration are still present on next load.

---

### User Story 4 - Reproducible first-run setup (Priority: P2)

A brand-new contributor (or CI runner) follows the documented steps and
ends up with an environment that is functionally identical to the
original developer's: same WordPress version, same admin user, same theme
active, same baseline content (or explicitly empty baseline).

**Why this priority**: "Works on my machine" failures are exactly what
Docker testing environments are meant to eliminate.

**Acceptance Scenarios**:

1. **Given** a clean host with only Docker, **When** the contributor
   follows the documented setup steps, **Then** they reach a working site
   in a bounded time without needing to ask the author for help.
2. **Given** the environment is running, **When** the contributor runs
   the documented teardown command and a third party starts it fresh on
   the same host, **Then** both reach the same initial state (theme
   active, no orphaned containers or volumes left behind unintentionally).

---

### Edge Cases

- **Port collision**: The default site port is already in use on the host
  (e.g., another web server bound to 80/8080). The environment MUST be
  configurable to use a different host port and MUST fail with a clear
  message if the requested port is unavailable.
- **Missing Docker**: The host has no Docker daemon. The setup MUST fail
  with an actionable message rather than a cryptic engine error.
- **Re-running setup**: If the developer re-invokes the bootstrap (e.g.,
  because they edited the bootstrap script), the existing database and
  uploads MUST NOT be wiped. Re-running MUST be idempotent on a populated
  environment and MUST be safely initializable on a fresh one.
- **Outdated local image cache**: If the WP image on disk is stale, the
  environment MUST pull or warn the developer so on refresh the running
  WordPress matches the documented version.
- **Filesystem permissions**: Files created inside the container (uploads,
  any logs) MUST be readable/writable from the host so the developer can
  inspect or version-control content they care about.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide a single documented command (and
  its inverse teardown command) that brings up the full testing
  environment.
- **FR-002**: The environment MUST consist of a WordPress service and a
  database service (and any supporting services needed to bootstrap the
  theme) wired together by a compose file checked into the repo.
- **FR-003**: The environment MUST install and activate the Vue Blocks
  theme automatically on first start so the developer does not have to
  use the WP admin UI for theme setup.
- **FR-004**: The project directory MUST be mounted into the WordPress
  service at the theme's install location so any edit to a theme source
  file is reflected on the next page load without rebuilding or
  restarting the container.
- **FR-005**: The WordPress database MUST be persisted across container
  restarts so that posts, settings, and theme configuration survive a
  `down`/`up` cycle.
- **FR-006**: The `wp-content` uploads MUST be persisted across container
  restarts so that media files survive a `down`/`up` cycle.
- **FR-007**: The site MUST be reachable on a configurable local URL
  (default documented in the README) so contributors without a domain
  can browse it.
- **FR-008**: The bootstrap MUST create a default admin user whose password
  is sourced from a documented environment variable (e.g.,
  `WP_ADMIN_PASSWORD`) with a non-production default value supplied via
  `.env.example`; the password MUST NOT be hardcoded in the compose file
  and MUST NOT be committed to the repository in plaintext. The default
  value MUST be clearly marked as a test-only credential in the
  documentation.
- **FR-009**: The bootstrap MUST complete WordPress core installation
  (site title, admin user, permalink settings useful for the Vue Blocks
  REST-driven flows) automatically on first start.
- **FR-010**: The environment MUST be idempotent: running the boot
  command on a populated environment MUST NOT wipe existing posts or
  uploads; running it on an empty environment MUST initialize a working
  baseline.
- **FR-011**: The environment MUST work on Linux, macOS, and Windows (with
  WSL2) hosts that meet the documented prerequisites.
- **FR-012**: Port conflicts and missing-daemon conditions MUST surface as
  actionable messages, not as raw engine errors.
- **FR-013**: The default configuration MUST NOT bind to any port below
  1024 and MUST default to a non-privileged port (for example, 8080) to
  avoid requiring root.
- **FR-014**: On first start, the bootstrap MUST seed a small baseline of
  sample content (at minimum: a handful of published posts with featured
  images and a configured primary menu) so the Vue Blocks homepage renders
  its post grid and its primary surfaces are immediately exercisable by
  the developer without manual content creation.

### Key Entities *(include if feature involves data)*

- **Testing Site**: The local WordPress instance exposed to the developer;
  attributes include reachable URL, admin user, admin password, site
  title, active theme.
- **Database Store**: The persisted MySQL/MariaDB state containing the
  WordPress tables; survives container restarts via a named volume or
  bind mount.
- **Uploads Store**: The persisted `wp-content/uploads` directory; same
  persistence rules as the database store.
- **Theme Source Mount**: The project's source tree mounted read/write
  into the WordPress container at the Vue Blocks install location;
  intentionally non-persistent — source of truth is the working tree.
- **Bootstrap Script**: The artifact that wires WordPress, the database,
  and the theme together at first run (and on subsequent runs); a single
  reproducible entry point for the developer.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: From a clean host with no local Docker images, the documented
  boot command brings up a WordPress site with the Vue Blocks theme
  visible on the public site in **under 5 minutes** (cold start, including
  image pull, WP install, and theme activation).
- **SC-002**: A subsequent boot with images cached and named volumes
  populated reaches a working site in **under 30 seconds** (warm start).
- **SC-003**: Any edit to a file inside the project directory (PHP, CSS,
  or `assets/js/app.js`) is visible on the next browser page refresh with
  no container restart.
- **SC-004**: After `docker compose down` followed by `docker compose up`,
  every post, media file, menu, and theme setting that existed before the
  `down` is still present on next load — **zero content loss**.
- **SC-005**: A developer with only Docker installed, following the
  documented steps, reaches a working site without needing to ask the
  theme author for help.

## Assumptions

- Target users are theme contributors with Docker (Engine or Desktop)
  installed locally on Linux, macOS, or Windows with WSL2.
- This environment is for local development and testing only — production
  deployment, HTTPS termination, and email sending (SMTP) are explicitly
  out of scope.
- The intended GitHub remote (`https://github.com/nikoz84/theme-wordpress-vue.git`)
  is not yet created; the test of "the environment works" does not depend
  on pushing the project.
- Default credentials and ports are acceptable for a local-only test
  environment; the system MUST document them as test-only.
- The Vue Blocks theme version installed matches the version checked
  out in the working tree (no separate version pinning required for v1).
- The default WordPress and database versions are the most recent
  officially supported releases available at the time of bootstrap,
  compatible with the theme's documented requirements (PHP 7.4+,
  WordPress 6.0+).
- The Docker Compose v2 (`docker compose` with a space) syntax is the
  baseline; v1 (`docker-compose`) compatibility is best-effort.
- No CI integration, no multi-site, no HTTPS, no custom domain, and no
  SMTP mail relay are required for the testing environment to fulfill
  its purpose.