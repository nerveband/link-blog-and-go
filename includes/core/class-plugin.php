<?php
/**
 * Main Plugin Class
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 */
class Link_Blog_Plugin {
    
    /**
     * Plugin version
     */
    const VERSION = '1.3.0';
    
    /**
     * Plugin slug
     */
    const SLUG = 'link-blog-and-go';
    
    /**
     * Plugin text domain
     */
    const TEXT_DOMAIN = 'link-blog-and-go';
    
    /**
     * Plugin instance
     *
     * @var Link_Blog_Plugin
     */
    private static $instance = null;
    
    /**
     * Admin instance
     *
     * @var Link_Blog_Admin
     */
    private $admin;
    
    /**
     * Public instance
     *
     * @var Link_Blog_Public
     */
    private $public;
    
    /**
     * Updater instance
     *
     * @var Link_Blog_Updater
     */
    private $updater;
    
    /**
     * Options manager
     *
     * @var Link_Blog_Options
     */
    private $options;
    
    /**
     * URL extractor
     *
     * @var Link_Blog_URL_Extractor
     */
    private $url_extractor;
    
    /**
     * Plugin file path
     *
     * @var string
     */
    private $plugin_file;
    
    /**
     * Plugin directory path
     *
     * @var string
     */
    private $plugin_dir;
    
    /**
     * Plugin URL
     *
     * @var string
     */
    private $plugin_url;
    
    /**
     * Constructor
     *
     * @param string $plugin_file Main plugin file path
     */
    private function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_dir = plugin_dir_path($plugin_file);
        $this->plugin_url = plugin_dir_url($plugin_file);
        
        $this->init();
    }
    
    /**
     * Get plugin instance
     *
     * @param string $plugin_file Main plugin file path
     * @return Link_Blog_Plugin
     */
    public static function get_instance($plugin_file = null) {
        if (is_null(self::$instance)) {
            self::$instance = new self($plugin_file);
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize components
        $this->options = new Link_Blog_Options();
        $this->url_extractor = new Link_Blog_URL_Extractor();
        
        // Initialize admin
        if (is_admin()) {
            $this->admin = new Link_Blog_Admin($this->options, $this->url_extractor);
            $this->updater = new Link_Blog_Updater($this->plugin_file);
        }
        
        // Initialize public
        $this->public = new Link_Blog_Public($this->options, $this->url_extractor);
        
        // Setup hooks
        $this->setup_hooks();
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        // Load autoloader
        require_once $this->plugin_dir . 'includes/class-autoloader.php';
        $autoloader = new Link_Blog_Autoloader();
        $autoloader->register();
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Activation/deactivation hooks
        register_activation_hook($this->plugin_file, array($this, 'activate'));
        register_deactivation_hook($this->plugin_file, array($this, 'deactivate'));
        
        // Plugin loaded
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        
        // Initialize
        add_action('init', array($this, 'init_plugin'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        $this->options->create_default_options();
        
        // Create link category
        $this->create_link_category();
        
        // Set activation flag
        add_option('link_blog_activated', true);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('link_blog_check_updates');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugins loaded callback
     */
    public function plugins_loaded() {
        // Load text domain
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname(plugin_basename($this->plugin_file)) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init_plugin() {
        // Check if activation is needed
        if (get_option('link_blog_activated')) {
            $this->create_link_category();
            delete_option('link_blog_activated');
        }
    }
    
    /**
     * Create link category
     */
    private function create_link_category() {
        $category_name = $this->options->get_option('category_name', 'Links');
        
        // Check if category exists
        if (!get_category_by_slug(sanitize_title($category_name))) {
            wp_insert_category(array(
                'cat_name' => sanitize_text_field($category_name),
                'category_nicename' => sanitize_title($category_name),
                'category_description' => __('Link blog posts category', self::TEXT_DOMAIN)
            ));
        }
    }
    
    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version() {
        return self::VERSION;
    }
    
    /**
     * Get plugin slug
     *
     * @return string
     */
    public function get_slug() {
        return self::SLUG;
    }
    
    /**
     * Get plugin text domain
     *
     * @return string
     */
    public function get_text_domain() {
        return self::TEXT_DOMAIN;
    }
    
    /**
     * Get plugin file path
     *
     * @return string
     */
    public function get_plugin_file() {
        return $this->plugin_file;
    }
    
    /**
     * Get plugin directory path
     *
     * @return string
     */
    public function get_plugin_dir() {
        return $this->plugin_dir;
    }
    
    /**
     * Get plugin URL
     *
     * @return string
     */
    public function get_plugin_url() {
        return $this->plugin_url;
    }
    
    /**
     * Get admin instance
     *
     * @return Link_Blog_Admin
     */
    public function get_admin() {
        return $this->admin;
    }
    
    /**
     * Get public instance
     *
     * @return Link_Blog_Public
     */
    public function get_public() {
        return $this->public;
    }
    
    /**
     * Get options instance
     *
     * @return Link_Blog_Options
     */
    public function get_options() {
        return $this->options;
    }
    
    /**
     * Get URL extractor instance
     *
     * @return Link_Blog_URL_Extractor
     */
    public function get_url_extractor() {
        return $this->url_extractor;
    }
}