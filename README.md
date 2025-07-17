# Link Blog and Go

> ✅ **Stable Release**: Version 1.3.0 brings major security improvements, modern architecture, and enhanced WordPress integration.

Transform your WordPress blog into a link blog - easily share and comment on interesting links you find across the web, with automatic domain extraction for clean, professional link attribution.

Version: 1.3.0

## What is a Link Blog?

A link blog is a type of blog where posts primarily consist of interesting links you've found on the web, along with your commentary about why they're worth sharing. Think of it as curating the best of the web for your readers.

![Link Blog and Go WordPress Plugin Interface](link-blog-and-go-screenshot.png)

## 🎉 What's New in Version 1.3.0

This major update brings significant improvements to security, architecture, and maintainability:

### 🔒 Security Enhancements
- **CSRF Protection**: All admin actions now require valid WordPress nonces
- **Input Validation**: Comprehensive sanitization and validation of all user inputs
- **Capability Checks**: Proper permission verification for all administrative functions
- **URL Security**: Enhanced URL validation with protocol restrictions
- **Secure Updates**: Improved GitHub auto-updater with SSL verification

### 🏗️ Architecture Improvements
- **Modern File Structure**: Organized into logical components following WordPress best practices
- **Autoloading**: PSR-4 style autoloading for better performance
- **Separation of Concerns**: Clean separation between admin, public, and core functionality
- **Dependency Injection**: Improved code maintainability and testability

### 🚀 Performance & Compatibility
- **WordPress 5.0+**: Full compatibility with modern WordPress
- **PHP 7.4+**: Leverages modern PHP features for better performance
- **Optimized Loading**: Assets only load where needed
- **Efficient Database Queries**: Proper use of WordPress APIs

### 🛡️ Developer Features
- **Comprehensive Documentation**: PHPDoc comments throughout
- **Error Handling**: Robust error management and logging
- **Hooks & Filters**: Extensive customization points for developers
- **Clean Uninstall**: Proper cleanup when removing the plugin

## Features

- 🔗 Automatically formats link posts with domain-based source attribution
- 🌐 **Domain extraction**: Shows clean domain names (e.g., "defector.com") instead of full URLs
- ⭐ Adds a distinctive permalink symbol to link posts (without modifying post titles)
- 📱 Clean, minimal design that works with any theme
- 📰 Optional RSS feed enhancements for link posts with domain display
- 🎯 Category-specific: Only affects posts in your designated links category
- 🎨 Custom fields for link and via URLs per post
- 🔄 Shortcodes and variables for theme customization with domain support
- 🧱 Full Bricks Builder integration with dynamic data and domain tags
- 🔐 **NEW**: Enhanced security with nonce verification and input sanitization
- 🏗️ **NEW**: Modern WordPress plugin architecture
- 🔄 **NEW**: Improved GitHub auto-update system
- ⚙️ Customizable settings:
  - Custom category name for link posts
  - Customizable permalink symbol and position
  - RSS feed modifications toggle
  - Custom field titles and display options

## System Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Bricks Builder Integration

Link Blog and Go integrates seamlessly with Bricks Builder through both dynamic data tags and echo functions.

### Dynamic Data Tags

1. `{link_blog_link}` - The main link URL from your post
   - Basic usage: `{link_blog_link}`
   - As link: `{link_blog_link:link}`
   - With custom title: `{link_blog_link @title:'Read More'}`
   - Open in new tab: `{link_blog_link:link:newTab}`

2. `{link_blog_via}` - The via link URL from your post
   - Basic usage: `{link_blog_via}`
   - As link: `{link_blog_via:link}`
   - With custom title: `{link_blog_via @title:'Source'}`
   - Open in new tab: `{link_blog_via:link:newTab}`

3. `{link_blog_domain}` - The domain from main link
   - Basic usage: `{link_blog_domain}` (shows domain as text)
   - As link: `{link_blog_domain:link}` (links to full URL)
   - Custom link text: `{link_blog_domain @linkText:'Visit Site'}`
   - Open in new tab: `{link_blog_domain:link:newTab}`

