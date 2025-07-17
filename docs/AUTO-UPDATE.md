# Auto-Update System Documentation

Link Blog and Go includes a built-in auto-update system that allows the plugin to update directly from GitHub releases without needing to go through WordPress.org.

## How It Works

1. **Version Checking**: The plugin periodically checks the GitHub API for the latest release
2. **Update Notification**: When a new version is available, WordPress displays an update notice
3. **One-Click Updates**: Users can update directly from the WordPress admin panel
4. **Automatic Downloads**: The plugin downloads the latest release zip from GitHub

## Setup Instructions

### For Plugin Developers

1. **Update GitHub Username**: 
   - Edit `link-blog-and-go.php` and replace `your-username` with your GitHub username
   - Edit `includes/class-update-checker.php` and update the `$github_username` property

2. **Create a Release**:
   ```bash
   # Tag your release
   git tag -a v1.2.1 -m "Release version 1.2.1"
   
   # Push the tag
   git push origin v1.2.1
   ```

3. **GitHub Actions**: The workflow will automatically:
   - Create a GitHub release
   - Build a clean plugin zip file
   - Upload it as a release asset
   - Generate update metadata

### For Plugin Users

Once installed, the plugin will:
- Check for updates every 12 hours (WordPress default)
- Show update notifications in the plugins page
- Allow one-click updates like any other plugin

## Alternative Update Methods

### Method 1: GitHub Releases API (Default)
The plugin uses the GitHub Releases API to check for updates. This is automatic and requires no additional setup.

### Method 2: Update Info JSON
You can also host an `update-info.json` file that contains version information:

1. Host the JSON file on GitHub Pages or as a Gist
2. Update the plugin to check this URL instead of the GitHub API
3. Manually update the JSON file when releasing new versions

### Method 3: Third-Party Services
- **Kernl.us**: Professional WordPress plugin update service
- **WP Updates**: Self-hosted update server
- **Plugin Update Checker**: Popular library by YahnisElsts

## Security Considerations

1. **HTTPS Only**: All update checks and downloads use HTTPS
2. **Version Validation**: The plugin validates version numbers before updating
3. **WordPress Nonces**: Update actions are protected by WordPress security nonces
4. **Capability Checks**: Only users with `update_plugins` capability can perform updates

## Troubleshooting

### Updates Not Showing
1. Clear WordPress transients: The update check is cached for 6 hours
2. Check GitHub API rate limits: Unauthenticated requests are limited to 60/hour
3. Verify the GitHub repository is public

### Update Fails
1. Check file permissions in wp-content/plugins/
2. Ensure the hosting environment allows external HTTPS requests
3. Verify the release zip file structure is correct

### Manual Update
If automatic updates fail, users can always:
1. Download the latest release from GitHub
2. Upload via WordPress admin panel
3. Or extract to wp-content/plugins/ via FTP

## Customization

### Change Update Frequency
Add to `wp-config.php`:
```php
define('WP_CRON_LOCK_TIMEOUT', 3600); // Check every hour
```

### Add Authentication
For private repositories, add GitHub token:
```php
// In class-update-checker.php
'headers' => array(
    'Authorization' => 'token YOUR_GITHUB_TOKEN',
    'Accept' => 'application/vnd.github.v3+json',
)
```

### Custom Update Messages
Modify the `show_update_notification` method to customize update messages.

## Benefits

1. **Independence**: No need for WordPress.org approval
2. **Rapid Updates**: Push updates instantly
3. **Version Control**: Full control over release timing
4. **Private Plugins**: Works with private repositories (with auth)
5. **Custom Metadata**: Include custom update information

## Best Practices

1. **Semantic Versioning**: Always use semantic versioning (x.y.z)
2. **Release Notes**: Include detailed release notes in GitHub releases
3. **Testing**: Test updates on a staging site first
4. **Backwards Compatibility**: Ensure updates don't break existing installations
5. **User Communication**: Notify users of major changes

## FAQ

**Q: Will this conflict with WordPress.org?**
A: No, this system is designed for plugins not hosted on WordPress.org.

**Q: Can I use this with premium plugins?**
A: Yes, add license key validation to the update checker.

**Q: How do I rollback an update?**
A: Users can download previous releases from GitHub and install manually.

**Q: Does this work with multisite?**
A: Yes, network admins can update for all sites.

**Q: Can I disable auto-updates?**
A: Yes, users can disable auto-updates in WordPress 5.5+ settings.