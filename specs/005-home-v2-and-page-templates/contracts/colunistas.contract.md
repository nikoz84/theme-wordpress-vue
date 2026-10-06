# Colunistas Category Contract: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

Defines the auto-creation of the "Colunistas" WordPress category
per Clarification Q3 and FR-004a.

## Required behavior

`theme/functions.php` MUST register a callback on the
`after_setup_theme` hook that:

1. Calls `category_exists('colunistas')`.
2. If `category_exists()` returns `null` or `0` (category does not
   exist), calls `wp_create_category('Colunistas')` with the
   default description and parent.

The callback MUST be idempotent — repeated invocations during
re-activations, theme switches, or container restarts MUST NOT create
duplicate categories or error.

## Signature

```php
function vb_register_colunistas_category(): void {
    if ( category_exists( 'colunistas' ) ) {
        return;
    }
    wp_create_category( 'Colunistas' );
}
add_action( 'after_setup_theme', 'vb_register_colunistas_category' );
```

## Failure handling

If `wp_create_category()` returns `WP_Error` or `0` (e.g., due to
database write permissions), the callback MUST NOT throw or
escalate the error. The Colunistas section (US4 / FR-004) falls
back to its empty-state rule (section hidden).

## Validation

Per spec FR-004a and SC-004: after theme activation, the
"Colunistas" category MUST exist with slug `colunistas` and name
"Colunistas".

Verification:

```sh
docker compose exec wordpress wp term list category --field=slug,count \
  | grep -E '^colunistas\b'
```

Expected: `colunistas` is listed.