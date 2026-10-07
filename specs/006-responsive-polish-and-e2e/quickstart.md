# Quickstart Validation: Home-Page Responsive Polish & Playwright E2E

**Date**: 2026-10-06
**Spec**: `specs/006-responsive-polish-and-e2e/spec.md`
**Branch**: `006-responsive-polish-and-e2e`

This file is a runnable validation guide.

---

## Setup prerequisites

- The Docker harness is up: `docker compose up -d` (or via the
  existing `bin/preflight.sh`).
- Node.js 20+ at the repo root.
- No global Playwright install required — `npx playwright install
  chromium` downloads Chromium on demand.

---

## Scenario A — Breakpoint reflow (US1, FR-001..FR-012, SC-001)

### Steps

```bash
# Manual visual verification at the 5 viewports
curl -sS http://localhost:8080/ -o /tmp/home.html
for sel in '.navbar' '.hero' '.news-grid' '.nl-compact-block' \
           '.segurado-block' '.nl-block' '.analise-layout' \
           '.tabs-section' '.maislist-section' '.boletim-section' \
           '.colunistas-section' '.site-footer'; do
  count=$(grep -c "$sel" /tmp/home.html 2>/dev/null || echo 0)
  echo "  $sel: $count"
done
# Each of the 12 classes must be present at least once.
```

Or automated via the e2e suite:

```bash
npm install
npx playwright install chromium
npm run test:e2e -- no-overflow.spec.ts
```

### Expected outcomes

- Each of the 12 class names appears at least once in the home page
  HTML (one per section).
- The Playwright `no-overflow.spec.ts` test exits 0; the manual
  curl loop reports 12 non-zero counts.

---

## Scenario B — Navbar brand is "Safe Mídia" (US2 / FR-008)

### Steps

```bash
curl -sS http://localhost:8080/ | grep -E 'class="logo-text"' | head -1
```

Or automated:

```bash
npm run test:e2e -- navbar-brand.spec.ts
```

### Expected outcomes

- The grep shows: `<span class="logo-text">Safe <span>Mídia</span></span>`.
- The Playwright test asserts `.logo-text`'s `textContent` is "Safe Mídia".

---

## Scenario C — `<title>` is decoupled from the navbar brand (US2 / FR-009)

### Steps

```bash
# 1. Document <title> at default (blogname = "Safe Mídia"):
curl -sS http://localhost:8080/ | grep -oE '<title>[^<]+' | head -1

# 2. Set the Customizer's Site title to "Anything Here":
docker compose exec wordpress wp --allow-root option update blogname 'Anything Here'

# 3. Document <title> again:
curl -sS http://localhost:8080/ | grep -oE '<title>[^<]+' | head -1

# 4. Confirm the navbar brand stays "Safe Mídia":
curl -sS http://localhost:8080/ | grep -E 'class="logo-text"' | head -1

# 5. Reset:
docker compose exec wordpress wp --allow-root option update blogname 'Safe Mídia'
```

Or automated (the full sequence) via:

```bash
npm run test:e2e -- title-decoupling.spec.ts
```

### Expected outcomes

- Step 1: `<title>Safe Mídia`.
- Step 3: `<title>Anything Here`.
- Step 4: `<span class="logo-text">Safe <span>Mídia</span></span>` (unchanged).
- The Playwright test exits 0 and verifies all three assertions.

---

## Scenario D — Long-title truncation (US3 / FR-013's referenced check)

### Steps

```bash
npm run test:e2e -- long-title.spec.ts
```

### Expected outcomes

- A draft post with 100 × `'A'` title is created via WP REST.
- The home page renders the post card with the long title (placeholder
  image, non-zero offsetHeight of the title element).
- After the test, the post is deleted via WP REST.
- The Playwright test exits 0.

---

## Scenario E — Theme name "Vue Blocks" preserved (US2 / FR-008 acceptance 3)

### Steps

```bash
grep -E '^Theme Name:' theme/style.css
```

### Expected outcomes

- The grep prints: `Theme Name:        Vue Blocks` (the theme's WP
  identifier remains unchanged; only the navbar's visible brand
  string changed from "Vue Blocks" to "Safe Mídia").

---

## Scenario F — `:root` tokens and `DESIGN.md` are byte-identical (SC-005)

### Steps

```bash
# Before:
git show HEAD:theme/style.css | sha256sum
git show HEAD:DESIGN.md | sha256sum

# After this feature lands:
sha256sum theme/style.css
sha256sum DESIGN.md
```

### Expected outcomes

- The `:root` block of `theme/style.css` and `DESIGN.md` are
  byte-identical before and after (only breakpoint-specific
  overrides are added at the bottom of `style.css`).

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Contracts: [`contracts/`](.)
- Research: [`research.md`](../research.md)