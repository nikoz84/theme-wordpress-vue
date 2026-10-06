# Contracts: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

This directory holds the interface definitions shared between the
contributor (who provides the `.env` file), the Compose stack (which
reads it), and the Bootstrap Script (which consumes both).

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `env.schema.md` | Contributor → `.env` → Compose + Bootstrap | Defines the env vars the contributor MAY set, their defaults, and validation rules. |
| `compose.schema.md` | Compose file → runtime | Defines the services, networks, and volumes the compose file declares and their contracts. |

## Contract reference

### `.env` (per `env.schema.md`)

| Var | Required | Default | Used by |
|---|---|---|---|
| `HOST_PORT` | no | `8080` | Compose service `wordpress.ports[0]` (host port) |
| `WP_ADMIN_USER` | no | `admin` | Bootstrap script (admin user) |
| `WP_ADMIN_PASSWORD` | yes (recommended) | (none — must be set) | Bootstrap script (admin password); `.env.example` provides a non-production default |
| `WP_ADMIN_EMAIL` | no | `admin@example.test` | Bootstrap script (admin email) |
| `WP_SITE_TITLE` | no | `Vue Blocks (local)` | Bootstrap script (site title) |
| `WP_DB_HOST` | no | `db` | Compose service `wordpress.environment` |
| `WP_DB_USER` | no | `wordpress` | Compose service `wordpress.environment` |
| `WP_DB_PASSWORD` | yes (recommended) | (none — must be set) | Compose service `db.environment` |
| `WP_DB_NAME` | no | `wordpress` | Compose service `db.environment` |

### Compose services (per `compose.schema.md`)

| Service | Image | Ports | Volumes | Depends on |
|---|---|---|---|---|
| `db` | `mysql:8.0` | (internal only) | `vb_db:/var/lib/mysql` | — |
| `wordpress` | `wordpress` (with custom entrypoint wrapper) | `${HOST_PORT:-8080}:80` | `vb_uploads:/var/www/html/wp-content/uploads`, repo root → `/var/www/html` | `db` |

## Cross-contract invariants

- `HOST_PORT` MUST default to a non-privileged port (≥ 1024) per
  FR-013.
- The Bootstrap Script MUST tolerate missing env vars by falling
  back to the documented defaults above (per FR-009).
- The compose file MUST NOT hardcode `8080`; the port MUST come
  from `${HOST_PORT:-8080}` (per FR-007).

## Related but separate surfaces

These are part of the contract surface but documented in the data
model:

- The named volumes `vb_db` and `vb_uploads` are data-model entities
  E2 and E3.
- The bind mount of the repo root is data-model entity E4.

## Out-of-scope surfaces (deliberately omitted)

- HTTPS / TLS termination — no public ingress.
- MailHog / SMTP relay — emails go to a null sink.
- Backup of named volumes — the spec's success criteria are about
  persistence, not backup; backups are out of scope.