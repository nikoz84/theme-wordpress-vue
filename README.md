# Vue Blocks — Docker Testing Environment

This directory ships a self-contained Docker Compose stack that boots a
local WordPress site with the Vue Blocks theme installed, activated,
and seeded with sample content. No host-side tooling is required
beyond Docker.

## Prerequisites

- Docker Engine 24+ (Linux) or Docker Desktop 4.x+ (macOS / Windows
  with WSL2).

## Quickstart

```bash
cp .env.example .env
# Open .env in your editor and replace WP_ADMIN_PASSWORD + WP_DB_PASSWORD
# (the values in .env.example are TEST-ONLY placeholders).

docker compose up -d
```

Open <http://localhost:8080> in a browser. The Vue Blocks homepage
should render with the seeded sample posts. Log in to the admin at
<http://localhost:8080/wp-admin> with the credentials from `.env`.

Cold start (image pull, WordPress install, theme activation, sample
content import) is bounded at **5 minutes** per SC-001. A subsequent
`docker compose up` with cached images and existing volumes is bounded
at **30 seconds** per SC-002.

## Teardown

```bash
docker compose down -v
```

Removes the WordPress and DB containers **and** the named volumes
(`vb_db`, `vb_uploads`). After this, the next `docker compose up`
produces a pristine install identical to the first run (per
quickstart Scenario I).

To stop without losing data, omit the `-v`:

```bash
docker compose down
```

## Troubleshooting

- **"Docker is not running. Start Docker and try again."**
  → Start Docker Desktop (or `systemctl start docker` on Linux) and
  retry.

- **"HOST_PORT=N is already in use on the host."**
  → Stop the conflicting process, OR set a different `HOST_PORT` in
  `.env` (must be ≥ 1024) and retry.

- **"HOST_PORT must be >= 1024."**
  → Per FR-013 the default site port must be non-privileged. Choose a
  value like 8080, 8000, or 8888.

- **"WP_ADMIN_PASSWORD is required"** or **"WP_DB_PASSWORD is required"**
  → Copy `.env.example` to `.env` and set both password values.
  `.env` is gitignored; only `.env.example` is committed.

- **Theme edits don't show up after a CSS / PHP / JS change.**
  → Refresh the page in your browser. PHP opcache is configured to
  revalidate on every request (see `config/php.ini`). If a PHP change
  still doesn't appear, clear your browser cache and retry.

- **Container won't start; logs show "bind: address already in use."**
  → Same as the port-collision message above — change `HOST_PORT`.

## Validation scenarios

The full set of validation scenarios is in
[`specs/001-wordpress-docker-testing/quickstart.md`](specs/001-wordpress-docker-testing/quickstart.md).
The 9 scenarios cover cold boot, warm boot, live edits, persistence,
reproducibility, idempotent bootstrap re-run, port collision,
missing Docker, and clean teardown.

## File map

| File | Role |
|---|---|
| `DESIGN.md` | Canonical design-system reference (tokens, components, layout, breakpoints). The human-readable mirror of `theme/style.css`'s `:root` block. |
| `theme/` | The packaged WordPress theme. All runtime files live here. `bash theme/package.sh` produces `vue-blocks-<version>.zip`. |
| `theme/DESIGN.md` | Duplicate of the repo-root `DESIGN.md` (refreshed at packaging time by `theme/package.sh`). |
| `theme/PACKAGE.md` | One-paragraph pointer to the packaged deliverable's contents and the contracts. |
| `theme/package.sh` | Packaging script: builds `<slug>-<version>.zip`, runs a post-build leakage grep. |
| `docker-compose.yml` | Compose v2 stack (db + wordpress services, named volumes). Mounts `./theme` → WP theme directory. |
| `.env.example` | Documented env-var defaults (committed). |
| `bin/bootstrap.sh` | WordPress container entrypoint: install, admin user, theme activation, seed import. Idempotent. |
| `bin/preflight.sh` | Host-side preflight check: confirms Docker is running before the contributor hits a cryptic engine error. |
| `seed/sample-content.xml` | WXR with 3 sample posts + featured images + a primary menu (per FR-014). |
| `config/php.ini` | Opcache revalidation policy so theme-source PHP edits reflect on the next request. |

## Design system

Open `DESIGN.md` for the canonical design tokens (Safe Mídia palette,
typography, spacing, radii, shadows), the 8 documented components
(navbar, hero-main, hero-aside, news-card, site-footer, logo, button,
social), the home-page layout patterns, and the responsive
breakpoints.

`theme/style.css`'s `:root` block is the machine-readable source of
truth for token values. `DESIGN.md` mirrors it; the in-theme
`theme/DESIGN.md` is a duplicate refreshed at packaging time by
`theme/package.sh`.

## Packaging the theme

```bash
bash theme/package.sh
```

The script refreshes `theme/DESIGN.md` from the canonical repo-root
copy, builds `vue-blocks-<version>.zip` at the repo root, runs a
post-build grep to confirm no test-harness paths leaked in
(`docker-compose.yml`, `bin/`, `seed/`, `config/`, `node_modules/`,
`dist/`, etc.), and verifies the archive is ≤ 5 MB. The resulting
file uploads directly to any WordPress 6.0+ / PHP 7.4+ site's
`wp-content/themes/` directory.