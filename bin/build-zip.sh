#!/usr/bin/env bash
#
# Build an installable theme zip from a git ref (default: HEAD).
#
#   bin/build-zip.sh            # zip of HEAD
#   bin/build-zip.sh main       # zip of another branch/tag/commit
#
# The zip contains only committed files (git archive), wrapped in a
# lifesites2021/ folder, minus everything marked export-ignore in
# .gitattributes. Upload it via WP-admin: Appearance > Themes > Add New >
# Upload Theme > "Replace active with uploaded". See DEPLOY.md.

set -euo pipefail

THEME=lifesites2021
REF="${1:-HEAD}"

cd "$(git rev-parse --show-toplevel)"

# Uncommitted changes are NOT in the zip; refuse so nothing is silently lost.
if [ "$REF" = "HEAD" ] && [ -n "$(git status --porcelain --untracked-files=no)" ]; then
	echo "Error: uncommitted changes in tracked files. Commit or stash first." >&2
	git status --short --untracked-files=no >&2
	exit 1
fi

BRANCH="$(git rev-parse --abbrev-ref HEAD)"
if [ "$REF" = "HEAD" ] && [ "$BRANCH" != "main" ]; then
	echo "Warning: building from branch '$BRANCH', not main." >&2
fi

SHA="$(git rev-parse --short "$REF")"

# Version from style.css and _S_VERSION in functions.php (at that ref) must agree:
# _S_VERSION is the ?ver= on the stylesheet/scripts, so it busts browser caches.
CSS_VERSION="$(git show "$REF:style.css" | sed -n 's/^Version:[[:space:]]*//p' | tr -d '\r')"
PHP_VERSION="$(git show "$REF:functions.php" | sed -n "s/.*define( '_S_VERSION', '\([^']*\)' ).*/\1/p")"
if [ "$CSS_VERSION" != "$PHP_VERSION" ]; then
	echo "Error: version mismatch: style.css=$CSS_VERSION, functions.php _S_VERSION=$PHP_VERSION" >&2
	exit 1
fi

mkdir -p dist
OUT="dist/${THEME}-${CSS_VERSION}-${SHA}.zip"
git archive --format=zip --prefix="${THEME}/" -o "$OUT" "$REF"

echo "Built $OUT  (version $CSS_VERSION, commit $SHA)"
unzip -l "$OUT" | tail -1
