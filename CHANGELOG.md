# Changelog

All notable changes to Link Blog and Go will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2025-01-17

### 🔒 Security Enhancements
- **CSRF Protection**: All admin actions now require valid WordPress nonces
- **Input Validation**: Comprehensive sanitization and validation of all user inputs
- **Capability Checks**: Proper permission verification for all administrative functions
- **URL Security**: Enhanced URL validation with protocol restrictions (http/https only)
- **Secure Updates**: Improved GitHub auto-updater with SSL verification and response validation

### 🏗️ Architecture Improvements
- **Complete Refactor**: Entire plugin rewritten following WordPress best practices
- **Modern File Structure**: Organized into logical components (admin, public, core, updater)
- **Class Autoloading**: PSR-4 style autoloading for better performance
- **Separation of Concerns**: Clean separation between admin, public, and core functionality
- **Dependency Injection**: Improved code maintainability and testability
- **Singleton Pattern**: Proper plugin initialization with single instance management

### ✨ New Features
- **Options Manager**: Centralized options handling with validation
- **URL Extractor**: Dedicated class for secure URL extraction and validation
- **Error Handling**: Comprehensive error management and logging
- **Uninstall Script**: Proper cleanup when removing the plugin
- **Requirements Check**: WordPress and PHP version validation on activation

### 🚀 Performance Improvements
- **Optimized Loading**: Assets only load where needed (admin pages only)
- **Efficient Database Queries**: Proper use of WordPress APIs
- **Transient Caching**: Improved caching for GitHub update checks
- **Conditional Asset Loading**: CSS/JS only loaded on plugin settings page

### 🛡️ Developer Features
- **Comprehensive Documentation**: PHPDoc comments throughout all classes and methods
- **Hooks & Filters**: Extensive customization points for developers
- **Clean Code**: Following WordPress coding standards and best practices
- **Backward Compatibility**: All existing features maintained with improved implementation

### 📁 File Structure Changes
- Created `includes/core/` for core functionality
- Created `includes/admin/` for admin-specific code
- Created `includes/public/` for public-facing code
- Created `includes/updater/` for update system
- Created `templates/admin/` for admin templates
- Moved assets to `assets/css/` and `assets/js/`

### 🔧 Technical Details
- Minimum PHP version: 7.4
- Minimum WordPress version: 5.0
- Added text domain support for full internationalization
- Proper use of WordPress constants and functions
- Secure file includes with ABSPATH checks

### 🐛 Fixes
- Fixed potential XSS vulnerabilities in admin interface
- Fixed CSRF vulnerabilities in AJAX handlers
- Fixed improper input handling in meta boxes
- Fixed insecure URL validation
- Fixed missing permission checks

### 📝 Notes
- Backup created as `link-blog-and-go-original-backup.php`
- Legacy update checker maintained for backward compatibility
- All user data and settings preserved during upgrade

## [1.2.5] - 2025-01-17

### Added
- **Modern Admin Interface**: Complete redesign of settings page with card-based layout
- **Organized Settings**: Grouped settings into logical sections (Basic, Permalink, Domain, RSS, Advanced)
- **Enhanced Sidebar**: Added comprehensive sidebar with quick guide, changelog, and support links
- **Custom Checkbox Styling**: Modern, accessible checkbox design with hover states
- **Responsive Design**: Improved mobile and tablet experience
- **Visual Hierarchy**: Better typography, spacing, and organization throughout

### Changed
- Replaced table-based layout with modern grid system
- Improved visual feedback with hover states and transitions
- Enhanced help text and examples throughout interface
- Better organization of update system and plugin information

### Improved
- User experience is significantly more intuitive and beautiful
- Settings are easier to understand with clear descriptions
- Professional appearance that matches modern WordPress standards
- Accessibility improvements with better contrast and focus states

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