4. `{link_blog_via_domain}` - The domain from via link
   - Basic usage: `{link_blog_via_domain}` (shows domain as text)
   - As link: `{link_blog_via_domain:link}` (links to full URL)
   - Custom link text: `{link_blog_via_domain @linkText:'Source Site'}`
   - Open in new tab: `{link_blog_via_domain:link:newTab}`

### Echo Functions

For more flexibility, you can use the following echo functions in Bricks Builder:

1. `link_blog_get_main_url` - Get just the main URL
   ```
   {echo:link_blog_get_main_url}
   ```

2. `link_blog_get_via_url` - Get just the via URL
   ```
   {echo:link_blog_get_via_url}
   ```

3. `link_blog_get_main_link` - Get formatted main link HTML
   ```
   {echo:link_blog_get_main_link}
   {echo:link_blog_get_main_link post_id="123"}
   {echo:link_blog_get_main_link title="Read More"}
   ```

4. `link_blog_get_via_link` - Get formatted via link HTML
   ```
   {echo:link_blog_get_via_link}
   {echo:link_blog_get_via_link post_id="123"}
   {echo:link_blog_get_via_link title="Source"}
   ```

5. `link_blog_get_main_domain` - Get just the domain name
   ```
   {echo:link_blog_get_main_domain}
   ```

6. `link_blog_get_via_domain` - Get just the via domain name
   ```
   {echo:link_blog_get_via_domain}
   ```

7. `link_blog_get_domain_link` - Get domain as a clickable link
   ```
   {echo:link_blog_get_domain_link}
   {echo:link_blog_get_domain_link target="_self"}
   ```

8. `link_blog_get_via_domain_link` - Get via domain as a clickable link
   ```
   {echo:link_blog_get_via_domain_link}
   {echo:link_blog_get_via_domain_link target="_self"}
   ```

### Using in Bricks Builder

1. Enable Custom Fields:
   - Go to Link Blog and Go settings
   - Enable "Custom Fields" option
   - Set your preferred titles for link and via fields

2. Enable Echo Functions:
   - Go to Bricks Settings > Custom Code
   - Enable "Code execution" for your user role
   - The plugin automatically registers its echo functions

3. In Bricks Builder:
   - Click any text element's dynamic data icon (lightning bolt)
   - Choose either dynamic tags or echo functions
   - For dynamic tags: Look for "Link Blog" group
   - For echo functions: Use the echo tag format shown above

4. Common Use Cases:
   - Display source link in post template:
     ```
     {link_blog_link:link @title:'Read Original Article'}
     ```
   - Show via attribution with custom title:
     ```
     {echo:link_blog_get_via_link title="Found via"}
     ```
   - Use raw URL in buttons or links:
     ```
     {echo:link_blog_get_main_url}
     ```

5. Tips:
   - Links are automatically detected from post content
   - Can be overridden with custom URLs in post editor
   - Works with both classic and block editor
   - Supports all Bricks dynamic data features
   - Echo functions provide more flexibility for custom formatting

## How It Works

1. Create a new post in WordPress
2. Add it to your links category (default: "Links")
3. Write your post like this example:

### Example Post (What You Write)

```
Title: Amazing New AI Breakthrough

This is fascinating! Researchers have developed a new AI model that can understand
context better than ever before. The implications for natural language processing
are huge.

Check out the details here:
https://example.com/ai-breakthrough

via https://news.ycombinator.com
```

### How It Appears on Your Blog

The plugin automatically formats your post to look like this:

```
★ Amazing New AI Breakthrough

This is fascinating! Researchers have developed a new AI model that can understand
context better than ever before. The implications for natural language processing
are huge.

Check out the details here:
https://example.com/ai-breakthrough

via https://news.ycombinator.com

→ example.com
```

### How It Appears in RSS Feed

In your RSS feed, the post is formatted for optimal reading in feed readers:

