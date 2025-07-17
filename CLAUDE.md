# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Link Blog and Go is a WordPress plugin that transforms a WordPress blog into a link blog format, similar to sites like Daring Fireball. The plugin automatically formats posts in a specific category with source attribution, permalink symbols, and theme integration features.

## Key Commands

### Development Commands
```bash
# Create release ZIP file (excludes git and GitHub files)
zip -r link-blog-and-go.zip . -x "*.git*" -x "*.github*" -x "*.specstory*"
```

### Testing
Since this is a WordPress plugin, testing requires a WordPress environment. There are no automated tests in the codebase. Manual testing involves:
1. Installing the plugin in a WordPress site
2. Creating posts in the designated links category
3. Verifying the formatting and features work correctly

## Architecture

### Main Plugin File
- `link-blog-and-go.php` - Core plugin file containing all PHP logic
  - `LinkBlogSetup` class - Main plugin class handling all functionality
  - `Provider_Link_Blog` class - Bricks Builder integration provider
  - Activation hook for creating the Links category

### Key Features
1. **Category-specific processing** - Only affects posts in the designated links category
2. **URL extraction** - Automatically extracts URLs from post content
3. **Custom fields** - Allows manual override of detected URLs
4. **Bricks Builder integration** - Dynamic data tags and echo functions
5. **RSS feed modifications** - Optional RSS feed enhancements
6. **Admin interface** - Settings page with live preview

### Integration Points
- **Shortcodes**: `[link_blog_link]` and `[via_link]`
- **Variables**: `{link_blog_link}` and `{via_link}`
- **Bricks Dynamic Tags**: `{link_blog_link}` and `{link_blog_via}`
- **Echo Functions**: `link_blog_get_main_url`, `link_blog_get_via_url`, etc.

### Data Storage
- Plugin settings stored in WordPress options table as `link_blog_options`
- Custom post meta: `_link_blog_custom_link` and `_link_blog_custom_via`

### Hooks and Filters
- Content filtering via `the_content` filter
- RSS modifications via `the_content_feed` and `the_title_rss` filters
- Admin assets enqueued on settings page only
- Meta boxes added for custom link fields when enabled

## Important Considerations

1. The plugin modifies post display non-destructively - original content is preserved
2. Category checking is critical - only posts in the designated category are affected
3. URL detection uses regex patterns to find the first URL in content
4. Bricks Builder integration requires the builder to be installed and active
5. Custom fields feature must be explicitly enabled in settings
6. RSS modifications are optional and off by default