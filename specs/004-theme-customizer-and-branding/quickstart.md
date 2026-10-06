# Quickstart Validation: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

This file is a runnable validation guide. It enumerates the scenarios
that prove the feature works end-to-end against the acceptance
criteria in the spec.

---

## Scenario A — Fresh install lands with site title "Safe Mídia" (US1 / SC-001)

### Setup

A clean host with Docker; the project's `bin/preflight.sh` runs
cleanly; `.env` has the default `WP_SITE_TITLE`.

### Steps

1. Stop and wipe the harness:

   ```bash
   docker compose down -v
   ```

2. Bring it up:

   ```bash
   docker compose up -d
   ```

3. Wait for the bootstrap to complete:

   ```bash
   docker compose logs -f wordpress
   ```

4. Verify the site title:

   ```bash
   docker compose exec wordpress \
     wp option get blogname --path=/var/www/html --allow-root
   ```

### Expected outcomes

- The command prints exactly: `Safe Mídia`
- The browser's `<title>` on the home page reads "Safe Mídia".
- `wp-admin → Appearance → Themes` still lists the theme as "Vue
  Blocks" (per FR-002).

---

## Scenario B — Google Fonts load on every page (US2 / SC-002)

### Setup

The harness from Scenario A is up; the theme is active.

### Steps

1. Fetch the home page HTML:

   ```bash
   curl -s http://localhost:8080/ -o /tmp/home.html
   ```

2. Extract the Google Fonts links:

   ```bash
   grep -oE '<link[^>]+googleapis[^>]+>' /tmp/home.html | sort -u
   ```

3. Repeat step 1 against any other page (e.g., a published post's
   permalink) and verify the same three `<link>` tags appear.

### Expected outcomes

- The grep output is exactly three lines:

  ```
  <link rel='preconnect' href='https://fonts.googleapis.com' />
  <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin />
  <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Inter:wght@300;400;500;600&display=swap' />
  ```

  (Actual `rel` and attribute order may differ slightly per WP's
  `wp_resource_hint_h` filter output — the URL pattern and the
  presence of `display=swap` are the load-bearing assertions.)

- In a browser, opening the home page shows requests to
  `fonts.googleapis.com` and `fonts.gstatic.com` in the network
  panel; the computed `font-family` of `.logo-text` starts with
  `'Merriweather'`.

---

## Scenario C — Social media Customizer (US3 / SC-003)

### Setup

The harness from Scenario A is up. Log in to `/wp-admin`.

### Steps

1. Open `wp-admin → Appearance → Customize`.
2. Find the "Social media" panel; enter a Facebook URL (e.g.,
   `https://example.com/fb`); click **Publish**.
3. Reload the home page in an incognito window; inspect the navbar's
   Facebook icon.
4. Repeat for Instagram, X, and LinkedIn.
5. Clear the Facebook URL in the Customizer; click **Publish** again.
6. Reload the home page; verify the Facebook icon is now gone.

### Expected outcomes

- The Customizer shows a "Social media" panel with four URL fields.
- After step 2, the rendered HTML on the home page contains exactly
  one `<a class="soc" title="Facebook" href="https://example.com/fb">`
  in the navbar and exactly one `<a class="footer-soc"
  aria-label="Facebook" href="https://example.com/fb">` in the footer.
- After step 5, both anchors are absent from the rendered HTML
  (the icon is not rendered, not rendered with an empty `href`).

```sh
# After step 2
curl -s http://localhost:8080/ \
  | grep -oE '<a[^>]+(soc|footer-soc)[^>]+Facebook[^>]+>' \
  | head
# Expected: two lines (one .soc, one .footer-soc), both with
# href="https://example.com/fb"

# After step 5
curl -s http://localhost:8080/ \
  | grep -E 'soc|footer-soc' | grep Facebook
# Expected: no output
```

---

## Scenario D — Layouts inventory document (US4 / SC-004)

### Setup

The packaged theme has been built:

```bash
bash theme/package.sh
```

### Steps

1. Open `theme/DESIGN_LAYOUTS.md` in the repo and confirm it lists
   the home page entry with the four core Safe Mídia sections
   (navbar, hero, news grid, footer).
2. Open the zip and verify `theme/DESIGN_LAYOUTS.md` is included:

   ```sh
   unzip -l vue-blocks-*.zip | grep DESIGN_LAYOUTS
   ```

### Expected outcomes

- `theme/DESIGN_LAYOUTS.md` lists every entry under `layouts-html/`
  with title, v1 status, and sections present.
- The home page entry explicitly lists navbar, hero, news grid,
  footer as the four implemented sections.
- The zip includes the inventory file (it ships with the theme
  per the spec's clarification Q2).

---

## Scenario E — Package size and exclusion (SC-005)

### Steps

```bash
bash theme/package.sh
ls -lh vue-blocks-*.zip
unzip -l vue-blocks-*.zip | awk '/^[ ]+[0-9]+/ {print $NF}' | grep -E '\.zip$|package\.sh$|PACKAGE\.md$|docker-compose|README\.md$'
```

### Expected outcomes

- The zip is ≤ 5 MB.
- The grep at the end returns no results (no test-harness paths
  leaked; `DESIGN_LAYOUTS.md` IS included — it ships with the
  theme).

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)