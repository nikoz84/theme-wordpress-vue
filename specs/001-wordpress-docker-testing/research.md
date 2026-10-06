# Phase 0 Research: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

This file consolidates the research needed to resolve every
`NEEDS CLARIFICATION` item and every technology-choice question that
emerged during planning. The format follows the project's planning
convention: **Decision** / **Rationale** / **Alternatives considered**.

## R1 — Compose version baseline

**Decision**: Compose v2 baseline (`docker compose ...` with a
space). v1 (`docker-compose` with a hyphen) compatibility is
best-effort (per the spec's Assumptions).

**Rationale**: v2 is the current default; Docker Desktop 4.x and
Docker Engine 24+ ship with v2 built in. Writing to the v2 schema
keeps the compose file modern and removes the need for a separate
v1 migration.

**Alternatives considered**:
- *v1 only* — would exclude current Docker installs.
- *Both via an install script* — adds complexity for diminishing
  benefit. Rejected.

## R2 — WordPress image choice

**Decision**: Use the official `wordpress` image (no version pin in
v1, or pinned to the most recent `6.x` tag at implementation time)
plus a custom entrypoint wrapper that runs `wp-cli` for bootstrap.

**Rationale**: The official image is widely used, has PHP 7.4+
variants, and is updated regularly. Wrapping it with a custom
entrypoint lets us run `wp-cli` on first boot without forking the
image.

**Alternatives considered**:
- *Custom Dockerfile* built on `php:8.x-apache` — would let us
  install `wp-cli` in the image, but adds a separate build step
  (violates the Constitution's "additive, no build step" principle
  for the theme itself — and the spec treats the theme as the
  artifact under test). Rejected.
- *Bitnami `wordpress` image* — opinionated defaults that don't
  match the spec; rejected.

## R3 — Database choice

**Decision**: `mysql:8.0`.

**Rationale**: Matches the spec's "MySQL 8.x" default. WordPress
officially supports MySQL 8; MariaDB 10.x is also supported but
not the WP core reference default.

**Alternatives considered**:
- *MariaDB 10.6 LTS* — works equivalently for WP; rejected to keep
  the spec's assumption visible (the spec assumes MySQL 8.x).
- *SQLite* — not officially supported by WP core; rejected.

## R4 — Bootstrap mechanism

**Decision**: A `bin/bootstrap.sh` shell script invoked from the
WordPress container's entrypoint on first start. The script:
1. Waits for the database to be reachable.
2. Runs `wp core install` if WordPress is not yet installed.
3. Creates / updates the admin user with the env-var-sourced
   password.
4. Activates the Vue Blocks theme.
5. Configures permalinks to a Vue Blocks-friendly structure
   (e.g., `post name`).
6. Imports `seed/sample-content.xml` if it hasn't been imported yet
   (idempotency: tracks import via a WP option or a marker file).

**Rationale**: `wp-cli` is the standard WordPress bootstrap surface and
is the canonical way to automate installs / theme activations /
content seeding. Using it from an entrypoint keeps the workflow
declarative and avoids writing custom PHP bootstrap code.

**Alternatives considered**:
- *A custom plugin* (`mu-plugins`) — would centralize logic but adds
  the WP plugin surface to a testing artifact; rejected.
- *SQL-only seed* — inflexible, doesn't seed menus or images
  cleanly; rejected.

## R5 — Sample content seeding

**Decision**: Ship a WXR file (`seed/sample-content.xml`) and import
it once on first boot via `wp import`. The WXR includes:
- A handful of published posts (5–10).
- Featured images (referenced by URL; uploaded by the importer to
  the media library).
- A configured primary menu linked to the Vue Blocks `primary`
  menu location.

**Rationale**: WXR is the WordPress-standard export/import format;
`wp import` ships with `wp-cli`. Posts get images and menus get
configured in one pass.

**Alternatives considered**:
- *A SQL dump* — works but doesn't trigger WP's image processing;
  rejected.
- *Generated content via `wp post create` shell loop* — verbose and
  harder to inspect; rejected.

## R6 — Network and ports

**Decision**: Single Compose network (default bridge is fine for
local-only). The host port is configurable via `HOST_PORT` env var
(default `8080` per FR-013). The container port is the standard
WordPress port 80.

**Rationale**: A single network is enough for the two services to
talk. `HOST_PORT` env var matches FR-007's "configurable local URL"
requirement without touching the compose file.

**Alternatives considered**:
- *Hardcoded `8080:80`* — would violate FR-007's configurability
  requirement; rejected.
- *Docker network aliases* — overkill for two services; rejected.

## R7 — Volume strategy

**Decision**: Two named Docker volumes plus one bind mount:
- Named volume `vb_db` → `/var/lib/mysql` inside the MySQL
  container.
- Named volume `vb_uploads` → `/var/www/html/wp-content/uploads`
  inside the WordPress container.
- Bind mount of the repo root → `/var/www/html` inside the
  WordPress container (so the theme's source files are visible
  and editable).

**Rationale**: Named volumes are the Docker-canonical way to persist
container state across restarts. The bind mount of the repo root
satisfies FR-004 (live theme edits without restart) — edits to
`style.css`, `functions.php`, or `assets/js/app.js` are reflected
on the next page request (with the WP container's PHP/Apache
opcache flushed by the entrypoint on each request, per FR's spirit).

**Alternatives considered**:
- *Bind-mount the DB data directory to the host* — leaks database
  internals to the contributor's filesystem; rejected.
- *Single combined volume* — would lose the clear "DB vs uploads"
  separation that the spec calls out as two distinct entities.
  Rejected.

## R8 — Opcache handling for live edits

**Decision**: The entrypoint runs `wp eval 'opcache_reset();'` (or
its WP-CLI equivalent) after the bind mount is established; the
default PHP configuration in the official `wordpress` image disables
opcache for CLI, but Apache's opcache is enabled. The simplest
robust approach is to set `opcache.revalidate_freq=0` and
`opcache.validate_timestamps=1` in a custom `php.ini` shipped at
`config/php.ini` and mounted into the container.

**Rationale**: Without aggressive opcache invalidation, PHP file
edits do not surface until the cache expires — defeating Story 2's
"edits visible without container restart."

**Alternatives considered**:
- *Disable opcache entirely* — measurably slower runtime; rejected.
- *Restart Apache after every edit* — breaks the no-restart
  invariant; rejected.

## R9 — Sample image hosting

**Decision**: Use small placeholder images served from the WXR
importer's HTTP fetch. The importer uploads to the media library
and the images live in the persisted `vb_uploads` volume.

**Rationale**: Keeps the seed self-contained; the WXR ships with
the importer pointing at known-good image URLs (e.g., Picsum or a
checked-in fallback).

**Alternatives considered**:
- *Checking image binaries into `seed/`* — bloats the repo;
  rejected.

## R10 — Failure modes handled by the implementation

| Scenario | Handling |
|---|---|
| `HOST_PORT` already bound | Bootstrap script prints a clear "port X is already in use; set HOST_PORT to another value" message and exits with a non-zero status (per FR-012). |
| Docker daemon not running | Compose emits its standard error; we add a `bin/preflight.sh` that runs `docker info` first and prints "Docker is not running. Start Docker and try again." |
| DB connection failure | `bin/bootstrap.sh` retries with backoff for up to 60 s before failing. |
| Existing populated volume | Idempotent: theme activation / admin user creation are skipped if already done. |
| Missing .env file | Compose reads `.env.example` if `.env` is missing, with a printed warning. |

## Resolved `NEEDS CLARIFICATION` items

None remained at the start of Phase 0; the spec was clarified during
`/speckit.clarify` (boot time, admin password, sample content) and
all defaults documented in the spec's `## Assumptions` section
hold.

## Deferred to `/speckit.tasks`

- Exact `wordpress` image tag at implementation time (pin to the
  current 6.x release).
- Exact MySQL 8.x tag (current LTS-recommended version).
- `wp-cli` invocation script details.
- `bin/preflight.sh` exact contents.
- `.env` validation (a small script that fails if required vars are
  missing).