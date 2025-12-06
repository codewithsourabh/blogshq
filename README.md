# BlogsHQ Admin Toolkit

A comprehensive WordPress admin toolkit featuring category logos, table of contents, FAQ blocks with schema markup, and AI-powered share functionality.

## Features

- 🎨 **Category Logos** - Light/dark mode support with shortcodes
- 📑 **Table of Contents** - Auto-generated TOC with smooth scrolling
- ❓ **FAQ Blocks** - SEO-friendly FAQ sections with schema markup
- 🤖 **AI Share** - Share content via ChatGPT, Claude, Gemini, and more
- ✨ **Modern Admin UI** - Beautiful, responsive admin interface
- 🔄 **Auto-Updates** - Automatic updates from GitHub releases

## Installation

### From WordPress Admin (Recommended)
1. Download the latest release from [GitHub Releases](https://github.com/codewithsourabh/blogshq/releases)
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and activate
4. Plugin will automatically check for updates

### From GitHub
```bash
cd wp-content/plugins
git clone https://github.com/codewithsourabh/blogshq.git blogshq-admin-toolkit
cd blogshq-admin-toolkit
composer install --no-dev

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Modern browser with JavaScript enabled

## Development

### Setup Development Environment
```bash
# Clone repository
git clone https://github.com/codewithsourabh/blogshq.git
cd blogshq

# Install dependencies
composer install
npm install

# Start development mode
npm run start

# Build for production
npm run build
```

### Creating a New Release

1. **Update version numbers:**
```bash
./scripts/prepare-release.sh 1.2.1
```

2. **Review changes:**
```bash
git diff
```

3. **Update CHANGELOG.md** with new version details

4. **Commit and tag:**
```bash
git add .
git commit -m "Release v1.2.1"
git tag v1.2.1
git push origin main --tags
```

5. **GitHub Actions automatically:**
   - Builds production assets
   - Creates release
   - Attaches ZIP file
   - Enables auto-updates

### Testing Updates Locally

1. Install current version on test site
2. Create new release with higher version
3. Check WordPress Admin → Updates
4. Verify update notification appears
5. Test update process

## Project Structure
```
blogshq-admin-toolkit/
├── admin/                  # Admin interface
│   ├── css/               # Admin styles
│   ├── js/                # Admin scripts
│   └── views/             # Admin templates
├── assets/                # Frontend assets
│   ├── css/              # Frontend styles
│   └── js/               # Frontend scripts
├── includes/             # Core plugin files
├── modules/              # Feature modules
│   ├── faq/             # FAQ functionality
│   ├── logos/           # Category logos
│   ├── toc/             # Table of contents
│   └── ai-share/        # AI share buttons
├── lib/                  # Third-party libraries
│   └── plugin-update-checker/
└── languages/            # Translation files
```

## Support

- **Documentation:** [GitHub Wiki](https://github.com/codewithsourabh/blogshq/wiki)
- **Issues:** [GitHub Issues](https://github.com/codewithsourabh/blogshq/issues)
- **Discussions:** [GitHub Discussions](https://github.com/codewithsourabh/blogshq/discussions)

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) first.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## License

GPL v2 or later - See [LICENSE](LICENSE) for details.

## Credits

- **Author:** Sourabh
- **Plugin Update Checker:** [YahnisElsts](https://github.com/YahnisElsts/plugin-update-checker)
- **Contributors:** [View all contributors](https://github.com/codewithsourabh/blogshq/graphs/contributors)