# Compose Schema Contract: WordPress Docker Testing Environment

**Date**: 2026-10-06
**Spec**: `specs/001-wordpress-docker-testing/spec.md`
**Branch**: `001-wordpress-docker-testing`

Defines the Compose v2 schema contract — the services, networks,
volumes, and dependencies the `docker-compose.yml` file MUST declare
to satisfy the spec.

## Schema (Compose v2)

```yaml
version: "2"        # see "version" note below

services:
  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${WP_DB_NAME:-wordpress}
      MYSQL_USER: ${WP_DB_USER:-wordpress}
      MYSQL_PASSWORD: ${WP_DB_PASSWORD:?WP_DB_PASSWORD is required}
      MYSQL_RANDOM_ROOT_PASSWORD: "yes"
    volumes:
      - vb_db:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 5s
      retries: 20

  wordpress:
    image: wordpress:6
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    ports:
      - "${HOST_PORT:-8080}:80"
    environment:
      WORDPRESS_DB_HOST: ${WP_DB_HOST:-db}
      WORDPRESS_DB_USER: ${WP_DB_USER:-wordpress}
      WORDPRESS_DB_PASSWORD: ${WP_DB_PASSWORD:?WP_DB_PASSWORD is required}
      WORDPRESS_DB_NAME: ${WP_DB_NAME:-wordpress}
      # The vars below are read by bin/bootstrap.sh (the container's
      # entrypoint) to drive `wp core install` and the admin user. They
      # MUST be present; `.env` provides the placeholders.
      WP_ADMIN_USER: ${WP_ADMIN_USER:?WP_ADMIN_USER is required}
      WP_ADMIN_PASSWORD: ${WP_ADMIN_PASSWORD:?WP_ADMIN_PASSWORD is required}
      WP_ADMIN_EMAIL: ${WP_ADMIN_EMAIL:?WP_ADMIN_EMAIL is required}
      WP_SITE_TITLE: ${WP_SITE_TITLE:?WP_SITE_TITLE is required}
    volumes:
      - vb_uploads:/var/www/html/wp-content/uploads
      # Bind mount of the repo root so theme source edits are
      # visible inside the container (per FR-004).
      - .:/var/www/html
    entrypoint: /var/www/html/bin/bootstrap.sh
    command: ["apache2-foreground"]

volumes:
  vb_db:
  vb_uploads:
```

### Note on `version`

The `version` key is **optional** in Compose v2 and emits a
warning when present. The contract permits either inclusion
(`version: "2"`) or omission; the implementer's preference.

## Services

### `db`

| Field | Value | Notes |
|---|---|---|
| `image` | `mysql:8.0` | Matches the spec assumption. |
| `restart` | `unless-stopped` | Survives host reboots. |
| `environment` | `MYSQL_*` | DB credentials from `.env`. |
| `volumes` | `vb_db:/var/lib/mysql` | Persisted named volume. |
| `healthcheck` | `mysqladmin ping` | Used by `wordpress`'s `depends_on` for startup sequencing. |

### `wordpress`

| Field | Value | Notes |
|---|---|---|
| `image` | `wordpress:6` | Matches WP 6.0+ constraint. |
| `ports` | `${HOST_PORT:-8080}:80` | Configurable host port (FR-007). |
| `volumes` | `vb_uploads`, repo root bind | Mounts (E3, E4). |
| `entrypoint` | `bin/bootstrap.sh` | First-run install + activate. |
| `command` | `apache2-foreground` | Default from the official image. |

## Volumes

| Name | Mount | Purpose |
|---|---|---|
| `vb_db` | `db:/var/lib/mysql` | Persisted MySQL data (E2). |
| `vb_uploads` | `wordpress:/var/www/html/wp-content/uploads` | Persisted media uploads (E3). |

## Cross-cutting invariants

1. The WordPress service MUST depend on `db` with
   `condition: service_healthy` so the bootstrap script never sees an
   unreachable database.
2. The host port MUST come from `${HOST_PORT:-8080}`, never
   hardcoded.
3. The repo root MUST be bind-mounted into the container at
   `/var/www/html` so the Vue Blocks theme directory is at
   `/var/www/html/wp-content/themes/vue-blocks` (the WP theme
   install path).
4. The compose file MUST NOT contain plaintext credentials — it
   reads them from `.env` (per FR-008).

## Out-of-scope

- **Production deploys** — no separate compose file; the same file
  is used for local testing.
- **CI** — no CI-specific overrides; `.env.ci.example` may exist
  as documentation but is out of scope for this spec.
- **HTTPS termination** — no Traefik / Caddy / nginx-proxy sidecar.