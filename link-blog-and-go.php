<?php
/**
 * Plugin Name: Link Blog and Go
 * Description: Transform your WordPress blog into a link blog - easily share and comment on interesting links you find across the web. <a href="https://github.com/nerveband/link-blog-and-go">GitHub Repository</a>
 * Version: 1.3.0
 * Author: Ashraf Ali
 * Author URI: https://ashrafali.net
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Plugin URI: https://github.com/nerveband/link-blog-and-go
 * GitHub Plugin URI: https://github.com/nerveband/link-blog-and-go
 * Update URI: https://github.com/nerveband/link-blog-and-go
 * Text Domain: link-blog-and-go
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LINK_BLOG_PLUGIN_FILE', __FILE__);
define('LINK_BLOG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LINK_BLOG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LINK_BLOG_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('LINK_BLOG_VERSION', '1.3.0');
define('LINK_BLOG_MINIMUM_WP_VERSION', '5.0');
define('LINK_BLOG_MINIMUM_PHP_VERSION', '7.4');

/**
 * Check system requirements
 */
function link_blog_check_requirements() {
    $requirements = array();
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), LINK_BLOG_MINIMUM_WP_VERSION, '<')) {
        $requirements[] = sprintf(
            __('WordPress %s or higher is required.', 'link-blog-and-go'),
            LINK_BLOG_MINIMUM_WP_VERSION
        );
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, LINK_BLOG_MINIMUM_PHP_VERSION, '<')) {
        $requirements[] = sprintf(
            __('PHP %s or higher is required.', 'link-blog-and-go'),
            LINK_BLOG_MINIMUM_PHP_VERSION
        );
    }
    
    return $requirements;
}

/**
 * Display requirements notice
 */
function link_blog_requirements_notice() {
    $requirements = link_blog_check_requirements();
    
    if (!empty($requirements)) {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>' . __('Link Blog and Go', 'link-blog-and-go') . '</strong><br>';
        echo implode('<br>', $requirements);
        echo '</p></div>';
    }
}

/**
 * Initialize plugin
 */
function link_blog_init() {
    // Check requirements
    $requirements = link_blog_check_requirements();
    
    if (!empty($requirements)) {
        add_action('admin_notices', 'link_blog_requirements_notice');
        return;
    }
    
    // Load plugin
    require_once LINK_BLOG_PLUGIN_PATH . 'includes/core/class-plugin.php';
    
    // Initialize plugin
    Link_Blog_Plugin::get_instance(LINK_BLOG_PLUGIN_FILE);
}

/**
 * Plugin activation
 */
function link_blog_activate() {
    // Check requirements
    $requirements = link_blog_check_requirements();
    
    if (!empty($requirements)) {
        deactivate_plugins(LINK_BLOG_PLUGIN_BASENAME);
        wp_die(
            '<strong>' . __('Link Blog and Go', 'link-blog-and-go') . '</strong><br>' .
            implode('<br>', $requirements),
            __('Plugin Activation Error', 'link-blog-and-go'),
            array('back_link' => true)
        );
    }
    
    // Load plugin for activation
    require_once LINK_BLOG_PLUGIN_PATH . 'includes/core/class-plugin.php';
    
    // Get plugin instance and activate
    $plugin = Link_Blog_Plugin::get_instance(LINK_BLOG_PLUGIN_FILE);
    $plugin->activate();
}

/**
 * Plugin deactivation
 */
function link_blog_deactivate() {
    // Load plugin for deactivation
    require_once LINK_BLOG_PLUGIN_PATH . 'includes/core/class-plugin.php';
    
    // Get plugin instance and deactivate
    $plugin = Link_Blog_Plugin::get_instance(LINK_BLOG_PLUGIN_FILE);
    $plugin->deactivate();
}

/**
 * Plugin uninstall
 */
function link_blog_uninstall() {
    // Load uninstall file
    require_once LINK_BLOG_PLUGIN_PATH . 'uninstall.php';
}

// Register hooks
register_activation_hook(__FILE__, 'link_blog_activate');
register_deactivation_hook(__FILE__, 'link_blog_deactivate');
register_uninstall_hook(__FILE__, 'link_blog_uninstall');

// Initialize plugin
add_action('plugins_loaded', 'link_blog_init');

