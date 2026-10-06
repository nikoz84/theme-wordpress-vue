# Vue Blocks — Bootstrap Script
#
# Runs as the WordPress container's entrypoint on every start.
# Idempotent: re-running on a populated environment is a no-op for
# already-performed steps (per FR-010).
#
# This script:
#   1. Generates wp-config.php if missing (using `wp config create`
#      against the WORDPRESS_DB_* env vars that the official
#      WordPress image expects).
#   2. Waits for the database.
#   3. Installs WordPress core (if not present).
#   4. Creates / updates the admin user from WP_ADMIN_* env vars.
#   5. Activates the Vue Blocks theme.
#   6. Sets permalinks.
#   7. Imports the seeded sample content (once).
#
# It then exec's the original image command (apache2-foreground).

set -euo pipefail

WP_PATH="${WP_PATH:-/var/www/html}"
THEME_PATH="${THEME_PATH:-/var/www/html/wp-content/themes/vue-blocks}"
# Seed lives at the repo root in ./seed/; the docker-compose.yml
# bind-mounts ./seed to /bootstrap/seed.
SEED_FILE="${SEED_FILE:-/bootstrap/seed/sample-content.xml}"

log() { printf '[vb-bootstrap] %s\n' "$*"; }
fail() { printf '[vb-bootstrap] ERROR: %s\n' "$*" >&2; exit 1; }

require_env() {
  local name="$1"
  if [ -z "${!name:-}" ]; then
    fail "${name} is required; copy .env.example to .env and set it."
  fi
}

# ---- Preflight: port collision (FR-012) ----------------------------
HOST_PORT="${HOST_PORT:-8080}"
if [ "${HOST_PORT}" -lt 1024 ] 2>/dev/null; then
  fail "HOST_PORT must be >= 1024; got ${HOST_PORT}."
fi
if command -v ss >/dev/null 2>&1; then
  if ss -ltn 2>/dev/null | awk '{print $4}' | grep -E "[:.]${HOST_PORT}\$" >/dev/null; then
    fail "HOST_PORT=${HOST_PORT} is already in use on the host. Stop the conflicting process OR set HOST_PORT to another value (>= 1024) and retry."
  fi
fi

# ---- Preflight: required env vars (FR-008) ------------------------
require_env WORDPRESS_DB_PASSWORD
require_env WP_ADMIN_PASSWORD

# ---- ensure_wp_cli ------------------------------------------------
#
# The official wordpress:6 image does NOT ship wp-cli. Install it
# once per image so the rest of this script can drive WordPress
# non-interactively.
ensure_wp_cli() {
  if command -v wp >/dev/null 2>&1; then
    return 0
  fi
  log "Installing wp-cli..."
  curl -fsSL -o /usr/local/bin/wp \
    https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x /usr/local/bin/wp
  wp --info --allow-root >/dev/null
}

# ---- setup_wp_core -------------------------------------------------
#
# The official wordpress:6 image ships WordPress core at
# /usr/src/wordpress/. Its built-in docker-entrypoint.sh normally
# rsyncs that into /var/www/html on first start. By overriding the
# entrypoint with this script, we have to do that copy ourselves.
setup_wp_core() {
  if [ -f "${WP_PATH}/wp-settings.php" ]; then
    log "WordPress core already present at ${WP_PATH}; skipping copy."
    return 0
  fi
  log "Copying WordPress core from /usr/src/wordpress to ${WP_PATH}..."
  if command -v rsync >/dev/null 2>&1; then
    rsync -a /usr/src/wordpress/ "${WP_PATH}/"
  else
    cp -a /usr/src/wordpress/. "${WP_PATH}/"
  fi
}

# ---- ensure_wp_config (the missing step) --------------------------
#
# The official WordPress image's docker-entrypoint.sh normally
# generates wp-config.php from WORDPRESS_DB_* env vars. We replaced
# that entrypoint with this bootstrap, so we have to do the
# generation ourselves — otherwise Apache can't connect to the DB.
ensure_wp_config() {
  if [ -f "${WP_PATH}/wp-config.php" ]; then
    log "wp-config.php already exists; skipping."
    return 0
  fi
  log "Generating wp-config.php..."
  wp config create \
    --path="${WP_PATH}" \
    --allow-root \
    --dbhost="${WORDPRESS_DB_HOST:-db}" \
    --dbname="${WORDPRESS_DB_NAME:-wordpress}" \
    --dbuser="${WORDPRESS_DB_USER:-wordpress}" \
    --dbpass="${WORDPRESS_DB_PASSWORD}" \
    --locale="${WORDPRESS_LOCALE:-en_US}" \
    --skip-check \
    --force
}

