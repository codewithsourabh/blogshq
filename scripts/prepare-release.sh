#!/bin/bash

# Get version from parameter
VERSION=$1

if [ -z "$VERSION" ]; then
    echo "Usage: ./scripts/prepare-release.sh 1.2.0"
    exit 1
fi

echo "Preparing release v$VERSION"

# Update version in main plugin file
sed -i "s/Version: .*/Version: $VERSION/" blogshq-admin-toolkit.php
sed -i "s/define( 'BLOGSHQ_VERSION', .*/define( 'BLOGSHQ_VERSION', '$VERSION' );/" blogshq-admin-toolkit.php

# Update version in composer.json
sed -i "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" composer.json

# Update version in package.json
sed -i "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" package.json

# Update CHANGELOG.md
echo "## [$VERSION] - $(date +%Y-%m-%d)" >> CHANGELOG.md.tmp
echo "" >> CHANGELOG.md.tmp
cat CHANGELOG.md >> CHANGELOG.md.tmp
mv CHANGELOG.md.tmp CHANGELOG.md

echo "Version updated to $VERSION"
echo "Please review changes and commit them"
echo ""
echo "Then run:"
echo "git add ."
echo "git commit -m 'Bump version to $VERSION'"
echo "git tag v$VERSION"
echo "git push origin main --tags"