# Home Sections Contract: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

Defines the shape, data source, and empty-state rule for each of
the eight new home-page sections.

## Section composition order

The home page composes the sections in this order (matching the Safe
Mídia reference HTML):

1. Navbar (v1, unchanged)
2. Hero (v1, unchanged)
3. News grid (v1, unchanged)
4. **Newsletter Compact** (NEW)
5. **Para o Segurado** (NEW)
6. **Newsletter Grande** (NEW)
7. **Análise de Mercado** (NEW)
8. **Category tabs** (NEW)
9. **Mais Lidas da Semana** (NEW)
10. **Boletim Regulatório** (NEW)
11. **Colunistas** (NEW)
12. Footer (v1, unchanged)

## Per-section shape

### 4. Newsletter Compact

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/newsletter-compact.php` |
| Data source | None |
| Empty state | Always rendered |
| CSS class | `.nl-compact-block` (Safe Mídia HTML) |

### 5. Para o Segurado

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/segurado.php` |
| Data source | `WP_Query` for 4 most recent posts excluding the hero + sidebar (category `segurado` if it exists, else fallback to most recent) |
| Empty state | Section hidden if 0 posts |
| CSS class | `.segurado-block`, `.segurado-grid`, `.scard` |

### 6. Newsletter Grande

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/newsletter-grande.php` |
| Data source | None |
| Empty state | Always rendered |
| CSS class | `.nl-block`, `.nl-perks`, `.perk` |

### 7. Análise de Mercado

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/analise.php` |
| Data source | 1 featured + 4-list from category `analise` (fallback to tag `analise`) |
| Empty state | Section hidden if no posts |
| CSS class | `.analise-layout`, `.analise-main`, `.analise-list`, `.aitem` |

### 8. Category tabs

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/category-tabs.php` |
| Data source | 4 most populated categories (excluding Uncategorized); each tab shows 4 latest posts |
| Empty state | Section hidden if fewer than 2 categories exist |
| CSS class | `.tabs-section`, `.tabs-nav`, `.tab-btn`, `.tabs-grid`, `.tab-card` |

### 9. Mais Lidas da Semana

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/mais-lidas.php` |
| Data source | 8 most recent posts (any category) |
| Empty state | Section hidden if no posts |
| CSS class | `.maislist-section`, `.mais-layout`, `.mais-item`, `.mais-num`, `.mais-thumb` |

### 10. Boletim Regulatório

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/boletim.php` |
| Data source | Posts in category `regulatorio` (fallback to posts tagged `regulatorio`) |
| Empty state | Section hidden if no posts |
| CSS class | `.boletim-section`, `.boletim-block`, `.boletim-item`, `.boletim-tipo`, `.boletim-tipo.circular`, `.boletim-tipo.resolucao`, `.boletim-tipo.consulta`, `.boletim-tipo.portaria` |

### 11. Colunistas

| Attribute | Value |
|---|---|
| Template part file | `theme/template-parts/home/colunistas.php` |
| Data source | Distinct authors in category `colunistas` (per data-model E2) |
| Empty state | Section hidden per FR-004 (0 posts) |
| CSS class | `.colunistas-section`, `.col-grid`, `.col-card`, `.col-avatar`, `.col-name`, `.col-role`, `.col-tag`, `.col-article-title`, `.col-read` |

## Validation

Per spec SC-001: with seeded content, the rendered HTML for each
section contains the expected CSS class names listed above.

Verification (smoke test):

```sh
curl -s http://localhost:8080/ > /tmp/home.html
grep -c 'class="nl-compact-block"' /tmp/home.html   # ≥ 1
grep -c 'class="segurado-block"' /tmp/home.html     # ≥ 1
grep -c 'class="nl-block"' /tmp/home.html            # ≥ 1
grep -c 'class="analise-layout"' /tmp/home.html      # ≥ 1
grep -c 'class="tabs-section"' /tmp/home.html        # ≥ 1
grep -c 'class="maislist-section"' /tmp/home.html     # ≥ 1
grep -c 'class="boletim-section"' /tmp/home.html      # ≥ 1
grep -c 'class="colunistas-section"' /tmp/home.html   # ≥ 1
```

All eight grep counts must be ≥ 1.