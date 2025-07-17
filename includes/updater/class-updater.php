<?php
/**
 * Secure GitHub Updater
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Secure GitHub Updater Class
 */
class Link_Blog_Updater {
    
    /**
     * Plugin file
     *
     * @var string
     */
    private $plugin_file;
    
    /**
     * Plugin slug
     *
     * @var string
     */
    private $plugin_slug;
    
    /**
     * GitHub username
     *
     * @var string
     */
    private $github_username = 'nerveband';
    
    /**
     * GitHub repository
     *
     * @var string
     */
    private $github_repo = 'link-blog-and-go';
    
    /**
     * GitHub API URL
     *
     * @var string
     */
    private $github_api_url;
    
    /**
     * Plugin data
     *
     * @var array
     */
    private $plugin_data;
    
    /**
     * Cache key
     *
     * @var string
     */
    private $cache_key;
    
    /**
     * Constructor
     *
     * @param string $plugin_file Plugin file path
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        );
        $this->cache_key = 'link_blog_github_update_' . md5($this->github_api_url);
        
        $this->setup_hooks();
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Only run in admin
        if (!is_admin()) {
            return;
        }
        
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'fix_plugin_folder'), 10, 3);
        add_action("after_plugin_row_{$this->plugin_slug}", array($this, 'show_update_notification'), 10, 2);
    }
    
    /**
     * Get plugin data
     *
     * @return array Plugin data
     */
    private function get_plugin_data() {
        if (is_null($this->plugin_data)) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $this->plugin_data = get_plugin_data($this->plugin_file);
        }
        
