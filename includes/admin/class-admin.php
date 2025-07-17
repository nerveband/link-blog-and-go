<?php
/**
 * Admin Class
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Class
 */
class Link_Blog_Admin {
    
    /**
     * Options instance
     *
     * @var Link_Blog_Options
     */
    private $options;
    
    /**
     * URL extractor instance
     *
     * @var Link_Blog_URL_Extractor
     */
    private $url_extractor;
    
    /**
     * Plugin version
     *
     * @var string
     */
    private $version;
    
    /**
     * Constructor
     *
     * @param Link_Blog_Options $options Options instance
     * @param Link_Blog_URL_Extractor $url_extractor URL extractor instance
     */
    public function __construct(Link_Blog_Options $options, Link_Blog_URL_Extractor $url_extractor) {
        $this->options = $options;
        $this->url_extractor = $url_extractor;
        $this->version = Link_Blog_Plugin::VERSION;
        
        $this->setup_hooks();
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_link_blog_create_category', array($this, 'ajax_create_category'));
        add_action('wp_ajax_link_blog_check_updates', array($this, 'ajax_check_updates'));
        add_action('wp_ajax_link_blog_force_update', array($this, 'ajax_force_update'));
        
        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box_data'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Link Blog and Go Settings', 'link-blog-and-go'),
            __('Link Blog and Go', 'link-blog-and-go'),
            'manage_options',
            'link-blog-settings',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Hook suffix
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ('settings_page_link-blog-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'link-blog-admin',
            plugin_dir_url(dirname(__FILE__)) . '../assets/css/admin.css',
            array(),
            $this->version
        );
        
