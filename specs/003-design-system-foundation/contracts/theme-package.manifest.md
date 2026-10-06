# Theme Package Manifest: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

Defines the contents of the `.zip` archive produced by
`theme/package.sh`.

## Required contents (MUST be present)

```
style.css
functions.php
index.php
front-page.php
header.php
footer.php
sidebar.php
page.php
single.php
archive.php
search.php
searchform.php
404.php
comments.php
screenshot.png
readme.txt
DESIGN.md
assets/js/app.js
template-parts/
inc/
languages/
```

## Excluded content (MUST NOT appear)

```
node_modules/
dist/
package.json
vite.config.js
package.sh
PACKAGE.md
docker-compose.yml
.env
.env.example
.gitignore
.dockerignore
bin/
seed/
config/
README.md
.specify/
.specify/
specs/
.opencode/
```

The `DESIGN.md` is the **one** documentation file included; the
canonical copy lives at the repo root and is mirrored into
`theme/DESIGN.md` at packaging time (per `theme/package.sh`).

## Output naming

Default zip name: `<theme-slug>-<version>.zip`

For the v1 release: `vue-blocks-1.0.0.zip`

The version MUST match the `Version:` header in `theme/style.css`
and the `VB_VERSION` constant in `theme/functions.php`.

## Validation

A post-build `grep` over `unzip -l <archive>` MUST succeed:

```sh
unzip -l "${ZIP_PATH}" | grep -E '(node_modules|dist|docker-compose\.yml|^\.env$|bin/bootstrap\.sh|package\.sh|PACKAGE\.md|specs/|README\.md$)' \
  && fail "Package contains excluded paths; aborting." \
  || log "Package contents clean."
```

## Source distribution

The package is built from `./theme/` at the repo root. The Docker
harness at the repo root mounts `./theme` to
`/var/www/html/wp-content/themes/vue-blocks` so contributors can test
the package locally without first zipping it.

## Out-of-scope

- A separate "dev" archive that includes source maps or test files
  — the packaged theme is for distribution, not development.
- Marketplace-ready metadata (screenshots, version metadata in
  `readme.txt` is already correct for distribution via the WordPress
  theme directory).