# Contracts: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

This directory holds the interface definitions for the Google Fonts
load and the WordPress Customizer section.

## contracts/

| File | Direction | Purpose |
|---|---|---|
| `fonts.contract.md` | Theme → Browser | Defines the exact `<link>` tags the theme emits to load Google Fonts |
| `customizer.contract.md` | Admin → Theme | Defines the Customizer section's shape, settings, and storage |

## Contract reference

### Fonts (per `fonts.contract.md`)

The theme MUST emit, in `<head>`, in source order:

```
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Inter:wght@300;400;500;600&display=swap">
```

Validation: SC-002 in the spec asserts the rendered page contains
exactly these three `<link>` tags with these exact attributes.

### Customizer (per `customizer.contract.md`)

The Customizer panel MUST:

- Be titled "Social media" (key `vb_social`).
- Have a description: "URLs for the navbar and footer social icons.
  Leave a field empty to hide its icon."
- Expose four URL controls (Facebook, Instagram, X, LinkedIn).
- Persist each URL as a `theme_mod` keyed `vb_social_<platform>`.

## Cross-contract invariants

- The fonts contract is independent of the customizer contract.
- Both contracts are independent of any data-model entity's
  lifecycle.

## Out-of-scope surfaces (deliberately omitted)

- The Customizer's other built-in sections (Site Identity, Colors,
  etc.) — left at WordPress defaults; no theme-level overrides.
- Page-template URLs (e.g., per-page custom social settings) — out
  of scope.