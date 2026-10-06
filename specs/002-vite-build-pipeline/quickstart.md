# Quickstart Validation: Vite Build Pipeline for Vue Blocks

**Date**: 2026-10-06
**Spec**: `specs/002-vite-build-pipeline/spec.md`
**Branch**: `002-vite-build-pipeline`

This file is a runnable validation guide. It enumerates the scenarios
that prove the feature works end-to-end against the acceptance
scenarios in the spec. Implementation details live in `tasks.md` and
the source files; this file stays at the "what to run, what to
observe" level.

---

## Prerequisites

- WordPress instance running with the Vue Blocks theme installed (or
  a fresh clone of the repository as the WordPress theme directory
  under `wp-content/themes/vue-blocks`).
- For the **build** scenarios: Node.js (active LTS line; declared in
  the theme's `package.json#engines.node`).
- For the **runtime check** scenarios: a browser.

---

## Scenario A — CDN-first default (Story 2 / SC-002)

This is the **regression guard** for end users (site owners). It
MUST pass at every commit and is the most important test of the
whole feature.

### Setup

A fresh checkout of the repository with **only the tracked files**
(no `node_modules/`, no `dist/`):

```bash
git clone https://github.com/nikoz84/theme-wordpress-vue.git
cd theme-wordpress-vue
ls -la
# confirm there is NO dist/ directory and NO node_modules/
```

### Steps

1. Copy or symlink the repository to a WordPress install as
   `wp-content/themes/vue-blocks`.
2. Activate the theme in `wp-admin → Appearance → Themes`.
3. Open the site's homepage in a browser.
4. Open the browser's developer tools and view the network panel.
5. Reload the page.

### Expected outcomes

- The page renders the Vue Blocks homepage (PHP-rendered, not a
  blank or stock theme).
- The mobile menu toggle and the dark-mode toggle are interactive
  (Vue is active).
- The network panel shows:
  - One `vue.global.prod.js` request to `unpkg.com` (or whatever
    `https://unpkg.com/vue@3.x.x/dist/vue.global.prod.js` resolves
    to at the time of testing).
  - One `app.js` (or `vue-blocks-app`) request to the local theme's
    `assets/js/app.js`.
- **No requests to any path under `dist/`.**

If any `dist/` request appears, the feature has broken the
CDN-first guarantee and the change MUST be reverted.

---

## Scenario B — Build produces expected artifacts (Story 1 / SC-001, SC-004, SC-006)

### Setup

```bash
git clone https://github.com/nikoz84/theme-wordpress-vue.git
cd theme-wordpress-vue
npm install
```

### Steps

1. Run the documented build command:

   ```bash
   npm run build
   ```

2. Inspect the output directory:

   ```bash
   ls -la dist/
   ls -la dist/assets/   # if assets/ is the chosen subdirectory
   ```

3. Verify `dist/manifest.json` parses as the contract
   ([`contracts/manifest.schema.json`](contracts/manifest.schema.json)):

   ```bash
   node -e "JSON.parse(require('fs').readFileSync('dist/manifest.json','utf8'))"
   ```

4. Verify the build is gitignored:

   ```bash
   git check-ignore -v dist/ dist/manifest.json dist/assets/app.*.js
   # all paths MUST report "dist/" or its children as ignored
   ```

### Expected outcomes

- `dist/` contains:
  - `manifest.json`
  - One JS bundle with content-hashed filename (e.g.,
    `app.8f3a2b.js`).
  - One CSS bundle with content-hashed filename (e.g.,
    `style.4e7c91.css`).
  - Matching `.map` files for each.
- `npm run build` completes:
  - In ≤ 30 seconds (warm; dependencies already present).
  - With no errors or warnings on stderr.
- All listed files are `.gitignore`d.

---

## Scenario C — Opt-in switch redirects enqueue (Story 1, scenario 2)

### Setup

- `dist/` is populated per Scenario B.
- The theme's `functions.php` (or a child theme's bootstrap) sets
  `VB_USE_BUNDLED_ASSETS = true`.

### Steps

1. Reload the site's homepage in a browser with the network panel
   open.
2. Click the dark-mode toggle and the mobile menu toggle to
   confirm Vue is alive.
3. Inspect the HTML source's `<script>` and `<link>` tags for the
   `wp_enqueue_*` outputs.

### Expected outcomes

- The page renders identically to the CDN-first path (no visual
  regression).
- The network panel shows:
  - **No** request to `unpkg.com/vue`.
  - One request to the theme's `dist/assets/app.[hash].js`.
  - One request to the theme's `dist/assets/style.[hash].css`.
  - The hashed filenames MUST match the entries in `dist/manifest.json`.
- **No** request to any `.map` file (source maps are not enqueued).

---

## Scenario D — Loud failure when manifest is missing (FR-011)

This validates the loud-failure rule when the opt-in switch is on but
the build has not been run.

### Setup

- `dist/` is **absent** (or `dist/manifest.json` is deleted).
- `VB_USE_BUNDLED_ASSETS = true` is set.

### Steps

1. Reload the site's homepage.
2. Observe the response.

### Expected outcomes

- The site MUST NOT render as if the CDN-first fallback applied
  (per FR-011: "no silent fallback to a stale or empty `dist/`").
- An actionable error MUST be visible (PHP `trigger_error()` →
  displayed when `WP_DEBUG` is on, or a fatal `wp_die()` off
  debug; the implementation choice is the implementer's).
- The error MUST mention `dist/manifest.json` and MUST tell the
  contributor either to run `npm run build` or to set
  `VB_USE_BUNDLED_ASSETS = false`.

---

## Scenario E — Source edits flow into the build (Story 3 / SC-003)

### Setup

- A clean build has been produced (Scenario B completed).

### Steps

1. Edit a small but visible string inside `assets/js/app.js`
   (e.g., change one `i18n.toggleTheme` value).
2. Edit a small but visible CSS custom property in `:root` in
   `style.css` (e.g., `--vb-primary`).
4. Run `npm run build` again.
5. Inspect `dist/manifest.json` and the new artifact filenames.

### Expected outcomes

- The new JS bundle's content hash differs from the previous one.
- The new CSS bundle's content hash differs from the previous one.
- `dist/manifest.json` now references the new hashed filenames.
- The previous hashed files have been replaced (no leftover stale
  artifacts in `dist/`).
- The edits are visible in the new artifacts (e.g., a `grep` for
  the new `--vb-primary` value inside the CSS bundle finds it).

---

## Scenario F — Fresh clone with no build still works (SC-002, regression)

Re-run **Scenario A** after pulling the latest commits. This MUST
continue to pass; if it does not, the feature has broken the
CDN-first guarantee.

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)