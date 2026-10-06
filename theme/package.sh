#!/usr/bin/env bash
# Vue Blocks — Theme packaging script.
#
# Produces a clean .zip of the theme/ directory at the repo root.
# The zip is what uploads to a fresh WordPress site's
# wp-content/themes/ directory.
#
# Excludes test-harness paths (docker-compose.yml, bin/, etc.) and
# build output (node_modules/, dist/) so the deliverable is the theme
# alone.
#
# Refreshes theme/DESIGN.md from the canonical repo-root DESIGN.md
# before packaging.

set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${THEME_DIR}/.." && pwd)"
VERSION="$(grep -E '^\s*\*\s*Version:' "${THEME_DIR}/style.css" | head -1 | sed -E 's/.*Version:\s*([^ ]+).*/\1/' || echo 1.0.0)"
SLUG="vue-blocks"
OUT="${REPO_ROOT}/${SLUG}-${VERSION}.zip"

log() { printf '[vb-package] %s\n' "$*"; }
fail() { printf '[vb-package] ERROR: %s\n' "$*" >&2; exit 1; }

# 1. Refresh theme/DESIGN.md from the canonical repo-root DESIGN.md.
if [ -f "${REPO_ROOT}/DESIGN.md" ]; then
  log "Refreshing theme/DESIGN.md from canonical repo-root DESIGN.md..."
  cp "${REPO_ROOT}/DESIGN.md" "${THEME_DIR}/DESIGN.md"
fi

# 2. Build the zip, excluding test-harness and build output paths.
# Use Python's zipfile module (the `zip` CLI may not be installed on
# every contributor's host — Python is universal).
log "Building ${OUT}..."
cd "${THEME_DIR}"

python3 - "${THEME_DIR}" "${OUT}" <<'PYEOF'
import os
import sys
import zipfile

theme_dir = sys.argv[1]
out_path = sys.argv[2]

EXCLUDE_TOP = {
    "package.sh", "PACKAGE.md", ".gitignore", "docker-compose.yml",
    "docker-compose.yaml", ".env", ".env.example", "README.md",
    "package.json", "package-lock.json", "vite.config.js",
    "skills-lock.json",
}

EXCLUDE_DIRS = {
    "node_modules", "dist", "bin", "seed", "config", "specs",
    ".specify", ".opencode", ".agents", ".claude", "layouts-html",
    "wp-content", ".git",
}

EXCLUDE_FILE_PATTERNS = ("*.dist", "*.tmp")

import fnmatch

def excluded(rel_path: str, is_dir: bool) -> bool:
    parts = rel_path.split("/")
    top = parts[0]
    base = parts[-1]
    if top in EXCLUDE_TOP:
        return True
    if top in EXCLUDE_DIRS:
        return True
    for pat in EXCLUDE_FILE_PATTERNS:
        if fnmatch.fnmatch(base, pat):
            return True
    return False

count = 0
with zipfile.ZipFile(out_path, "w", zipfile.ZIP_DEFLATED) as zf:
    for root, dirs, files in os.walk(theme_dir):
        # Skip excluded directories in-place so os.walk doesn't descend into them.
        dirs[:] = [d for d in dirs if not excluded(os.path.relpath(os.path.join(root, d), theme_dir), True)]
        for f in files:
            full = os.path.join(root, f)
            rel = os.path.relpath(full, theme_dir)
            if excluded(rel, False):
                continue
            zf.write(full, rel)
            count += 1

print(f"[vb-package] added {count} files to archive")
PYEOF

# 3. Verify size (≤ 5 MB).
SIZE_BYTES=$(stat -c%s "${OUT}" 2>/dev/null || stat -f%z "${OUT}")
SIZE_MB=$(awk "BEGIN { printf \"%.2f\", ${SIZE_BYTES} / 1024 / 1024 }")
log "Zip size: ${SIZE_MB} MB (target: ≤ 5 MB)"
if [ "$(awk "BEGIN { print (${SIZE_MB} > 5) ? 1 : 0 }")" = "1" ]; then
  fail "Package exceeds 5 MB (${SIZE_MB} MB); aborting."
fi

# 4. Post-build leakage grep (SC-006).
# We only look for paths at the *root* of the archive (the trailing-space
# pattern) and meta files that should never appear at any depth
# (package.sh, PACKAGE.md, docker-compose.yml, README.md at root).
log "Verifying zip contents are clean of test-harness paths..."
LEAKED="$(unzip -l "${OUT}" | awk '/^[ ]+[0-9]+/ {print $NF}' | grep -E '^(node_modules|dist|docker-compose\.yml|\.env$|bin/bootstrap\.sh|seed/|config/|specs/|\.specify/|\.opencode/|\.agents/|\.claude/|layouts-html/|wp-content/|package\.sh|PACKAGE\.md|README\.md|package\.json|package-lock\.json|vite\.config\.js|skills-lock\.json)$' || true)"
if [ -n "${LEAKED}" ]; then
  printf '%s\n' "${LEAKED}" >&2
  fail "Package contains excluded paths; aborting."
fi

log "Package built: ${OUT}"
log "Done."