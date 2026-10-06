# Implementation Plan: WordPress Docker Testing Environment

**Branch**: `001-wordpress-docker-testing` | **Date**: 2026-10-06 | **Spec**: [spec.md](../specs/spec.md)

**Input**: Feature specification from `/specs/001-wordpress-docker-testing/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command; its definition describes the execution workflow.

## Summary

Provide a single-command Docker Compose-based local testing
environment for the Vue Blocks WordPress theme. The environment
brings up WordPress and a database, mounts the project directory as
the active theme, persists database and uploads across container
restarts, seeds sample content on first boot so the Vue Blocks
homepage renders its post grid immediately, and works on Linux,
macOS, and Windows (WSL2) hosts that meet the documented
prerequisites.

The environment is **additive**: no theme file is modified, no build
step is introduced, and a host with only the tracked theme files and
Docker installed gets a fully working site.

## Technical Context

**Language/Version**:
- YAML — `docker-compose.yml` (Compose v2 schema baseline).
- Bash — bootstrap / entrypoint scripts.
- PHP 7.4+ — runtime inside the WordPress container (theme's existing
  constraint).
- WordPress 6.0+ — runtime inside the WordPress container.

**Primary Dependencies**:
- **Docker Engine 24+** or **Docker Desktop 4.x+** (provides
  Compose v2 out of the box).
- **`wordpress` image** (Docker Hub official) — WordPress + Apache +
  PHP runtime.
- **`mysql:8.0` image** — database (matches the spec's MySQL 8.x
  default).
- **`wordpress-cli` (wp-cli)** — used inside the container for
  automated theme install/activate, admin user creation, permalink
  setup, and sample-content seeding. The wp-cli invocation is
  baked into a custom entrypoint wrapper.
- **Vue Blocks theme** — mounted from the host's project directory
  into `/var/www/html/wp-content/themes/vue-blocks` (the theme's
  install location).

**Storage**:
- **Named Docker volume `vb_db`** — MySQL data directory.
- **Named Docker volume `vb_uploads`** — `wp-content/uploads`.
- **Bind mount of the project directory** — read/write into the
  WordPress container at the theme's install location (per FR-004).

**Testing**: Manual end-to-end per `quickstart.md`. Optional:
  scripted healthcheck via `curl http://localhost:$HOST_PORT/`
  expecting HTTP 200 from a body that contains the Vue Blocks
  homepage markup.

**Target Platform**:
- Linux (x86_64 and arm64).
- macOS (Intel and Apple Silicon) via Docker Desktop.
- Windows 10/11 via WSL2 + Docker Desktop.

**Project Type**: Web application infrastructure — a local-only
containerized development environment. The implementation is
"additive artifacts in the repo root" rather than an
application-level code change.

**Performance Goals**:
- Cold boot: ≤ 5 minutes (SC-001).
- Warm boot: ≤ 30 seconds (SC-002).
- Edit-to-refresh latency: ≤ 1 second for CSS / PHP / JS edits
  reflected in the next browser page load (SC-003).

**Constraints**:
- Default host port MUST be ≥ 1024 (FR-013). Default: 8080.
- No SMTP, no HTTPS, no custom domain (per spec Assumptions).
- No CI integration in v1.
- Compose v2 baseline; v1 (`docker-compose`) compatibility is
  best-effort.

