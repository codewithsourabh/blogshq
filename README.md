# BlogsHQ Admin Toolkit

## Installation

### From WordPress Admin
1. Download the latest release from [GitHub Releases](https://github.com/codewithsourabh/blogshq/releases)
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and activate

### Automatic Updates
Once installed, the plugin will automatically check for updates from GitHub and notify you when new versions are available.

## Development & Releases

### Creating a New Release

1. Update version numbers:
```bash
./scripts/prepare-release.sh 1.2.0
```

2. Review changes:
```bash
git diff
```

3. Commit and tag:
```bash
git add .
git commit -m "Bump version to 1.2.0"
git tag v1.2.0
git push origin main --tags
```

4. GitHub Actions will automatically:
   - Create a release
   - Build and attach the plugin ZIP file
   - Make it available for auto-updates

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.