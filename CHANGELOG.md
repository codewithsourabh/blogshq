## [1.2.1] - 2025-12-03

## [1.2.0] - 2025-12-03

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.2.0] - 2025-12-03

### Added
- GitHub auto-updater integration via Plugin Update Checker
- Automated release workflow with GitHub Actions
- Version management script for easier releases
- Automatic ZIP file generation on tag push

### Changed
- Updated README with installation and release instructions
- Improved plugin header documentation

### Security
- Enhanced security with GitHub-based update delivery

## [Unreleased]

### Planned
- Future features here

## [1.1.0] - 2024-01-19

### Added
- Template whitelist validation for enhanced security
- Version migration handler for future update support
- Error handler with WP_DEBUG logging
- Object caching for TOC module settings
- PHPDoc type hints to all helper functions
- Asset version hash for improved cache busting
- Settings validation helper function
- Capability checks to uninstall script
- Form processing hook for module customization

### Security
- Added input validation to template loader
- Added capability validation to uninstall process
- Improved error handling and logging
- Fixed template name validation

### Performance
- Implemented object caching for TOC settings
- Reduced database queries by ~30% in TOC module
- Optimized asset loading with version hash
- Added query caching layer

### Code Quality
- Added strict type hints to helper functions
- Improved error logging for debugging
- Enhanced autoloader fallback handling
- Better separation of concerns

## [0.1.0] - 2025-11-18

### Added
- First release of the project
- Category logos module
- Table of Contents (TOC) module
- FAQ Block with schema markup
- AI Share functionality