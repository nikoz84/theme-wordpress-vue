#!/usr/bin/env bash
# Vue Blocks — delete an old theme folder via WP-CLI.
#
# WordPress refuses to delete the active theme, so when the target is
# active you must name a replacement with --activate; it is activated
# first. Menu locations / Customizer settings live in the per-theme
# option `theme_mods_<slug>`; --copy-mods carries them over to the
# replacement so menus don't disappear.
#
# Usage:
#   bin/delete-theme.sh [old-slug] [--activate <slug>] [--copy-mods] [--yes] [-- <extra wp args>]
#
# Examples:
#   # Production over SSH (WP-CLI on the server):
#   bin/delete-theme.sh vue-blocks-1.0.0 --activate vue-blocks --copy-mods -- --ssh=user@host/path/to/wp
#
#   # Local Docker harness:
#   WP_CLI="docker compose exec -T wordpress wp --allow-root --path=/var/www/html" \
#     bin/delete-theme.sh vue-blocks-1.0.0 --activate vue-blocks --copy-mods
#
# Default old-slug: vue-blocks-1.0.0 (the folder WordPress creates when
# the zip is uploaded through wp-admin).

set -euo pipefail

log() { printf '[vb-delete-theme] %s\n' "$*"; }
fail() { printf '[vb-delete-theme] ERROR: %s\n' "$*" >&2; exit 1; }

SLUG="vue-blocks-1.0.0"
ACTIVATE=""
COPY_MODS=0
ASSUME_YES=0
EXTRA=()

while [ $# -gt 0 ]; do
  case "$1" in
    --activate) ACTIVATE="${2:-}"; [ -n "$ACTIVATE" ] || fail "--activate needs a theme slug"; shift 2 ;;
    --copy-mods) COPY_MODS=1; shift ;;
    --yes|-y) ASSUME_YES=1; shift ;;
    --) shift; EXTRA=("$@"); break ;;
    -h|--help) sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    -*) fail "unknown option: $1" ;;
    *) SLUG="$1"; shift ;;
  esac
done

# shellcheck disable=SC2206
WP=( ${WP_CLI:-wp} )
command -v "${WP[0]}" >/dev/null 2>&1 || fail "WP-CLI not found (set WP_CLI or install wp)"
wp_() { "${WP[@]}" "$@" "${EXTRA[@]}"; }

wp_ theme is-installed "$SLUG" || fail "theme '$SLUG' is not installed"
[ "$SLUG" != "$ACTIVATE" ] || fail "--activate must be a different theme than '$SLUG'"

ACTIVE="$(wp_ theme list --status=active --field=name)"
if [ "$ACTIVE" = "$SLUG" ]; then
  [ -n "$ACTIVATE" ] || fail "'$SLUG' is the active theme; pass --activate <slug> to switch first"
fi
if [ -n "$ACTIVATE" ]; then
  wp_ theme is-installed "$ACTIVATE" || fail "replacement theme '$ACTIVATE' is not installed"
fi

log "Theme to delete : $SLUG"
log "Active theme    : $ACTIVE"
[ -n "$ACTIVATE" ] && log "Will activate   : $ACTIVATE"
[ "$COPY_MODS" = 1 ] && log "Copy theme_mods : theme_mods_${SLUG} -> theme_mods_${ACTIVATE:-$ACTIVE}"

if [ "$ASSUME_YES" != 1 ]; then
  read -r -p "[vb-delete-theme] Delete '$SLUG' permanently? [y/N] " answer
  case "$answer" in y|Y|s|S) ;; *) log "Aborted."; exit 0 ;; esac
fi

if [ "$COPY_MODS" = 1 ]; then
  TARGET="${ACTIVATE:-$ACTIVE}"
  if MODS="$(wp_ option get "theme_mods_${SLUG}" --format=json 2>/dev/null)"; then
    printf '%s' "$MODS" | wp_ option update "theme_mods_${TARGET}" --format=json
    log "Copied theme_mods to '$TARGET'."
  else
    log "No theme_mods_${SLUG} found; nothing to copy."
  fi
fi

if [ -n "$ACTIVATE" ] && [ "$ACTIVE" != "$ACTIVATE" ]; then
  wp_ theme activate "$ACTIVATE"
fi

wp_ theme delete "$SLUG"
log "Done."
