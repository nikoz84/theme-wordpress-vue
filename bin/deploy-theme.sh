#!/usr/bin/env bash
# Safe Mídia — deploy the theme to production over SSH.
#
# Usage:
#   bin/deploy-theme.sh            # DRY RUN: connect, inspect remote, list files. Changes nothing.
#   bin/deploy-theme.sh --yes      # Back up the remote theme, then upload theme/ over it.
#
# Reads SSH_HOST_PROD, SSH_PORT_PROD, SSH_USER_PROD and SSH_DIRECTORY_PROD
# from the repo-root .env (gitignored). The SSH password is NOT read from
# .env — ssh prompts for it once and the connection is reused for every
# step (ControlMaster). Prefer an SSH key:  ssh-copy-id -p PORT USER@HOST
#
# Safety:
#   - Never deletes anything remotely. Files are overwritten/added only;
#     files removed locally stay on the server.
#   - Before uploading, the current remote theme folder is archived to
#     ~/theme-backups/<slug>-<timestamp>.tar.gz on the server.
#   - Does not touch the database.
#
# Rollback (on the server):
#   cd <SSH_DIRECTORY_PROD>/wp-content/themes && tar xzf ~/theme-backups/<file>.tar.gz
#
# Override the remote theme folder name with THEME_SLUG=... (default: vue-blocks).

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="${REPO_ROOT}/theme"
ENV_FILE="${REPO_ROOT}/.env"
THEME_SLUG="${THEME_SLUG:-vue-blocks}"
APPLY=0

log()  { printf '[vb-deploy] %s\n' "$*"; }
fail() { printf '[vb-deploy] ERROR: %s\n' "$*" >&2; exit 1; }

for arg in "$@"; do
  case "$arg" in
    --yes) APPLY=1 ;;
    -h|--help) sed -n '2,25p' "${BASH_SOURCE[0]}"; exit 0 ;;
    *) fail "unknown argument: $arg" ;;
  esac
done

# --- Read only the keys we need from .env (do not `source` it: values
# such as "Safe Mídia" are unquoted and would execute as commands). ---
[ -f "$ENV_FILE" ] || fail ".env not found at $ENV_FILE"
env_get() { grep -E "^$1=" "$ENV_FILE" | tail -1 | cut -d= -f2-; }

SSH_HOST="$(env_get SSH_HOST_PROD)"
SSH_PORT="$(env_get SSH_PORT_PROD)"
SSH_USER="$(env_get SSH_USER_PROD)"
REMOTE_ROOT="$(env_get SSH_DIRECTORY_PROD)"
for v in SSH_HOST SSH_PORT SSH_USER REMOTE_ROOT; do
  [ -n "${!v}" ] || fail "$v is empty — check the *_PROD keys in .env"
done
REMOTE_THEMES="${REMOTE_ROOT%/}/wp-content/themes"

# --- Sanity checks on the local theme ---
[ -f "${THEME_DIR}/style.css" ] || fail "theme/style.css not found"
for f in "${THEME_DIR}"/*.php "${THEME_DIR}"/inc/*.php "${THEME_DIR}"/template-parts/*/*.php; do
  [ -f "$f" ] || continue
  if command -v php >/dev/null 2>&1; then
    php -l "$f" >/dev/null || fail "PHP syntax error in $f"
  fi
done
log "Local PHP lint OK"

# Files that must never ship (mirrors theme/package.sh + large logo source).
TAR_EXCLUDES=(
  --exclude=./package.sh --exclude=./PACKAGE.md --exclude=./.gitignore
  --exclude=./node_modules --exclude=./dist
  --exclude='*.tmp' --exclude='*.dist'
  --exclude=./assets/images/logo.svg
)

# --- One SSH connection, reused for every step (single password prompt) ---
CTL_DIR="$(mktemp -d)"
CTL="${CTL_DIR}/ctl"
SSH=(ssh -p "$SSH_PORT" -o ControlPath="$CTL" -o StrictHostKeyChecking=accept-new "${SSH_USER}@${SSH_HOST}")
cleanup() { ssh -p "$SSH_PORT" -o ControlPath="$CTL" -O exit "${SSH_USER}@${SSH_HOST}" >/dev/null 2>&1 || true; rm -rf "$CTL_DIR"; }
trap cleanup EXIT

log "Connecting to ${SSH_USER}@${SSH_HOST}:${SSH_PORT} ..."
ssh -p "$SSH_PORT" -o ControlMaster=yes -o ControlPath="$CTL" -o ControlPersist=600 \
    -o StrictHostKeyChecking=accept-new -fN "${SSH_USER}@${SSH_HOST}"

# Remote paths are passed as positional args, so they are never re-parsed by the shell.
"${SSH[@]}" bash -s -- "$REMOTE_THEMES" "$THEME_SLUG" <<'REMOTE'
set -e
themes="$1"; slug="$2"
[ -d "$themes" ] || { echo "[remote] ERROR: themes dir not found: $themes" >&2; exit 1; }
echo "[remote] Themes in $themes:"
ls -1 "$themes" | sed 's/^/[remote]   /'
if [ -d "$themes/$slug" ]; then
  echo "[remote] Target theme folder exists: $slug ($(grep -m1 -E 'Version:' "$themes/$slug/style.css" 2>/dev/null | sed 's/^[ *]*//'))"
else
  echo "[remote] NOTE: $slug does not exist yet — it will be created."
fi
if command -v wp >/dev/null 2>&1; then
  echo "[remote] Active theme: $(cd "$themes/../.." && wp theme list --status=active --field=name 2>/dev/null || echo '?')"
fi
REMOTE

if [ "$APPLY" -ne 1 ]; then
  log "DRY RUN — files that would be uploaded to ${REMOTE_THEMES}/${THEME_SLUG}:"
  tar -C "$THEME_DIR" "${TAR_EXCLUDES[@]}" -cf - . | tar -tvf - | awk '{print "  " $3 "\t" $6}' | grep -v '/$' || true
  log "Nothing was changed. Re-run with --yes to back up and deploy."
  exit 0
fi

TS="$(date +%Y%m%d-%H%M%S)"
log "Backing up remote theme to ~/theme-backups/${THEME_SLUG}-${TS}.tar.gz ..."
"${SSH[@]}" bash -s -- "$REMOTE_THEMES" "$THEME_SLUG" "$TS" <<'REMOTE'
set -e
themes="$1"; slug="$2"; ts="$3"
mkdir -p "$HOME/theme-backups"
if [ -d "$themes/$slug" ]; then
  tar -C "$themes" -czf "$HOME/theme-backups/$slug-$ts.tar.gz" "$slug"
  echo "[remote] Backup: $(ls -lh "$HOME/theme-backups/$slug-$ts.tar.gz" | awk '{print $5, $9}')"
else
  echo "[remote] No existing theme folder — nothing to back up."
fi
mkdir -p "$themes/$slug"
REMOTE

log "Uploading theme ..."
tar -C "$THEME_DIR" "${TAR_EXCLUDES[@]}" -czf - . \
  | "${SSH[@]}" "tar -C '${REMOTE_THEMES}/${THEME_SLUG}' -xzf - --no-same-owner && echo '[remote] Upload complete.'"

log "Done. If the site uses LiteSpeed Cache / Hostinger cache, purge it so visitors see the new logo"
log "(wp-admin → LiteSpeed Cache → Purge All, or hPanel → Cache Manager)."
log "Rollback: on the server, tar -C ${REMOTE_THEMES} -xzf ~/theme-backups/${THEME_SLUG}-${TS}.tar.gz"
