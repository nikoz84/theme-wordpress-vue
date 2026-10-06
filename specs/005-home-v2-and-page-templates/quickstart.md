# Quickstart Validation: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

This file is a runnable validation guide.

---

## Setup prerequisites

- The Docker harness is up: `docker compose up -d`.
- The theme is active with seeded content (the previous test
  harness's WXR import provides 3 sample posts; this feature adds
  more for full coverage).
- The "Colunistas" WordPress category exists (auto-created on
  activation).

---

## Scenario A — All 12 Safe Mídia sections render on the home page (US1 / SC-001)

### Setup

Activate the theme and ensure seeded content (≥6 published posts
across ≥3 categories; the existing Docker harness's WXR provides
3; seed more as needed for category coverage).

### Steps

1. Fetch the home page HTML:

   ```bash
   curl -s http://localhost:8080/ > /tmp/home.html
   ```

2. Verify each of the eight new section class names is present:

   ```bash
   for cls in \
     'nl-compact-block' \
     'segurado-block' \
     'nl-block' \
     'analise-layout' \
     'tabs-section' \
     'maislist-section' \
     'boletim-section' \
     'colunistas-section'; do
     if ! grep -q "class=\"${cls}\"" /tmp/home.html; then
       echo "MISSING: ${cls}"
     fi
   done
   ```

3. Verify the section order matches the contract:

   ```bash
   for cls in \
     'navbar' 'hero' 'news-section' \
     'nl-compact-block' 'segurado-block' 'nl-block' 'analise-layout' \
     'tabs-section' 'maislist-section' 'boletim-section' 'colunistas-section' \
     'site-footer'; do
     grep -n "class=\"${cls}\"" /tmp/home.html | head -1
   done
   ```

   The line numbers MUST increase through the list (sections render
   in the documented order).

### Expected outcomes

- All eight grep counts are ≥ 1.
- The line numbers strictly increase through the section list.

---

## Scenario B — Single post renders with the full Safe Mídia editorial (US2 / SC-002)

### Setup

The theme is active; at least one published post exists with a
featured image and a known category.

### Steps

1. Fetch a single-post permalink (use `wp post list --field=ID` to
   find one):

   ```bash
   POST_ID=$(docker compose exec wordpress wp post list \
     --post_type=post --post_status=publish --posts_per_page=1 \
     --has_thumbnail=true --format=ids)
   curl -s "http://localhost:8080/?p=${POST_ID}" > /tmp/single.html
   ```

2. Verify the editorial structure:

   ```bash
   grep -q 'class="post-hero"' /tmp/single.html
   grep -q 'class="entry-content"' /tmp/single.html
   grep -q 'class="related-posts"' /tmp/single.html
   ```

3. Verify the typography via curl + grep:

   ```bash
   curl -s "http://localhost:8080/?p=${POST_ID}" \
     | grep -oE 'class="post-title[^"]*"' | head -1
   # Verify the post-title is inside the post-hero and uses Merriweather
   # (the SC-002 acceptance test for typography happens in the browser
   # DevTools; this smoke test confirms the markup is correct)
   ```

### Expected outcomes

- All three grep checks pass.
- The single-post page renders with hero + body + related-posts rail.

---

## Scenario C — Colunistas category auto-created on activation (US4 / FR-004a / SC-004)

### Setup

A fresh Docker volume (wipe `vb_db` / `vb_uploads`) and a fresh
activation.

### Steps

1. Wipe and re-up:

   ```bash
   docker compose down -v
   docker compose up -d
   ```

2. Wait for the bootstrap to complete; then check:

   ```bash
   docker compose exec wordpress wp term list category \
     --field=slug,name | grep -E '^colunistas\b'
   ```

### Expected outcomes

- `colunistas` is listed (slug = `colunistas`, name = `Colunistas`).

---

## Scenario D — Archive / search / 404 templates render with Safe Mídia typography (US3 / SC-003)

### Steps

```bash
curl -s http://localhost:8080/?cat=1 > /tmp/cat.html
curl -s 'http://localhost:8080/?s=nonexistent_query_xyz' > /tmp/search.html
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/this/does/not/exist
```

Then:

```bash
# Archive uses the Safe Mídia .ncard pattern
grep -q 'class="ncard"' /tmp/cat.html
# Search renders the Safe Mídia typography
grep -q 'class="entry"' /tmp/search.html || grep -q 'nenhum resultado\|no results\|Nenhum' /tmp/search.html
```

### Expected outcomes

- Archive page uses Safe Mídia `.ncard` markup.
- Search page renders Safe Mídia typography and a "no results"
  message for zero matches.

---

## Scenario E — Package size and no test-harness leakage (SC-005)

### Steps

```bash
bash theme/package.sh
ls -lh vue-blocks-*.zip
unzip -l vue-blocks-*.zip \
  | awk '/^[ ]+[0-9]+/ {print $NF}' \
  | grep -E '(node_modules|dist/|docker-compose|bin/bootstrap|seed/|config/|specs/|package\.sh|PACKAGE\.md|README\.md$)'
```

### Expected outcomes

- The zip is ≤ 5 MB.
- The grep at the end returns no results — the packaged theme
  contains only runtime files (the eight new home template parts
  ship inside the package).

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)