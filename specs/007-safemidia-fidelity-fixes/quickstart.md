# Quickstart Validation: Safe Mídia Visual Fidelity Fixes

**Date**: 2026-10-06
**Spec**: `specs/007-safemidia-fidelity-fixes/spec.md`
**Branch**: `007-safemidia-fidelity-fixes`

This file is a runnable validation guide.

---

## Setup prerequisites

- The Docker harness is up: `docker compose up -d`.
- `VB_TEST_SHIM=1` is set in `docker-compose.yml` (default).
- Seeded content is present (≥ 4 published posts so the
  "Para o Segurado" section renders).

---

## Scenario A — "Para o Segurado" Safe Mídia markup (US1 / SC-001)

### Steps

```bash
# Manual visual
curl -sS http://localhost:8080/ -o /tmp/h.html
echo "=== .segurado-hd present? ==="
grep -c 'class="segurado-hd"' /tmp/h.html
echo "=== .segurado-title ==="
grep -oE 'class="segurado-title">[^<]+' /tmp/h.html | head -1
echo "=== .segurado-sub ==="
grep -oE 'class="segurado-sub">[^<]+' /tmp/h.html | head -1
echo "=== .segurado-btn-all ==="
grep -oE 'class="segurado-btn-all"[^>]*href="[^"]*"' /tmp/h.html | head -1

# Automated
npm run test:e2e -- segurado-markup.spec.ts
```

### Expected outcomes

- `.segurado-hd` count: 1 (the wrapper is present).
- `.segurado-title` reads exactly "Para o Segurado".
- `.segurado-sub` contains "Direitos, dicas e orientações para quem
  já tem ou quer contratar um seguro".
- `.segurado-btn-all` href is a non-empty, non-`#` URL.
- The Playwright `segurado-markup.spec.ts` exits 0.

---

## Scenario B — Footer brand is "Safe Mídia", independent of Site title (US2 / SC-002)

### Steps

```bash
# Manual
echo "=== Default state ==="
curl -sS http://localhost:8080/ -o /tmp/h.html
grep -oE 'class="footer-bottom">[^<]+' /tmp/h.html | head -1

# Mutate blogname via the test shim
curl -sS -X POST -H "Content-Type: application/json" \
  -d '{"blogname":"Anything Here"}' \
  http://localhost:8080/wp-json/vue-blocks/v1/test-set-blogname

echo "=== After mutating Site title ==="
curl -sS http://localhost:8080/ -o /tmp/h.html
grep -oE 'class="footer-bottom">[^<]+' /tmp/h.html | head -1

# Restore
curl -sS -X POST -H "Content-Type: application/json" \
  -d '{"blogname":"Safe Mídia"}' \
  http://localhost:8080/wp-json/vue-blocks/v1/test-set-blogname

# Automated
npm run test:e2e -- title-decoupling.spec.ts
```

### Expected outcomes

- Default state: `footer-bottom` contains "© YYYY Safe Mídia".
- After mutating blogname to "Anything Here":
  `<title>` updates to "Anything Here"; `footer-bottom` STILL
  contains "© YYYY Safe Mídia".
- The Playwright `title-decoupling.spec.ts` exits 0.

---

## Scenario C — Suite coverage (US3 / SC-003)

### Steps

```bash
npm run test:e2e
```

### Expected outcomes

- All 8 (or 9, if the new specs add to the count) tests pass.
- The suite covers the "Para o Segurado" markup and the footer-brand
  decoupling.

---

## Scenario D — `:root` tokens and `DESIGN.md` unchanged (SC-004)

### Steps

```bash
# Compare before/after
git show HEAD:theme/style.css | sha256sum
sha256sum theme/style.css

git show HEAD:DESIGN.md | sha256sum
sha256sum DESIGN.md
```

### Expected outcomes

- The two sha256 sums match for `theme/style.css`'s `:root` block
  (only the new markup / behavior was added; no token change).
- The two sha256 sums match for `DESIGN.md`.

---

## Related documents

- Spec: [`spec.md`](../spec.md)
- Plan: [`plan.md`](../plan.md)
- Data model: [`data-model.md`](../data-model.md)
- Research: [`research.md`](../research.md)