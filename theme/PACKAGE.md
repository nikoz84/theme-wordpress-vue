# Theme Package

This directory is the **packaged deliverable**: it is what
`theme/package.sh` zips into a `.zip` archive that uploads to any
WordPress 6.0+ / PHP 7.4+ site's `wp-content/themes/` directory.

## Contents (what ships)

See `specs/003-design-system-foundation/contracts/theme-package.manifest.md`
for the full manifest. Required runtime files: `style.css`,
`functions.php`, all template files (`index.php`, `front-page.php`,
`header.php`, `footer.php`, …), `template-parts/`, `inc/`,
`assets/`, `languages/`, `screenshot.png`, `readme.txt`, `DESIGN.md`.

## Excluded (does NOT ship)

`docker-compose.yml`, `.env*`, `bin/`, `seed/`, `config/`,
`README.md`, `package.json`, `vite.config.js`, `package.sh`,
`PACKAGE.md` (and other meta files at the repo root).

## Build

```bash
cd theme && bash package.sh
```

Output: `vue-blocks-<version>.zip` in the repo root.

## Canonical source

The canonical design-system reference is `../DESIGN.md` at the repo
root. The in-theme `DESIGN.md` is a duplicate refreshed at packaging
time.