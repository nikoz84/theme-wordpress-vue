# Quickstart Validation: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

This file is a runnable validation guide. It enumerates the scenarios
that prove the feature works end-to-end against the acceptance
criteria in the spec.

---

## Scenario A — Repo contains DESIGN.md with the documented shape (US1 / SC-001)

### Setup

A clean checkout of the repository, with the Docker harness stopped
(`docker compose down -v` if previously running).

### Steps

1. Open `DESIGN.md` at the repo root.
2. Verify it contains five sections (Design tokens, Components,
   Layout patterns, Responsive breakpoints, Maintenance).
3. Verify the Design tokens section lists at least 20 tokens.
4. Verify the Components section documents at least 8 components.
5. Verify the Responsive breakpoints section documents at least 2
   breakpoints.

### Expected outcomes

- The file exists at the repo root.
- The token table contains all the tokens in
  `contracts/design-tokens.schema.md`.
- The component catalog lists navbar, hero-main, hero-aside,
  news-card, site-footer, logo, button, social.

---

## Scenario B — Theme files live under `theme/` and the harness keeps working (US2 / SC-005, SC-006)

### Setup

The repo after the restructuring.

### Steps

1. List the contents of `theme/`:

   ```bash
   ls theme/
   ```

2. List the contents of the repo root, EXCLUDING `theme/` and
   test-harness paths:

   ```bash
   ls | grep -v -E '^(theme|specs|\.specify|\.opencode|node_modules|dist)$'
   ```

3. Verify the Docker harness still boots:

   ```bash
   docker compose down -v
   docker compose up -d
   docker compose logs -f wordpress
   ```

### Expected outcomes

- `theme/` exists and contains `style.css`, `functions.php`, all
  template files, `assets/`, `template-parts/`, `inc/`, `languages/`,
  `screenshot.png`, `readme.txt`, `DESIGN.md`, `PACKAGE.md`,
  `package.sh`.
- The repo root contains `DESIGN.md`, `docker-compose.yml`,
  `.env.example`, `bin/`, `seed/`, `config/`, `README.md`.
- `docker compose up -d` boots successfully and the bootstrap
  completes (log shows "Vue Blocks bootstrap complete.").

---

## Scenario C — The packaged zip installs cleanly into a fresh WordPress site (US2 / SC-003)

### Setup

A second host with a fresh WordPress 6.0+ / PHP 7.4+ site at
`/var/www/html/`, no themes installed (or another theme installed
but the Vue Blocks one absent).

### Steps

1. From the repo root of the working repo, build the zip:

   ```bash
   bash theme/package.sh
   ```

2. The script produces `<theme-slug>-<version>.zip` in the repo
   root. Copy it to the test host:

   ```bash
   scp vue-blocks-1.0.0.zip test-host:/tmp/
   ```

3. On the test host, install:

   ```bash
   unzip /tmp/vue-blocks-1.0.0.zip -d /var/www/html/wp-content/themes/vue-blocks
   ```

4. Activate the theme in `wp-admin → Appearance → Themes`.

### Expected outcomes

- The package is **at most 5 MB** in size (SC-002).
- The package contains **no test-harness paths** (SC-006); verify
  with:

  ```bash
  unzip -l vue-blocks-1.0.0.zip | grep -E '(node_modules|dist|docker-compose|bin/bootstrap|specs/|package\.sh)'
  ```

  → No matches.

- The theme appears in the Themes list with the Safe Mídia visual
  language applied to `style.css`.
- No PHP errors, warnings, or missing-file notices in the browser
  console on the home page.

---

## Scenario D — The home page renders the four core sections (US3 / SC-004)

### Setup

A fresh WordPress site with the packaged theme activated and at
least 6 published posts (each with a featured image) and a primary
menu configured.

### Steps

1. Open the home page in a browser.
2. Visually compare to
   `layouts-html/01 - Home/01 - safemidia-home-fixed.html` —
   the navbar, hero (with sidebar posts), news grid, footer should
   match in section order and visual hierarchy.
3. Resize the browser to tablet width (≤1024px) and confirm the
   layout reflows per `DESIGN.md` § Responsive breakpoints.
4. Resize to mobile width (≤768px) and re-confirm.
5. Resize to small mobile (≤420px) and re-confirm.

### Expected outcomes

- The four core sections render in the documented order.
- The seven deferred sections (newsletter compact, "Para o Segurado",
  Boletim Regulatório, Colunistas, Análise de Mercado, category
  tabs, "Mais Lidas da Semana", newsletter grande) are NOT in v1 and
  MUST NOT appear (per FR-007).
- Reflow at each breakpoint matches `DESIGN.md`.

---

## Scenario E — Local iteration via Docker keeps working (US2 / SC-005)

### Setup

The harness is up (`docker compose up -d`); the theme is mounted from
`./theme`.

### Steps

1. Edit a CSS custom property in `:root` of `theme/style.css`.
2. Refresh the home page in the browser.
3. Verify the change reflects immediately (no container restart).

### Expected outcomes

- The change reflects on the next page refresh (the existing
  bind-mount + opcache configuration continues to work post-
  restructure).

---

## Scenario F — Package contents clean (SC-006)

### Steps

After `bash theme/package.sh`:

```bash
unzip -l vue-blocks-1.0.0.zip | grep -E '(node_modules|dist|docker-compose|bin/bootstrap|seed/|config/|specs/|package\.sh|PACKAGE\.md|README\.md$)'
```

### Expected outcomes

- No matches — the zip is clean of test-harness paths and meta
  files (per `contracts/theme-package.manifest.md`).

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)