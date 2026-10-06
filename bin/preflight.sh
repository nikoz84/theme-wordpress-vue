#!/usr/bin/env bash
# Vue Blocks — host-side preflight.
#
# Confirms Docker is running before the contributor runs
# `docker compose up`; emits an actionable message on failure (per
# FR-012, Edge Case "Missing Docker").

set -euo pipefail

if ! command -v docker >/dev/null 2>&1; then
  printf '[vb-preflight] ERROR: Docker is not installed. Install Docker Desktop or Docker Engine and try again.\n' >&2
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  printf '[vb-preflight] ERROR: Docker is not running. Start Docker and try again.\n' >&2
  exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
  printf '[vb-preflight] ERROR: Docker Compose v2 is required (the `docker compose` command). Update Docker Desktop or Docker Engine.\n' >&2
  exit 1
fi

if [ ! -f ./.env ] && [ -f ./.env.example ]; then
  printf '[vb-preflight] No .env found; copying .env.example to .env. Edit .env and replace the TEST-ONLY placeholder passwords before sharing the environment.\n'
  cp ./.env.example ./.env
fi

printf '[vb-preflight] Docker is ready.\n'