**Scale/Scope**:
- Single WordPress container, single MySQL container.
- One theme, no multisite.
- One host port (default 8080; configurable via `HOST_PORT` env var).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Evidence |
|---|---|---|
| I. Server-First Rendering (PHP) | **Pass** | The theme is mounted unchanged; PHP renders all HTML as today. The Docker setup adds no JS-rendered HTML. |
| II. Progressive Enhancement | **Pass** | The Docker setup adds no new client-side behaviors; existing no-JS paths are preserved. |
| III. WP Template Hierarchy Discipline | **Pass** | No template files added, renamed, or removed. `style.css` header and `functions.php` are loaded unchanged. |
| IV. REST API as the PHP↔Vue Bridge | **Pass** | No REST endpoints added or modified; `vbData` localization flows unchanged. |
| V. CDN-First Distribution with Optional Build Path | **Pass** | The Docker setup does not introduce a build step into the theme itself. The CDN Vue and un-built `app.js` continue to load as they do today. The amendment at v1.1.0 is irrelevant to this feature. |
| Technical Constraints | **Pass** | PHP 7.4+ and WP 6.0+ are baked into the official `wordpress` image tag; MySQL 8.x is the database. License (GPLv2+ for WP) and text domain `vue-blocks` are preserved by mounting the theme directory. |
| Development Workflow | **Pass** | No PHP file edits; new files live at the repo root (`docker-compose.yml`, `.env.example`, `bin/`, `seed/`). `ABSPATH` guards and `vb_`/`VB_`/`vb-` naming remain unchanged. |
| Governance | **Pass** | v1.1.0 amendment preceded this plan; no further amendment needed. |

**Gate verdict**: PASS — no unjustified violations. Proceeding to
Phase 0.

### Post-design re-evaluation (after Phase 1)

After generating `research.md`, `data-model.md`, `contracts/`, and
`quickstart.md`, the Constitution Check is re-evaluated:

| Principle | Status post-design | Evidence |
|---|---|---|
| I–IV. (unchanged principles) | **Pass** | Docker setup is strictly additive; theme source files are mounted read/write but no changes are required. |
| V. CDN-First with Optional Build Path | **Pass** | The Docker setup never references `dist/` or any build output; the mounted theme directory contains only the tracked source files (no `node_modules/`, no `dist/`). The CDN Vue and un-built `app.js` paths are unchanged. |
| Technical Constraints | **Pass** | The chosen `wordpress` image (PHP 7.4+) and `mysql:8.0` image match the Constitution's Technical Constraints. |
| Development Workflow | **Pass** | New artifacts sit alongside the theme; the theme's file ownership (PHP in `inc/`, JS in `assets/js/`, templates at root) is untouched. |

**Final gate verdict**: PASS — design is fully compliant with the
Constitution as amended to v1.1.0.

## Project Structure

### Documentation (this feature)

```text
specs/001-wordpress-docker-testing/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
│   ├── env.schema.md
│   └── compose.schema.md
├── checklists/
│   └── requirements.md
└── spec.md              # already created by /speckit.specify
```

### Source Code (repository root)

```text
vue-wp-theme/                       # theme directory (existing)
├── assets/
│   └── js/
│       └── app.js                  # source of truth (mounted as theme file)
├── style.css                      # source of truth (mounted as theme file)
├── functions.php                  # source of truth (mounted as theme file)
├── ...                            # all existing theme files unchanged

# New artifacts added at the repo root:

docker-compose.yml                 # Compose v2 baseline, services for WordPress + DB
.env.example                       # Documented defaults for env vars (HOST_PORT, WP_ADMIN_PASSWORD, ...)
bin/
└── bootstrap.sh                   # One-shot wp-cli bootstrap (theme install/activate, admin user, seed content)
seed/
└── sample-content.xml             # WXR (WordPress eXtended RSS) for first-run posts + images + menu
README.md                          # (or updates to the existing readme.txt) — documented boot command + URLs

# Mounted into the WordPress container at runtime:
#   - repo root                       -> /var/www/html
#   - repo root/wp-content/themes/<theme> -> /var/www/html/wp-content/themes/<theme>
# (Compose handles this through a project-root bind mount + the bootstrap script's symlink.)
```

**Structure Decision**: Single project, additive files only. The
theme directory is not modified. The Compose file lives at the repo
root so contributors can `cd vue-wp-theme && docker compose up`.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|---|---|---|
| (none) | — | — |

No complexity justifications needed; the Constitution Check passed
without violations.