```
★ Amazing New AI Breakthrough

This is fascinating! Researchers have developed a new AI model that can understand
context better than ever before. The implications for natural language processing
are huge.

→ example.com
```

The plugin will automatically:
- Extract the first URL it finds in your post (or use your custom URL)
- Add a source attribution at the bottom
- Add a permalink symbol for easy reference (in the post content, not the title)
- Style the post distinctively (while staying compatible with your theme)
- Only apply these changes to posts in your designated links category
- Make URLs available as dynamic data in Bricks Builder

## Installation

1. Download the plugin
2. Upload to your WordPress site's `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure settings under Settings > Link Blog and Go

## Configuration

Access plugin settings under Settings > Link Blog and Go to customize:

- **Link Category Name**: Change the default "Links" category name
- **Permalink Symbol**: Change the default "★" symbol and its position
- **RSS Feed Modifications**: Toggle enhanced RSS feed formatting for link posts
- **Custom Fields**: Enable and customize titles for link and via fields
- **Per-Post Settings**: Custom URL fields in the post editor
- **Auto-Link Behavior**: Control automatic domain link appending

## Theme Customization

You can display link and via information in your theme using either shortcodes or variables:

### Shortcodes
```
[link_blog_link]              # Shows full URL
[link_blog_link title="Custom Title"]
[via_link]                    # Shows via URL
[via_link title="Found Via"]
[link_blog_domain]            # Shows domain only
[link_blog_domain link="false"]  # Domain as text only
[via_domain]                  # Shows via domain
[via_domain target="_self"]  # Opens in same tab
```

### Variables
```
{link_blog_link}              # Full URL link
{via_link}                    # Full via URL link
{link_blog_domain}            # Domain only link
{via_domain}                  # Via domain only link
```

## Key Features

### Category-Specific Processing
- Changes only apply to posts in your designated links category
- Other posts remain completely unaffected
- Easy to manage which posts get the link blog treatment

### Non-Destructive Formatting
- Preserves your original post titles
- Adds permalink symbols and formatting without modifying the database
- Easily reversible - disable the plugin to return to original formatting

### Custom Link Management
- Override automatic link detection with custom URLs
- Set custom via links independently of content
- Flexible theme integration options

### Security First
- All user inputs are sanitized and validated
- CSRF protection on all admin actions
- Secure URL handling with protocol validation
- Regular security updates

## Development Status

This plugin is now **stable** with enterprise-grade security and architecture. Version 1.3.0 represents a major milestone:

- ✅ Complete security overhaul
- ✅ Modern WordPress plugin architecture
- ✅ Enhanced error handling and logging
- ✅ Improved GitHub auto-update system
- ✅ Comprehensive input validation
- ✅ Full backward compatibility maintained

## Contributing

This is an open-source project and contributions are welcome! To contribute:

1. Fork the repo
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow WordPress coding standards
- Include proper security measures (nonces, sanitization, validation)
- Add comprehensive PHPDoc comments
- Test with multiple WordPress versions
- Ensure backward compatibility

## Support

- **Issues**: Report bugs or request features on [GitHub Issues](https://github.com/nerveband/link-blog-and-go/issues)
- **Documentation**: Check the [wiki](https://github.com/nerveband/link-blog-and-go/wiki) for detailed documentation
- **Updates**: Follow the [releases page](https://github.com/nerveband/link-blog-and-go/releases) for updates

## License

MIT License - see [LICENSE](LICENSE) for details.

## Author

Created by [Ashraf Ali](https://ashrafali.net)

---

*Note: This plugin is inspired by the classic link blog format popularized by sites like [Daring Fireball](https://daringfireball.net) and [Simon Willison's approach to link blogging](https://simonwillison.net/2024/Dec/22/link-blog/). Simon's article provides excellent insights into the value and practice of link blogging:*

> Sharing interesting links with commentary is a low effort, high value way to contribute to internet life at large.
> 
> — Simon Willison