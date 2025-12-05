#!/bin/bash

echo "Creating test release package..."

# Create temp directory
mkdir -p /tmp/blogshq-test
rsync -av --progress . /tmp/blogshq-test/ --exclude='.git'

# Remove files listed in .distignore
while IFS= read -r pattern; do
    [[ -z "$pattern" || "$pattern" =~ ^#.*$ ]] && continue
    echo "Would remove: $pattern"
    rm -rf "/tmp/blogshq-test/$pattern"
done < .distignore

echo ""
echo "Files that would be included in release:"
echo "=========================================="
find /tmp/blogshq-test -type f | sed 's|/tmp/blogshq-test/||' | sort

echo ""
echo "Total size:"
du -sh /tmp/blogshq-test

# Cleanup
rm -rf /tmp/blogshq-test