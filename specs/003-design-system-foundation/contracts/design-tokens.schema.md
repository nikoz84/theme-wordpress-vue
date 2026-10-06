# Design Tokens Contract: Design System & Theme Package Foundation

**Date**: 2026-10-06
**Spec**: `specs/003-design-system-foundation/spec.md`
**Branch**: `003-design-system-foundation`

This file is the contract between the canonical CSS source
(`theme/style.css`'s `:root` block) and the human-readable
documentation (`DESIGN.md` § Design tokens, with a duplicate inside
`theme/DESIGN.md`).

## Required token categories

### Colors

| Token | Value (Safe Mídia) | Used in |
|---|---|---|
| `--navy` | `#0B1E3D` | Footer background, primary text on light surfaces |
| `--blue` | `#1E50D4` | Primary action, link color, hero title chip |
| `--blue-hover` | `#1A46C0` | `:hover` for `--blue` |
| `--gold` | `#F5C418` | Subscribe CTA, hero meta bar, perk dots |
| `--gold-dark` | `#DFB000` | `:hover` for `--gold` |
| `--forest` | `#0D3B27` | "Para o Segurado" branding (deferred in v1) |
| `--forest-mid` | `#0F4C35` | (deferred) |
| `--white` | `#FFFFFF` | Surface background |
| `--off-white` | `#F4F6FB` | Strip background, card surface |
| `--gray-100` | `#EDF0F8` | Hairline borders, ad slot fill |
| `--gray-200` | `#DDE2EF` | Card borders, hairline dividers |
| `--gray-text` | `#5C6E8A` | Secondary text, category labels |
| `--dark` | `#0F1C30` | Body text, primary heading text |

### Typography

| Token | Value | Used in |
|---|---|---|
| `--font-serif` | `'Merriweather', serif` | Headings, titles (700, 900 weights) |
| `--font-sans` | `'Inter', sans-serif` | Body text, navigation, UI (300, 400, 500, 600 weights) |

### Spacing

The reference uses raw px values; v1 tokenizes a few common ones:

| Token | Value | Used in |
|---|---|---|
| `--space-section` | `36px` | Top margin of major sections |
| `--space-row` | `16px` | Default vertical spacing between rows |

### Radii

| Token | Value | Used in |
|---|---|---|
| `--radius-sm` | `6px` | Inline controls, buttons |
| `--radius-md` | `10px` | Cards, image containers |
| `--radius-lg` | `16px` | Hero, hero feature card |

### Shadows

| Token | Value | Used in |
|---|---|---|
| `--shadow-card` | `0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04)` | Hover lift on news cards |
| `--shadow-navbar` | `0 2px 16px rgba(11,30,61,.07)` | Sticky navbar |

## Validation

- Every token in this contract MUST be declared at `:root` in
  `theme/style.css`.
- Every `:root` declaration in `theme/style.css` MUST appear in
  this contract AND in `DESIGN.md`.
- `package.sh` SHOULD detect a name mismatch at packaging time and
  warn (not fail).

## Source of truth

`theme/style.css`'s `:root` block is authoritative for the computed
value. `DESIGN.md` mirrors the table above. Both MUST agree at all
times.

The Safe Mídia reference HTML at
`layouts-html/01 - Home/01 - safemidia-home-fixed.html` (lines 13–27
of the file) is the original visual-design source.