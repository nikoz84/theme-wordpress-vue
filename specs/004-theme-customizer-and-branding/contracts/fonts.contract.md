# Fonts Contract: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

Defines the exact `<link>` tags the theme emits in `<head>` to load
the Safe Mídia Google Fonts.

## Required `<link>` tags

The theme MUST emit the following three `<link>` tags, in source order,
in `<head>` on every front-end page (single, page, archive, search,
404, home):

```
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;0,900;1,400&family=Inter:wght@400;500;600;700&display=swap">
```

## Tag order rationale

- The two `preconnect` directives come first so the browser opens
  TCP/TLS handshakes early.
- The `preconnect` to `fonts.gstatic.com` uses `crossorigin` because
  the font file requests are cross-origin.
- The stylesheet link comes after so it inherits the warm connection.

## Font families and weights

| Family | Weights | Where used in `theme/style.css` |
|---|---|---|
| Merriweather (serif) | 400, 700, 900; italic 400 | 400: single-post body (`.article-body`); 700: headings, titles, `<strong>` in posts; 900: `.page-title`; italic 400: `<em>`/blockquotes in posts |
| Inter (sans) | 400, 500, 600, 700 | Body, navigation, UI, buttons/badges (700) |

Only weights actually referenced by `theme/style.css` are loaded (Inter 300 is unused; the lone Inter 800 rule falls back to 700).

## `display=swap`

The stylesheet URL includes `&display=swap`, meaning the page renders
text in the system fallback (Georgia for Merriweather, system-ui for
Inter) immediately and swaps in the web fonts when they load.

## Validation

Per spec SC-002: with the theme active, every front-end page MUST
emit exactly one `<link rel="preconnect">` to `fonts.googleapis.com`,
exactly one `<link rel="preconnect">` to `fonts.gstatic.com`, and
exactly one `<link rel="stylesheet">` to the documented Google Fonts
URL with the documented query string.

Verification:

```sh
curl -s http://localhost:8080/ \
  | grep -oE '<link[^>]+googleapis[^>]+>' \
  | sort -u
```

The output MUST be exactly the three lines above.