# Env File Contract: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

Defines the `.env` file contract — what variables the contributor
MAY set, what the documented defaults are, and which variables MUST
be supplied by the contributor before the environment will boot.

This is a **documentation contract** — the file is plain text in
the INI-style env format that both Compose and bash `source`
understand. There is no schema validator; `bin/bootstrap.sh` reads
the values directly and emits an actionable error if a required
value is missing or malformed.

## Variables

### `HOST_PORT`

| Property | Value |
|---|---|
| Required | no |
| Default | `8080` |
| Type | integer |
| Constraint | MUST be a non-privileged port (≥ 1024) per FR-013. |
| Used by | Compose service `wordpress.ports[0]` (host port). |
| Failure mode | If the port is < 1024, the bootstrap script refuses with "HOST_PORT must be ≥ 1024; got ${HOST_PORT}." |

### `WP_ADMIN_USER`

| Property | Value |
|---|---|
| Required | no |
| Default | `admin` |
| Type | string |
| Constraint | MUST be a valid WP username (≤ 60 chars, no whitespace). |
| Used by | Bootstrap script — admin user creation. |

### `WP_ADMIN_PASSWORD`

| Property | Value |
|---|---|
| Required | recommended (no default — the bootstrap script MUST refuse to start without one in `.env.example`) |
| Default (in `.env.example`) | non-production placeholder, clearly marked test-only |
| Type | string |
| Constraint | MUST NOT appear in any committed file other than `.env.example` (per FR-008). The compose file MUST NOT contain this value. |
| Used by | Bootstrap script — admin user password. |
| Failure mode | If unset, the bootstrap script refuses with "WP_ADMIN_PASSWORD is required; copy `.env.example` to `.env` and set it." |

### `WP_ADMIN_EMAIL`

| Property | Value |
|---|---|
| Required | no |
| Default | `admin@example.test` |
| Type | string (RFC-5322 email). |
| Used by | Bootstrap script — admin user email. |

### `WP_SITE_TITLE`

| Property | Value |
|---|---|
| Required | no |
| Default | `Vue Blocks (local)` |
| Type | string |
| Used by | Bootstrap script — site title (`wp option update blogname`). |

### `WP_DB_HOST`

| Property | Value |
|---|---|
| Required | no |
| Default | `db` (the Compose service name) |
| Type | hostname or `host:port` |
| Used by | WordPress service environment — passed to WP's `DB_HOST` constant. |

### `WP_DB_USER`

| Property | Value |
|---|---|
| Required | no |
| Default | `wordpress` |
| Type | string |
| Used by | WordPress + DB services environment. |

### `WP_DB_PASSWORD`

| Property | Value |
|---|---|
| Required | recommended |
| Default | (none — `.env.example` provides a non-production placeholder) |
| Type | string |
| Constraint | MUST NOT be committed to the repository in plaintext other than `.env.example` (per FR-008). |
| Used by | DB + WordPress services environment. |
| Failure mode | If unset, the bootstrap script refuses with "WP_DB_PASSWORD is required; copy `.env.example` to `.env`." |

### `WP_DB_NAME`

| Property | Value |
|---|---|
| Required | no |
| Default | `wordpress` |
| Type | string |
| Used by | DB + WordPress services environment. |

## Validation

The Bootstrap Script (E5 in the data model) reads `.env` at
container start. It MUST:

1. Emit an actionable error (not a cryptic engine error) for any
   missing required value or any malformed value (per FR-021 — port
   number, email format, etc.).
2. Never log the values of `WP_ADMIN_PASSWORD` or `WP_DB_PASSWORD`.
3. Fall back to documented defaults for any variable that has one.

## `.env.example`

The repo MUST ship a `.env.example` with documented placeholder
values for every variable above. The placeholder values MUST be
clearly marked as test-only in inline comments.