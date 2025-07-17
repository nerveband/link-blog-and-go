<?php
/**
 * Legacy Update Checker - Redirects to new system
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Legacy Update Checker for backward compatibility
 */
class LinkBlogUpdateChecker {
    
    /**
     * Constructor
     *
     * @param string $plugin_file Plugin file path
     */
    public function __construct($plugin_file) {
        // This class is now deprecated
        // The new updater is handled by Link_Blog_Updater
        if (is_admin()) {
            add_action('admin_notices', array($this, 'show_deprecation_notice'));
        }
    }
    
    /**
     * Show deprecation notice
     */
    public function show_deprecation_notice() {
        if (current_user_can('manage_options')) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p>';
            echo '<strong>' . __('Link Blog and Go', 'link-blog-and-go') . '</strong>: ';
            echo __('The plugin has been updated with a new secure updater system.', 'link-blog-and-go');
            echo '</p>';
            echo '</div>';
        }
    }
}