// Global echo functions for Bricks Builder compatibility
if (!function_exists('link_blog_get_main_url')) {
    /**
     * Get main URL for post
     *
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    function link_blog_get_main_url($post_id = null) {
        if (!$post_id) {
            global $post;
            if (!$post) {
                return false;
            }
            $post_id = $post->ID;
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return false;
        }
        
        $url_extractor = $plugin->get_url_extractor();
        $post_obj = get_post($post_id);
        
        if (!$post_obj) {
            return false;
        }
        
        return $url_extractor->extract_main_url($post_obj->post_content, $post_id);
    }
}

if (!function_exists('link_blog_get_via_url')) {
    /**
     * Get via URL for post
     *
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    function link_blog_get_via_url($post_id = null) {
        if (!$post_id) {
            global $post;
            if (!$post) {
                return false;
            }
            $post_id = $post->ID;
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return false;
        }
        
        $url_extractor = $plugin->get_url_extractor();
        $post_obj = get_post($post_id);
        
        if (!$post_obj) {
            return false;
        }
        
        return $url_extractor->extract_via_url($post_obj->post_content, $post_id);
    }
}

if (!function_exists('link_blog_get_main_domain')) {
    /**
     * Get main domain for post
     *
     * @param int $post_id Post ID
     * @return string|false Domain or false if not found
     */
    function link_blog_get_main_domain($post_id = null) {
        $url = link_blog_get_main_url($post_id);
        if (!$url) {
            return false;
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return false;
        }
        
        $url_extractor = $plugin->get_url_extractor();
        return $url_extractor->extract_domain($url);
    }
}

if (!function_exists('link_blog_get_via_domain')) {
    /**
     * Get via domain for post
     *
     * @param int $post_id Post ID
     * @return string|false Domain or false if not found
     */
    function link_blog_get_via_domain($post_id = null) {
        $url = link_blog_get_via_url($post_id);
        if (!$url) {
            return false;
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return false;
        }
        
        $url_extractor = $plugin->get_url_extractor();
        return $url_extractor->extract_domain($url);
    }
}

if (!function_exists('link_blog_get_main_link')) {
    /**
     * Get formatted main link HTML
     *
     * @param int $post_id Post ID
     * @param string $title Link title
     * @param bool $use_domain Use domain as link text
     * @return string Formatted link HTML
     */
    function link_blog_get_main_link($post_id = null, $title = '', $use_domain = false) {
        $url = link_blog_get_main_url($post_id);
        if (!$url) {
            return '';
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return '';
        }
        
        $options = $plugin->get_options();
        $url_extractor = $plugin->get_url_extractor();
        
        $link_title = $title ?: $options->get_option('link_blog_title', 'Link Blog Link');
        $display_text = $use_domain ? $url_extractor->extract_domain($url) : $url;
        
        return sprintf(
            '<div class="link-blog-custom-link"><strong>%s:</strong> <a href="%s" rel="noopener noreferrer">%s</a></div>',
            esc_html($link_title),
            esc_url($url),
            esc_html($display_text)
        );
    }
}

if (!function_exists('link_blog_get_via_link')) {
    /**
     * Get formatted via link HTML
     *
     * @param int $post_id Post ID
     * @param string $title Link title
     * @param bool $use_domain Use domain as link text
     * @return string Formatted link HTML
     */
    function link_blog_get_via_link($post_id = null, $title = '', $use_domain = false) {
        $url = link_blog_get_via_url($post_id);
        if (!$url) {
            return '';
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return '';
        }
        
        $options = $plugin->get_options();
        $url_extractor = $plugin->get_url_extractor();
        
        $via_title = $title ?: $options->get_option('via_link_title', 'Via Link');
        $display_text = $use_domain ? $url_extractor->extract_domain($url) : $url;
        
        return sprintf(
            '<div class="link-blog-via-link"><strong>%s:</strong> <a href="%s" rel="noopener noreferrer">%s</a></div>',
            esc_html($via_title),
            esc_url($url),
            esc_html($display_text)
        );
    }
}

if (!function_exists('link_blog_get_domain_link')) {
    /**
     * Get domain link HTML
     *
     * @param int $post_id Post ID
     * @param string $target Link target
     * @return string Formatted domain link HTML
     */
    function link_blog_get_domain_link($post_id = null, $target = '_blank') {
        $url = link_blog_get_main_url($post_id);
        if (!$url) {
            return '';
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return '';
        }
        
        $url_extractor = $plugin->get_url_extractor();
        return $url_extractor->get_domain_link($url, '', $target);
    }
}

if (!function_exists('link_blog_get_via_domain_link')) {
    /**
     * Get via domain link HTML
     *
     * @param int $post_id Post ID
     * @param string $target Link target
     * @return string Formatted via domain link HTML
     */
    function link_blog_get_via_domain_link($post_id = null, $target = '_blank') {
        $url = link_blog_get_via_url($post_id);
        if (!$url) {
            return '';
        }
        
        $plugin = Link_Blog_Plugin::get_instance();
        if (!$plugin) {
            return '';
        }
        
        $url_extractor = $plugin->get_url_extractor();
        return $url_extractor->get_domain_link($url, '', $target);
    }
}