        wp_enqueue_script(
            'link-blog-admin',
            plugin_dir_url(dirname(__FILE__)) . '../assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_localize_script('link-blog-admin', 'linkBlogAdmin', array(
            'nonce' => wp_create_nonce('link_blog_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'current_version' => $this->version,
            'strings' => array(
                'error_creating_category' => __('Error creating Links category', 'link-blog-and-go'),
                'error_network' => __('Network error occurred', 'link-blog-and-go'),
                'checking_updates' => __('Checking for updates...', 'link-blog-and-go'),
                'preparing_update' => __('Preparing update...', 'link-blog-and-go'),
                'redirecting' => __('Redirecting to WordPress update page...', 'link-blog-and-go')
            )
        ));
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        $this->check_links_category();
    }
    
    /**
     * Check if Links category exists
     */
    private function check_links_category() {
        $category_name = $this->options->get_option('category_name', 'Links');
        $category = get_category_by_slug(sanitize_title($category_name));
        
        if (!$category) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>';
            echo '<strong>' . __('Link Blog and Go:', 'link-blog-and-go') . '</strong> ';
            echo sprintf(
                __('The "%s" category doesn\'t exist. ', 'link-blog-and-go'),
                esc_html($category_name)
            );
            echo '<a href="' . admin_url('options-general.php?page=link-blog-settings') . '">';
            echo __('Create it now', 'link-blog-and-go');
            echo '</a>';
            echo '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'link-blog-and-go'));
        }
        
        // Load template
        $template_path = plugin_dir_path(dirname(__FILE__)) . '../templates/admin/settings-page.php';
        if (file_exists($template_path)) {
            $options = $this->options->get_options();
            $version = $this->version;
            include $template_path;
        }
    }
    
    /**
     * AJAX handler for creating category
     */
    public function ajax_create_category() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'link_blog_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'link-blog-and-go')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'link-blog-and-go')));
        }
        
        $category_name = $this->options->get_option('category_name', 'Links');
        
        // Check if category already exists
        if (get_category_by_slug(sanitize_title($category_name))) {
            wp_send_json_error(array('message' => __('Category already exists', 'link-blog-and-go')));
        }
        
        // Create category
        $category_id = wp_insert_category(array(
            'cat_name' => sanitize_text_field($category_name),
            'category_nicename' => sanitize_title($category_name),
            'category_description' => __('Link blog posts category', 'link-blog-and-go')
        ));
        
        if (is_wp_error($category_id)) {
            wp_send_json_error(array('message' => $category_id->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('Category created successfully', 'link-blog-and-go'),
            'category_id' => $category_id
        ));
    }
    
    /**
     * AJAX handler for checking updates
     */
    public function ajax_check_updates() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'link_blog_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'link-blog-and-go')));
        }
        
        // Check permissions
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'link-blog-and-go')));
        }
        
        // Get updater instance
        $plugin = Link_Blog_Plugin::get_instance();
        $updater = $plugin->get_updater();
        
        if (!$updater) {
            wp_send_json_error(array('message' => __('Updater not available', 'link-blog-and-go')));
        }
        
        // Check for updates (this would be implemented in the updater)
        $update_info = $updater->check_for_updates();
        
        if ($update_info) {
            wp_send_json_success(array(
                'has_update' => true,
                'latest_version' => $update_info['version'],
                'release_date' => $update_info['date'],
                'release_notes' => $update_info['notes'],
                'release_url' => $update_info['url']
            ));
        } else {
            wp_send_json_success(array(
                'has_update' => false,
                'message' => __('You have the latest version', 'link-blog-and-go')
            ));
        }
    }
    
    /**
     * AJAX handler for force update
     */
    public function ajax_force_update() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'link_blog_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'link-blog-and-go')));
        }
        
        // Check permissions
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'link-blog-and-go')));
        }
        
        // Generate update URL
        $plugin_file = plugin_basename(Link_Blog_Plugin::get_instance()->get_plugin_file());
        $update_url = wp_nonce_url(
            self_admin_url('update.php?action=upgrade-plugin&plugin=' . $plugin_file),
            'upgrade-plugin_' . $plugin_file
        );
        
        wp_send_json_success(array(
            'redirect_url' => $update_url
        ));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Only add if custom fields are enabled
        if (!$this->options->get_option('enable_custom_fields', false)) {
            return;
        }
        
        add_meta_box(
            'link_blog_custom_links',
            __('Link Blog Custom Links', 'link-blog-and-go'),
            array($this, 'render_meta_box'),
            'post',
            'normal',
            'default'
        );
    }
    
    /**
     * Render meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_meta_box($post) {
        // Add nonce field
        wp_nonce_field('link_blog_meta_box', 'link_blog_meta_box_nonce');
        
        // Get current values
        $custom_link = get_post_meta($post->ID, '_link_blog_custom_link', true);
        $custom_via = get_post_meta($post->ID, '_link_blog_custom_via', true);
        
        // Load template
        $template_path = plugin_dir_path(dirname(__FILE__)) . '../templates/admin/meta-box.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
    
    /**
     * Save meta box data
     *
     * @param int $post_id Post ID
     */
    public function save_meta_box_data($post_id) {
        // Check if nonce is set
        if (!isset($_POST['link_blog_meta_box_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['link_blog_meta_box_nonce'], 'link_blog_meta_box')) {
            return;
        }
        
        // Check if user has permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save custom link
        if (isset($_POST['link_blog_custom_link'])) {
            $custom_link = $this->url_extractor->get_validated_url($_POST['link_blog_custom_link']);
            if ($custom_link) {
                update_post_meta($post_id, '_link_blog_custom_link', $custom_link);
            } else {
                delete_post_meta($post_id, '_link_blog_custom_link');
            }
        }
        
        // Save custom via
        if (isset($_POST['link_blog_custom_via'])) {
            $custom_via = $this->url_extractor->get_validated_url($_POST['link_blog_custom_via']);
            if ($custom_via) {
                update_post_meta($post_id, '_link_blog_custom_via', $custom_via);
            } else {
                delete_post_meta($post_id, '_link_blog_custom_via');
            }
        }
    }
}