<?php
/**
 * Uninstall Script
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has permission to uninstall
if (!current_user_can('activate_plugins')) {
    return;
}

// Check if the plugin file matches
if (__FILE__ !== WP_UNINSTALL_PLUGIN) {
    return;
}

/**
 * Remove plugin options
 */
function link_blog_remove_options() {
    // Remove main options
    delete_option('link_blog_options');
    
    // Remove activation flag
    delete_option('link_blog_activated');
    
    // Remove any cached update data
    delete_transient('link_blog_github_update_' . md5('https://api.github.com/repos/nerveband/link-blog-and-go/releases/latest'));
    
    // Remove any other transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_link_blog_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_link_blog_%'");
}

/**
 * Remove plugin meta data
 */
function link_blog_remove_meta_data() {
    global $wpdb;
    
    // Remove custom post meta
    $wpdb->delete($wpdb->postmeta, array('meta_key' => '_link_blog_custom_link'));
    $wpdb->delete($wpdb->postmeta, array('meta_key' => '_link_blog_custom_via'));
}

/**
 * Remove plugin categories (optional)
 */
function link_blog_remove_categories() {
    // Get the default category name
    $default_category_name = 'Links';
    
    // Check if we should remove the Links category
    // We only remove it if it's empty or if user specifically wants to remove it
    $category = get_category_by_slug(sanitize_title($default_category_name));
    
    if ($category) {
        // Get posts in this category
        $posts = get_posts(array(
            'category' => $category->term_id,
            'numberposts' => 1,
            'post_status' => 'any'
        ));
        
        // Only remove if no posts in category
        if (empty($posts)) {
            wp_delete_category($category->term_id);
        }
    }
}

/**
 * Clear scheduled events
 */
function link_blog_clear_scheduled_events() {
    // Clear any scheduled update checks
    wp_clear_scheduled_hook('link_blog_check_updates');
    
    // Clear any other scheduled events
    wp_clear_scheduled_hook('link_blog_cleanup');
}

/**
 * Remove user meta data
 */
function link_blog_remove_user_meta() {
    global $wpdb;
    
    // Remove any user meta related to the plugin
    $wpdb->delete($wpdb->usermeta, array('meta_key' => 'link_blog_dismissed_notices'));
}

/**
 * Clean up database
 */
function link_blog_cleanup_database() {
    global $wpdb;
    
    // Optimize tables that might have been affected
    $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->postmeta}");
}

// Only proceed with uninstall if explicitly requested
if (defined('LINK_BLOG_UNINSTALL_REMOVE_ALL_DATA') && LINK_BLOG_UNINSTALL_REMOVE_ALL_DATA) {
    // Perform full cleanup
    link_blog_remove_options();
    link_blog_remove_meta_data();
    link_blog_remove_categories();
    link_blog_clear_scheduled_events();
    link_blog_remove_user_meta();
    link_blog_cleanup_database();
} else {
    // Perform minimal cleanup (only remove plugin options)
    link_blog_remove_options();
    link_blog_clear_scheduled_events();
}

// Flush rewrite rules
flush_rewrite_rules();