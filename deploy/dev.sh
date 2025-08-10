#!/usr/bin/env bash
set -euo pipefail

# FlashcardPro Dev Bootstrap Script (simple, in-container or host shell)
# Usage (inside container):
#   bash deploy/dev.sh
#
# This script relies on .env for configuration (e.g., APP_PORT). It does not
# manage Docker Compose or override environment variables.

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")"/.. && pwd)"
cd "$ROOT_DIR"

# Ensure environment file exists
if [[ ! -f ".env" && -f ".env.example" ]]; then
  echo "[dev] Creating .env from example..."
  cp .env.example .env
fi

# Ensure SQLite database file exists (safe no-op if using another driver)
mkdir -p database
touch database/database.sqlite || true

# Optional dependency installation (safe to re-run)
if [[ -f "composer.json" ]]; then
  echo "[dev] Installing composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

if [[ -f "package.json" ]]; then
  echo "[dev] Installing npm dependencies..."
  npm ci --loglevel=error
fi

echo "[dev] Generating app key (safe to re-run)..."
php artisan key:generate --force || true

echo "[dev] Running migrations and seeders..."
php artisan migrate --force --seed

echo "[dev] Optimizing framework caches..."
php artisan optimize || true

echo "[dev] Generating API docs (if available)..."
if php artisan list | grep -q "l5-swagger:generate"; then
php artisan l5-swagger:generate || true
fi

if [[ -f "package.json" ]]; then
  echo "[dev] Building frontend assets..."
  npm run build --silent || true
fi

echo "[dev] Done."