# ---- wait_for_db ----------------------------------------------------
#
# Wait for the database service to accept a connection. The wordpress:6
# image does NOT ship mysql / mysqlcheck / mysqladmin binaries, and
# wp-cli's `wp db check` / `wp db query` shell out to them. We test the
# connection from PHP directly via the mysqli extension (which IS present
# in the wordpress image).
wait_for_db() {
  local host="${WORDPRESS_DB_HOST:-db}"
  local user="${WORDPRESS_DB_USER:-wordpress}"
  local pass="${WORDPRESS_DB_PASSWORD}"
  local name="${WORDPRESS_DB_NAME:-wordpress}"
  local port="${WORDPRESS_DB_PORT:-3306}"
  local i=0
  while [ "$i" -lt 60 ]; do
    if php -r "mysqli_report(MYSQLI_REPORT_OFF); \$db = @new mysqli('${host}', '${user}', '${pass}', '${name}', ${port}); if (\$db->connect_error) { exit(1); } \$db->close(); exit(0);" >/dev/null 2>&1; then
      log "Database is ready."
      return 0
    fi
    i=$((i + 1))
    sleep 1
  done
  fail "Database did not become reachable within 60s. Check the db service logs."
}

# ---- install_wordpress ---------------------------------------------
install_wordpress() {
  if wp core is-installed --path="${WP_PATH}" --allow-root 2>/dev/null; then
    log "WordPress already installed; skipping core install."
    return 0
  fi
  log "Installing WordPress core..."
  wp core install \
    --path="${WP_PATH}" \
    --allow-root \
    --url="http://localhost:${HOST_PORT}" \
    --title="${WP_SITE_TITLE:-Safe Mídia}" \
    --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL:-admin@example.test}" \
    --skip-email
}

# ---- ensure_admin_user ---------------------------------------------
ensure_admin_user() {
  local user="${WP_ADMIN_USER:-admin}"
  if wp user get "${user}" --path="${WP_PATH}" --allow-root >/dev/null 2>&1; then
    log "Admin user '${user}' already exists; updating password/email."
    wp user update "${user}" \
      --path="${WP_PATH}" --allow-root \
      --user_pass="${WP_ADMIN_PASSWORD}" \
      --user_email="${WP_ADMIN_EMAIL:-admin@example.test}" >/dev/null
  else
    log "Creating admin user '${user}'..."
    wp user create "${user}" "${WP_ADMIN_EMAIL:-admin@example.test}" \
      --path="${WP_PATH}" --allow-root \
      --role=administrator \
      --user_pass="${WP_ADMIN_PASSWORD}"
  fi
}

# ---- activate_theme ------------------------------------------------
activate_theme() {
  local theme="vue-blocks"
  local active
  active=$(wp theme list --path="${WP_PATH}" --allow-root --status=active --field=name 2>/dev/null || echo "")
  if [ "${active}" = "${theme}" ]; then
    log "Theme '${theme}' already active; skipping."
    return 0
  fi
  log "Activating theme '${theme}'..."
  wp theme activate "${theme}" --path="${WP_PATH}" --allow-root
}

# ---- set_permalinks ------------------------------------------------
set_permalinks() {
  local current
  current=$(wp option get permalink_structure --path="${WP_PATH}" --allow-root 2>/dev/null || echo "")
  if [ "${current}" = "/%postname%/" ]; then
    log "Permalinks already set; skipping."
    return 0
  fi
  log "Setting permalink structure to /%postname%/..."
  wp rewrite structure "/%postname%/" --hard --path="${WP_PATH}" --allow-root
}

# ---- ensure_wp_importer ------------------------------------------
#
# `wp import` requires the official wordpress-importer plugin. The
# wordpress:6 image does not ship it; install once.
ensure_wp_importer() {
  if wp plugin status wordpress-importer --path="${WP_PATH}" --allow-root 2>/dev/null | grep -q "^Status:"; then
    return 0
  fi
  log "Installing wordpress-importer plugin..."
  wp plugin install wordpress-importer --allow-root --path="${WP_PATH}" || log "wordpress-importer install failed; continuing."
  wp plugin activate wordpress-importer --allow-root --path="${WP_PATH}" 2>/dev/null || true
}

# ---- import_seed (FR-014) ------------------------------------------
import_seed() {
  local marker="vb_seed_imported"
  local existing
  existing=$(wp option get "${marker}" --path="${WP_PATH}" --allow-root 2>/dev/null || echo "")
  if [ "${existing}" = "1" ]; then
    log "Sample content already imported; skipping."
    return 0
  fi
  if [ ! -f "${SEED_FILE}" ]; then
    log "No seed file at ${SEED_FILE}; skipping sample content import."
    return 0
  fi
  log "Importing sample content from ${SEED_FILE}..."
  ensure_wp_importer
  wp import "${SEED_FILE}" \
    --path="${WP_PATH}" --allow-root \
    --authors=skip || log "Sample content import failed; continuing."
  wp option update "${marker}" "1" --path="${WP_PATH}" --allow-root >/dev/null
}

# ---- main ----------------------------------------------------------
log "Vue Blocks bootstrap starting (HOST_PORT=${HOST_PORT})."
ensure_wp_cli
setup_wp_core
ensure_wp_config
wait_for_db
install_wordpress
ensure_admin_user
activate_theme
set_permalinks
import_seed
log "Vue Blocks bootstrap complete."

# Hand off to the image's default command (apache2-foreground).
exec "$@"