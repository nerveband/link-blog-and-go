# Changelog

All notable changes to Link Blog and Go will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.4] - 2025-01-17

### Added
- **Manual Update System**: Added manual update check and trigger in settings page
- "Check for Updates" button to manually check for new versions
- "Update Now" button to trigger immediate updates
- Real-time update status display with version information
- Release notes preview in update notifications
- Visual progress indicators during update process

### Changed
- Enhanced admin interface with dedicated Plugin Updates section
- Improved update user experience with clear status messages
- Added AJAX handlers for seamless update checking

### Fixed
- Update checker now properly clears cache for manual checks
- Better error handling and user feedback during update process

## [1.2.3] - 2025-01-17

### Added
- **Auto-Link Control**: Added option to disable automatic link appending to posts
- **Title Display Control**: Added checkboxes to disable "Link Blog Link Title" and "Via Link Title"
- **Fixed Bricks Builder Dynamic Tags**: Implemented proper Bricks Builder integration using correct filter hooks
- New Bricks dynamic tags that actually work:
  - `{link_blog_link}` - Main URL
  - `{link_blog_via}` - Via URL  
  - `{link_blog_domain}` - Main domain
  - `{link_blog_via_domain}` - Via domain

### Changed
- Auto-append behavior is now optional (can be disabled in settings)
- Link and via title display can be individually controlled
- Fixed Bricks Builder dynamic tag registration using proper WordPress filters
- Enhanced admin interface with Auto-Link Behavior section

### Fixed
- Bricks Builder dynamic tags now properly register and render in the builder
- Fixed dynamic tag content replacement in Bricks templates

## [1.2.2] - 2025-01-17

### Added
- **Customizable Domain Text**: Added before/after text options for domain variables and shortcodes
- New settings for domain text customization in admin interface:
  - Domain Before Text (default: "→ ")
  - Domain After Text (default: "")
  - Via Domain Before Text (default: "via ")
  - Via Domain After Text (default: "")
- Enhanced shortcodes with custom text support:
  - `[link_blog_domain before="Source: " after=" →"]`
  - `[via_domain before="(via " after=")"]`

### Changed
- Domain variables now use customizable text from settings
- Updated admin interface with new Domain Text Customization section
- Enhanced shortcode documentation with before/after attributes examples

## [1.2.1] - 2025-01-17

### Fixed
- **Conditional Domain Links**: Automatic domain links now only appear when no manual shortcodes or variables are used
- Plugin no longer adds duplicate domain attribution when users manually place `[link_blog_domain]` or `{link_blog_domain}` in content
- Added detection for all link blog shortcodes and variables to prevent auto-addition conflicts

### Changed
- Updated admin interface to explain conditional behavior
- Enhanced live preview to show when auto-addition occurs
- Improved user documentation with manual placement examples

## [1.2.0] - 2025-01-17

### Added
- **Domain Extraction**: Now displays clean domain names (e.g., "defector.com") instead of full URLs
- **New Bricks Builder Dynamic Tags**: 
  - `{link_blog_domain}` - Shows domain from main link
  - `{link_blog_via_domain}` - Shows domain from via link
- **New Shortcodes**:
  - `[link_blog_domain]` - Display main link domain
  - `[via_domain]` - Display via link domain
- **New Variables**:
  - `{link_blog_domain}` - Main domain as link
  - `{via_domain}` - Via domain as link
- **Echo Functions for Bricks**:
  - `link_blog_get_main_domain()` - Returns domain only
  - `link_blog_get_via_domain()` - Returns via domain only
  - `link_blog_get_domain_link()` - Returns domain as clickable link
  - `link_blog_get_via_domain_link()` - Returns via domain as clickable link
- **Auto-Update System**: Direct GitHub updates without WordPress.org
- **GitHub Actions**: Automated release creation with zip packages

### Changed
- Source attribution now shows domain with arrow (→) instead of "Source:"
- RSS feeds now display domains instead of full URLs
- Cleaner, more modern link attribution style
- Updated admin interface to show all new domain features

### Fixed
- Plugin initialization now works correctly on both admin and frontend

## [1.1.0] - Previous Release

### Added
- Bricks Builder integration with dynamic data support
- Custom fields for link and via URLs
- Shortcodes and variables for theme customization
- RSS feed enhancements

### Changed
- Non-destructive title handling
- Improved category-specific processing

## [1.0.0] - Initial Release

### Added
- Automatic URL extraction from post content
- Category-specific formatting for link posts
- Permalink symbols with customizable position
- Admin settings page with live preview
- Basic RSS feed modifications