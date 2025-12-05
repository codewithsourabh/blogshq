#!/bin/bash

# Exit on error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get version from parameter
VERSION=$1

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Version number required${NC}"
    echo "Usage: ./scripts/prepare-release.sh 1.2.0"
    exit 1
fi

echo -e "${GREEN}Preparing release v$VERSION${NC}"

# Update version in main plugin file
echo "Updating blogshq-admin-toolkit.php..."
sed -i.bak "s/Version: .*/Version: $VERSION/" blogshq-admin-toolkit.php
sed -i.bak "s/define( 'BLOGSHQ_VERSION', .*/define( 'BLOGSHQ_VERSION', '$VERSION' );/" blogshq-admin-toolkit.php

# Update version in composer.json
echo "Updating composer.json..."
sed -i.bak "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" composer.json

# Update version in package.json
echo "Updating package.json..."
sed -i.bak "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" package.json

# Clean up backup files
rm -f blogshq-admin-toolkit.php.bak composer.json.bak package.json.bak

echo -e "${GREEN}Version updated to $VERSION${NC}"
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Update CHANGELOG.md with release notes"
echo "2. Run: git add ."
echo "3. Run: git commit -m 'Release v$VERSION'"
echo "4. Run: git tag v$VERSION"
echo "5. Run: git push origin main --tags"