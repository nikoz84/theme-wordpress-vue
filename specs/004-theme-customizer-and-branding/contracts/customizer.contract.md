# Customizer Contract: Theme Branding & Customizer Integration

**Date**: 2026-10-06
**Spec**: `specs/004-theme-customizer-and-branding/spec.md`
**Branch**: `004-theme-customizer-and-branding`

Defines the WordPress Customizer section the theme registers for
social media URLs.

## Section shape

| Attribute | Value |
|---|---|
| Section ID | `vb_social` |
| Section title (display) | "Social media" |
| Section description (display) | "URLs for the navbar and footer social icons. Leave a field empty to hide its icon." |
| Capability required | `edit_theme_options` |
| Storage | `theme_mod` (per the WordPress customization API default for theme-level settings) |

## Settings and controls

| Setting ID | Control type | Sanitize callback | Default | Notes |
|---|---|---|---|---|
| `vb_social_facebook` | URL (`WP_Customize_Url_Control`) | `esc_url_raw` | `''` | Empty → icon hidden |
| `vb_social_instagram` | URL | `esc_url_raw` | `''` | Empty → icon hidden |
| `vb_social_x` | URL | `esc_url_raw` | `''` | Empty → icon hidden |
| `vb_social_linkedin` | URL | `esc_url_raw` | `''` | Empty → icon hidden |

## Reading the settings

The navbar (`theme/template-parts/header/navbar.php`) and the footer
(`theme/template-parts/footer/site-footer.php`) read each setting via:

```php
$facebook_url = get_theme_mod( 'vb_social_facebook', '' );
if ( '' !== $facebook_url ) {
    // emit <a class="soc" href="...">...</a>
}
```

The pattern is: read with `''`, `''` default; if non-empty, render
the `<a>` element. An empty value suppresses the icon (per spec edge
case "Empty Customizer URL").

## Validation

Per spec SC-003:

- With Facebook URL set in the Customizer, the rendered HTML
  contains exactly one `<a class="soc" title="Facebook">` and
  exactly one `<a class="footer-soc" aria-label="Facebook">`, both
  with the configured `href`.
- With the URL cleared, both anchors are absent from the rendered
  HTML.

Verification (assuming `http://example.com/fb` is set in the
Customizer):

```sh
curl -s http://localhost:8080/ \
  | grep -oE '<a[^>]+class="soc"[^>]+href="[^"]*"[^>]*>' \
  | grep -i facebook
```

The output MUST be exactly one line with `href="http://example.com/fb"`.