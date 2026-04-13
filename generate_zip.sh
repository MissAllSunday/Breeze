#!/bin/bash
set -euo pipefail

BUILD_DIR="build/assets"
COMPONENTS_DIR="Themes/default/scripts/breezeComponents"
BREEZE_PHP="Sources/Breeze/Breeze.php"
ZIP_NAME="Breeze.zip"

# ── 1. Lint & build ─────────────────────────────────────────────────────────
echo "==> Linting & building..."
npm run lint
npm run build
composer lint

# ── 2. Copy built JS to SMF theme ───────────────────────────────────────────
rm -rf "${COMPONENTS_DIR:?}"/*
cp -a "${BUILD_DIR}/." "${COMPONENTS_DIR}/"
echo "==> Copied build output to ${COMPONENTS_DIR}"

# ── 3. Extract hash from Vite output (index-{hash}.js) ─────────────────────
js_file=$(find "$BUILD_DIR" -maxdepth 1 -name 'index-*.js' -print -quit)

if [[ -z "$js_file" ]]; then
  echo "ERROR: No index-*.js found in ${BUILD_DIR}" >&2
  exit 1
fi

new_hash=$(basename "$js_file" .js)
echo "==> New hash: ${new_hash}"

# ── 4. Update REACT_HASH in Breeze.php ──────────────────────────────────────
old_hash=$(grep -oP "REACT_HASH = '\K[^']+" "$BREEZE_PHP")

if [[ -z "$old_hash" ]]; then
  echo "ERROR: Could not find REACT_HASH in ${BREEZE_PHP}" >&2
  exit 1
fi

if [[ "$old_hash" != "$new_hash" ]]; then
  sed -i "s/${old_hash}/${new_hash}/g" "$BREEZE_PHP"
  echo "==> Updated REACT_HASH: ${old_hash} → ${new_hash}"
else
  echo "==> REACT_HASH unchanged (${old_hash})"
fi

# ── 5. Production composer install & zip ────────────────────────────────────
echo "==> Installing production dependencies..."
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader

rm -f "$ZIP_NAME"
zip -r "$ZIP_NAME" \
  breezeVendor/ \
  Sources/ \
  Themes/ \
  install.php \
  installCheck.php \
  License \
  package-info.xml \
  README.md
echo "==> Created ${ZIP_NAME}"

# ── 6. Restore dev dependencies ─────────────────────────────────────────────
echo "==> Restoring dev dependencies..."
composer install
echo "==> Done"