        return $this->plugin_data;
    }
    
    /**
     * Get GitHub release data
     *
     * @param bool $force_refresh Force refresh cache
     * @return array|false GitHub data or false on failure
     */
    private function get_github_data($force_refresh = false) {
        // Check cache first
        if (!$force_refresh) {
            $cached_data = get_transient($this->cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Make API request
        $response = wp_remote_get($this->github_api_url, array(
            'timeout' => 15,
            'sslverify' => true,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => $this->get_user_agent()
            )
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            $this->log_error('GitHub API request failed: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $this->log_error('GitHub API returned non-200 status: ' . $response_code);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Validate response
        if (!$this->validate_github_response($data)) {
            $this->log_error('Invalid GitHub API response');
            return false;
        }
        
        // Cache for 6 hours
        set_transient($this->cache_key, $data, 6 * HOUR_IN_SECONDS);
        
        return $data;
    }
    
    /**
     * Validate GitHub response
     *
     * @param array $data GitHub response data
     * @return bool True if valid
     */
    private function validate_github_response($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $required_fields = array('tag_name', 'name', 'body', 'published_at', 'html_url');
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        
        // Validate tag name format
        if (!preg_match('/^v?\d+\.\d+\.\d+/', $data['tag_name'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for update
     *
     * @param object $transient Update transient
     * @return object Modified transient
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $plugin_data = $this->get_plugin_data();
        $github_data = $this->get_github_data();
        
        if (!$github_data) {
            return $transient;
        }
        
        // Get versions
        $current_version = $plugin_data['Version'];
        $latest_version = ltrim($github_data['tag_name'], 'v');
        
        // Compare versions
        if (version_compare($current_version, $latest_version, '<')) {
            $download_url = $this->get_download_url($github_data);
            
            if ($download_url) {
                $plugin_update = array(
                    'slug' => dirname($this->plugin_slug),
                    'plugin' => $this->plugin_slug,
                    'new_version' => $latest_version,
                    'url' => $github_data['html_url'],
                    'package' => $download_url,
                    'icons' => $this->get_plugin_icons(),
                    'banners' => $this->get_plugin_banners(),
                    'tested' => $this->get_tested_version(),
                    'requires_php' => $this->get_required_php_version(),
                    'compatibility' => new stdClass(),
                );
                
                $transient->response[$this->plugin_slug] = (object) $plugin_update;
            }
        }
        
        return $transient;
    }
    
    /**
     * Get download URL
     *
     * @param array $github_data GitHub release data
     * @return string|false Download URL or false
     */
    private function get_download_url($github_data) {
        // First, try to find the plugin zip in release assets
        if (!empty($github_data['assets']) && is_array($github_data['assets'])) {
            foreach ($github_data['assets'] as $asset) {
                if ($asset['name'] === 'link-blog-and-go.zip') {
                    return $asset['browser_download_url'];
                }
            }
        }
        
        // Fallback to zipball URL
        return isset($github_data['zipball_url']) ? $github_data['zipball_url'] : false;
    }
    
    /**
     * Get plugin icons
     *
     * @return array Plugin icons
     */
    private function get_plugin_icons() {
        $plugin_url = plugin_dir_url($this->plugin_file);
        
        return array(
            '2x' => $plugin_url . 'assets/icon-256x256.png',
            '1x' => $plugin_url . 'assets/icon-128x128.png',
        );
    }
    
    /**
     * Get plugin banners
     *
     * @return array Plugin banners
     */
    private function get_plugin_banners() {
        $plugin_url = plugin_dir_url($this->plugin_file);
        
        return array(
            'low' => $plugin_url . 'assets/banner-772x250.png',
            'high' => $plugin_url . 'assets/banner-1544x500.png',
        );
    }
    
    /**
     * Get tested WordPress version
     *
     * @return string Tested version
     */
    private function get_tested_version() {
        return '6.4';
    }
    
    /**
     * Get required PHP version
     *
     * @return string Required PHP version
     */
    private function get_required_php_version() {
        return '7.4';
    }
    
    /**
     * Plugin info for WordPress admin
     *
     * @param false|object|array $result Result
     * @param string $action Action
     * @param object $args Arguments
     * @return false|object|array Modified result
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }
        
        if ($args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }
        
        $github_data = $this->get_github_data();
        if (!$github_data) {
            return $result;
        }
        
        $plugin_data = $this->get_plugin_data();
        
        $plugin_info = array(
            'name' => $plugin_data['Name'],
            'slug' => dirname($this->plugin_slug),
            'version' => ltrim($github_data['tag_name'], 'v'),
            'author' => $plugin_data['Author'],
            'author_profile' => $plugin_data['AuthorURI'],
            'last_updated' => $github_data['published_at'],
            'homepage' => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections' => array(
                'description' => $plugin_data['Description'],
                'changelog' => $this->parse_changelog($github_data['body']),
            ),
            'download_link' => $this->get_download_url($github_data),
            'tested' => $this->get_tested_version(),
            'requires_php' => $this->get_required_php_version(),
        );
        
        return (object) $plugin_info;
    }
    
    /**
     * Parse changelog from GitHub release body
     *
     * @param string $body Release body
     * @return string Parsed changelog
     */
    private function parse_changelog($body) {
        // Sanitize input
        $body = wp_kses_post($body);
        
        // Basic markdown to HTML conversion
        $body = wpautop($body);
        $body = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $body);
        $body = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $body);
        $body = preg_replace('/^- (.+?)$/m', '<li>$1</li>', $body);
        $body = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$1</ul>', $body);
        
        return $body;
    }
    
    /**
     * Fix plugin folder name after update
     *
     * @param string $source Source path
     * @param string $remote_source Remote source path
     * @param object $upgrader Upgrader object
     * @return string Corrected source path
     */
    public function fix_plugin_folder($source, $remote_source, $upgrader) {
        global $wp_filesystem;
        
        if (strpos($source, $this->github_repo) === false) {
            return $source;
        }
        
        $corrected_source = trailingslashit($remote_source) . 'link-blog-and-go/';
        
        if ($wp_filesystem->move($source, $corrected_source)) {
            return $corrected_source;
        }
        
        return $source;
    }
    
    /**
     * Show update notification
     *
     * @param string $file Plugin file
     * @param array $plugin Plugin data
     */
    public function show_update_notification($file, $plugin) {
        if (!current_user_can('update_plugins')) {
            return;
        }
        
        $github_data = $this->get_github_data();
        if (!$github_data) {
            return;
        }
        
        $latest_version = ltrim($github_data['tag_name'], 'v');
        $current_version = $this->get_plugin_data()['Version'];
        
        if (version_compare($current_version, $latest_version, '>=')) {
            return;
        }
        
        $update_url = wp_nonce_url(
            self_admin_url('update.php?action=upgrade-plugin&plugin=' . $file),
            'upgrade-plugin_' . $file
        );
        
        echo '<tr class="plugin-update-tr active"><td colspan="4" class="plugin-update">';
        echo '<div class="update-message notice inline notice-warning notice-alt">';
        echo '<p>';
        echo sprintf(
            __('There is a new version of %s available. ', 'link-blog-and-go'),
            esc_html($plugin['Name'])
        );
        echo '<a href="' . esc_url($github_data['html_url']) . '" target="_blank" rel="noopener noreferrer">';
        echo sprintf(__('View version %s details', 'link-blog-and-go'), esc_html($latest_version));
        echo '</a> ';
        echo __('or', 'link-blog-and-go') . ' ';
        echo '<a href="' . esc_url($update_url) . '">' . __('update now', 'link-blog-and-go') . '</a>.';
        echo '</p>';
        echo '</div></td></tr>';
    }
    
    /**
     * Check for updates (for manual checks)
     *
     * @return array|false Update info or false
     */
    public function check_for_updates() {
        $github_data = $this->get_github_data(true);
        
        if (!$github_data) {
            return false;
        }
        
        $current_version = $this->get_plugin_data()['Version'];
        $latest_version = ltrim($github_data['tag_name'], 'v');
        
        if (version_compare($current_version, $latest_version, '<')) {
            return array(
                'version' => $latest_version,
                'date' => $github_data['published_at'],
                'notes' => $github_data['body'],
                'url' => $github_data['html_url']
            );
        }
        
        return false;
    }
    
    /**
     * Get user agent for API requests
     *
     * @return string User agent string
     */
    private function get_user_agent() {
        return sprintf(
            'WordPress/%s; %s; LinkBlogAndGo/%s',
            get_bloginfo('version'),
            get_bloginfo('url'),
            Link_Blog_Plugin::VERSION
        );
    }
    
    /**
     * Log error
     *
     * @param string $message Error message
     */
    private function log_error($message) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Link Blog and Go Updater: ' . $message);
        }
    }
}