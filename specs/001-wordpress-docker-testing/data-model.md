# Phase 1 Data Model: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

This file enumerates the entities defined in the spec's
`### Key Entities` section, attributes each one, and notes the
validation rules and lifecycle / state transitions that the
implementation MUST honor.

---

## E1 — Testing Site

The local WordPress instance exposed to the developer.

| Field | Type | Required | Description |
|---|---|---|---|
| `reachableUrl` | URL | yes | `http://localhost:${HOST_PORT}` (or the configured hostname). |
| `adminUrl` | URL | yes | `http://localhost:${HOST_PORT}/wp-admin`. |
| `adminUser` | string | yes | Default `admin` (configurable via env). |
| `adminPassword` | secret | yes | Sourced from `WP_ADMIN_PASSWORD` env var (per FR-008). |
| `siteTitle` | string | yes | Set during the first-run install. |
| `activeTheme` | string | yes | `vue-blocks` — activated on first run. |
| `permalinkStructure` | string | yes | Post-name (`/%postname%/`) — matches the Vue Blocks REST-driven flows. |

### Validation

- `adminPassword` MUST NOT appear in any committed file.
- `activeTheme` MUST be exactly `vue-blocks` for the environment to
  satisfy FR-003.
- `reachableUrl` MUST bind to a host port ≥ 1024 (per FR-013).

### Lifecycle

| State | Trigger |
|---|---|
| **Absent** | Container not yet started. |
| **Installing** | First run; `wp core install` is in flight. |
| **Ready** | Install complete; admin user created; theme active; permalinks set; seed content imported. |
| **Populated** | Developer has used the site; new posts, media, menus exist. |
| **Persisted** | After `docker compose down`; named volumes retain content. |

## E2 — Database Store

The persisted MySQL state containing the WordPress tables.

| Field | Type | Description |
|---|---|---|
| `volumeName` | string | `vb_db`. |
| `mountPoint` | path | `/var/lib/mysql` inside the MySQL container. |
| `engine` | string | MySQL 8.x. |
| `tables` | set<string> | WordPress core tables (`wp_*`) plus any plugin/theme tables; the bootstrap script does not enumerate them. |

### Validation

- The volume MUST be declared in `docker-compose.yml` as a named
  top-level volume.
- The volume MUST NOT be bind-mounted to a host directory (it lives
  in Docker's volume storage; the host never sees the raw data).

### Lifecycle

| State | Trigger |
|---|---|
| **Absent** | Fresh install; volume does not yet exist. |
| **Initializing** | MySQL container starting up; bootstrap waiting. |
| **Empty** | Volume created but no WordPress tables yet. |
| **Installed** | WordPress core install complete; tables present. |
| **Populated** | Developer activity has added posts, options, etc. |
| **Stale** | Bind-mount mismatch between volumes and the running
  compose stack (e.g., compose file updated but volumes not
  recreated). Bootstrap script detects and recreates. |

## E3 — Uploads Store

The persisted `wp-content/uploads` directory.

| Field | Type | Description |
|---|---|---|
| `volumeName` | string | `vb_uploads`. |
| `mountPoint` | path | `/var/www/html/wp-content/uploads` inside the WordPress container. |
| `hostInspectable` | bool | `true` — the volume's contents MUST be inspectable via `docker volume inspect vb_uploads` and via the file-mount point on the host if the user chooses to bind-mount it for inspection. |

### Validation

- The volume MUST be writable by the WordPress process inside the
  container.
- The host MUST be able to read uploaded files (per Edge Cases:
  Filesystem permissions).

### Lifecycle

| State | Trigger |
|---|---|
| **Absent** | Fresh install. |
| **Empty** | Volume exists; no uploads. |
| **Populated** | At least one media file present (per Edge Cases: uploads survive restart). |

## E4 — Theme Source Mount

The project's source tree mounted into the WordPress container at
the Vue Blocks install location.

| Field | Type | Description |
|---|---|---|
| `hostPath` | path | The repository root on the host. |
| `containerPath` | path | `/var/www/html` inside the WordPress container (the whole web root is bind-mounted so the theme ends up at the right path). |
| `mode` | enum | `bind` (read/write). |
| `intentionalPersisted` | bool | `false` — the source of truth is the working tree; the host's working tree is what survives, not anything in the container. |

### Validation

- The mount MUST be a bind mount (not a named volume) so that edits
  on the host are visible inside the container.
- The mount MUST point at the repo root, not at a sub-directory; the
  theme directory is one level under it.
- The mount MUST be read/write — WP's theme editor and the
  container's filesystem permissions require it.

### Lifecycle

| State | Trigger |
|---|---|
| **Absent** | Container not started. |
| **Mounted** | Compose has started; bind mount active. |
| **Edited** | Developer changed a file in the working tree; the change is visible inside the container at the next file-system event. |

## E5 — Bootstrap Script

The artifact that wires WordPress, the database, and the theme
together at first run.

| Field | Type | Description |
|---|---|---|
| `path` | path | `bin/bootstrap.sh` in the repo root. |
| `entrypoint` | bool | `true` — the Compose WordPress service runs this script as its entrypoint. |
| `idempotent` | bool | `true` — the script MUST detect existing state and skip steps that have already been performed. |
| `envVarsRead` | set<string> | `HOST_PORT`, `WP_ADMIN_PASSWORD`, `WP_ADMIN_USER`, `WP_SITE_TITLE`, `WP_DB_HOST`, `WP_DB_USER`, `WP_DB_PASSWORD`, `WP_DB_NAME`. |

### Validation

- The script MUST be `bash -e`-compatible (exit on any error).
- The script MUST be idempotent: re-running it on a populated
  environment MUST NOT wipe existing posts or uploads (per FR-010).
- The script MUST emit actionable messages on each error path (per
  FR-012).

### Lifecycle

The script is invoked once on each container start, but is
**idempotent** — first run does the install; subsequent runs are
no-ops on the install steps.

---

## Cross-entity invariants

1. The Testing Site (E1) and the Database Store (E2) are
   **coupled** — the install writes site config into the database.
3. The Uploads Store (E3) is **persisted across restarts**; if wiped,
   media references in posts become broken.
4. The Theme Source Mount (E4) is the **only** entity that is not
   persisted; the host's working tree is the source of truth.
5. The Bootstrap Script (E5) **MUST** tolerate the absence of any
   single entity (e.g., DB not yet ready) and **MUST NOT** create
   the Testing Site without all the supporting entities being
   present.

---

## Out-of-scope entities (deliberately omitted)

- **Production WordPress deployment** — out of scope (per spec
  Assumptions).
- **MailHog / SMTP relay** — out of scope.
- **Reverse proxy / HTTPS termination** — out of scope.
- **Multi-site WordPress** — out of scope.
- **GitHub Actions / CI runner** — out of scope.