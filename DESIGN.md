# Vue Blocks — Design System

This document is the **single source of truth** for the Vue Blocks
WordPress theme's visual language. It mirrors the design tokens
declared at `:root` in `theme/style.css` and documents the theme's
component inventory, layout patterns, responsive breakpoints, and
maintenance workflow.

> **Maintenance rule**: `theme/style.css`'s `:root` block is the
> machine-readable source of truth for token values. This file is the
> human-readable mirror. A canonical copy of this file is duplicated
> inside `theme/DESIGN.md` (the in-theme copy is refreshed from this
> one by `theme/package.sh` at packaging time).

## Design tokens

The full token catalog is defined in `contracts/design-tokens.schema.md`.
Summary by category:

### Colors

| Token | Value (Safe Mídia) | Used in |
|---|---|---|
| `--navy` | `#0B1E3D` | Footer background, primary heading text |
| `--blue` | `#1E50D4` | Primary action, link color, hero title chip |
| `--blue-hover` | `#1A46C0` | `:hover` for `--blue` |
| `--gold` | `#F5C418` | Subscribe CTA, hero meta bar, perk dots |
| `--gold-dark` | `#DFB000` | `:hover` for `--gold` |
| `--forest` | `#0D3B27` | (deferred — "Para o Segurado" branding) |
| `--forest-mid` | `#0F4C35` | (deferred) |
| `--white` | `#FFFFFF` | Surface background |
| `--off-white` | `#F4F6FB` | Strip background, card surface |
| `--gray-100` | `#EDF0F8` | Hairline borders, ad slot fill |
| `--gray-200` | `#DDE2EF` | Card borders, hairline dividers |
| `--gray-text` | `#5C6E8A` | Secondary text, category labels |
| `--dark` | `#0F1C30` | Body text |

### Typography

| Token | Value | Used in |
|---|---|---|
| `--font-serif` | `'Merriweather', serif` | Headings, titles (weights 700, 900) |
| `--font-sans` | `'Inter', sans-serif` | Body, nav, UI (weights 300, 400, 500, 600) |

### Spacing, radii, shadows

| Token | Value | Used in |
|---|---|---|
| `--space-section` | `36px` | Top margin of major sections |
| `--space-row` | `16px` | Default vertical row spacing |
| `--radius-sm` | `6px` | Inline controls, buttons |
| `--radius-md` | `10px` | Cards, image containers |
| `--radius-lg` | `16px` | Hero, hero feature card |
| `--shadow-card` | `0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04)` | Hover lift on news cards |
| `--shadow-navbar` | `0 2px 16px rgba(11,30,61,.07)` | Sticky navbar |

## Components

The theme's component inventory is defined in `data-model.md` (Entity E2).
Per-component details:

### navbar
- **Purpose**: Sticky header with logo, primary nav links, social icons, hamburger (mobile), and subscribe CTA.
- **Use when**: every page (via `header.php`).
- **Avoid when**: never (this is the canonical navbar).
- **template-parts path**: `theme/template-parts/header/navbar.php`
- **Tokens consumed**: `--white`, `--gray-200`, `--navy`, `--blue`, `--gold`, `--shadow-navbar`.

### hero-main
- **Purpose**: Featured sticky post (or most-recent) on the left half of the hero region.
- **Use when**: the homepage's hero section.
- **Avoid when**: non-homepage pages (those use `header.php` directly).
- **template-parts path**: `theme/template-parts/hero/hero.php` (composes hero-main + hero-aside).
- **Tokens consumed**: `--dark`, `--blue`, `--gold`, `--white`.

### hero-aside
- **Purpose**: Four small "recent posts" cards stacked on the right of the hero.
- **Use when**: the homepage's hero region (sibling of hero-main).
- **Tokens consumed**: `--dark`, `--blue`, `--gray-100`, `--gray-text`.

### news-card
- **Purpose**: Single news card with image, category chip, and title.
- **Use when**: any news grid (homepage, archive, category).
- **template-parts path**: `theme/template-parts/news/news-card.php`
- **Tokens consumed**: `--radius-md`, `--shadow-card`, `--blue`, `--dark`.

### site-footer
- **Purpose**: Site-wide footer with logo, columns of links, newsletter form, and copyright.
- **Use when**: every page (via `footer.php`).
- **template-parts path**: `theme/template-parts/footer/site-footer.php`
- **Tokens consumed**: `--navy`, `--white`, `--gold`, `--gray-text`.

### logo
- **Purpose**: Logo shield + brand name; used inside `navbar` and `site-footer`.
- **Tokens consumed**: `--blue`, `--navy`, `--white`.

### button (subscribe CTA)
- **Purpose**: Pill-shaped CTA used in the navbar ("Assinar Newsletter").
- **Tokens consumed**: `--gold`, `--gold-dark`, `--navy`.

### social
- **Purpose**: Circular icon button (Facebook, Instagram, X, LinkedIn) used in the navbar.
- **Tokens consumed**: `--gray-200`, `--blue`.

## Layout patterns

The home page is composed of four core sections in the documented
order. The seven other sections shown in the Safe Mídia reference are
**out of scope for v1** (deferred to a v2 follow-up).

| Order | Section | Component | WordPress source |
|---|---|---|---|
| 1 | navbar | `navbar` | Registered `primary` menu |
| 2 | hero | `hero-main` + `hero-aside` | First sticky post (else most recent); next 4 posts |
| 3 | news-grid | `news-card` × 6 | Next 6 most-recent posts |
| 4 | footer | `site-footer` | Registered `footer` menu + `wp_list_categories()` |

## Responsive breakpoints

| Breakpoint | Width | Behavior |
|---|---|---|
| Desktop | > 1024px | Full layout per the Safe Mídia reference |
| Tablet | ≤ 1024px | Single-column hero; 2-column news grid; sidebar hidden |
| Mobile | ≤ 768px | Hamburger nav; stacked news cards; compact newsletter |
| Small mobile | ≤ 420px | Single-column news grid; simplified footer |

## Maintenance

- **`DESIGN.md` (this file)** is the canonical human-readable design reference.
- **`theme/style.css`** is the canonical machine-readable design reference. Every visual value flows from a `:root` custom property.
- **`theme/DESIGN.md`** is a duplicate of this file, refreshed at packaging time by `theme/package.sh`. The two MUST stay byte-identical between packaging runs.
- **Changing a token**: edit `theme/style.css` first; then update the matching row in this file; then run `bash theme/package.sh` to refresh the in-theme copy.
- **Adding a component**: add the component to `theme/template-parts/...`; add a row to the Components table above; document consumed tokens.

Source visual design: `layouts-html/01 - Home/01 - safemidia-home-fixed.html` (Safe Mídia reference).