<?php
/**
 * Options Manager
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Options Manager Class
 */
class Link_Blog_Options {
    
    /**
     * Options key
     */
    const OPTIONS_KEY = 'link_blog_options';
    
    /**
     * Options
     *
     * @var array
     */
    private $options;
    
    /**
     * Default options
     *
     * @var array
     */
    private $default_options = array(
        'category_name' => 'Links',
        'permalink_symbol' => '★',
        'show_permalink' => true,
        'permalink_position' => 'before',
        'modify_rss' => false,
        'rss_show_symbol' => true,
        'rss_symbol_position' => 'before',
        'rss_show_source' => true,
        'enable_custom_fields' => false,
        'show_link_title' => true,
        'show_via_title' => true,
        'auto_append_links' => true,
        'link_blog_title' => 'Link Blog Link',
        'via_link_title' => 'Via Link',
        'domain_before_text' => '→ ',
        'domain_after_text' => '',
        'via_domain_before_text' => 'via ',
        'via_domain_after_text' => ''
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_options();
        $this->setup_hooks();
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Load options
     */
    private function load_options() {
        $this->options = get_option(self::OPTIONS_KEY);
        
        if (false === $this->options) {
            $this->options = $this->default_options;
            update_option(self::OPTIONS_KEY, $this->options);
        }
        
        // Merge with defaults to ensure all keys exist
        $this->options = wp_parse_args($this->options, $this->default_options);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'link_blog_options_group',
            self::OPTIONS_KEY,
            array($this, 'sanitize_options')
        );
    }
    
    /**
     * Sanitize options
     *
     * @param array $input Raw input
     * @return array Sanitized options
     */
    public function sanitize_options($input) {
        $sanitized = array();
        
        // Sanitize text fields
        $text_fields = array(
            'category_name',
            'permalink_symbol',
            'link_blog_title',
            'via_link_title',
            'domain_before_text',
            'domain_after_text',
            'via_domain_before_text',
            'via_domain_after_text'
        );
        
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // Sanitize select fields
        $select_fields = array(
            'permalink_position' => array('before', 'after'),
            'rss_symbol_position' => array('before', 'after')
        );
        
        foreach ($select_fields as $field => $allowed_values) {
            if (isset($input[$field]) && in_array($input[$field], $allowed_values)) {
                $sanitized[$field] = $input[$field];
            }
        }
        
        // Sanitize boolean fields
        $boolean_fields = array(
            'show_permalink',
            'modify_rss',
            'rss_show_symbol',
            'rss_show_source',
            'enable_custom_fields',
            'show_link_title',
            'show_via_title',
            'auto_append_links'
        );
        
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? (bool) $input[$field] : false;
        }
        
        // Merge with existing options
        $sanitized = wp_parse_args($sanitized, $this->options);
        
        return $sanitized;
    }
    
    /**
     * Get option value
     *
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed Option value
     */
    public function get_option($key, $default = null) {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        
        if (isset($this->default_options[$key])) {
            return $this->default_options[$key];
        }
        
        return $default;
    }
    
    /**
     * Get all options
     *
     * @return array Options
     */
    public function get_options() {
        return $this->options;
    }
    
    /**
     * Update option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return bool Success
     */
    public function update_option($key, $value) {
        $this->options[$key] = $value;
        return update_option(self::OPTIONS_KEY, $this->options);
    }
    
    /**
     * Update multiple options
     *
     * @param array $options Options to update
     * @return bool Success
     */
    public function update_options($options) {
        $this->options = wp_parse_args($options, $this->options);
        return update_option(self::OPTIONS_KEY, $this->options);
    }
    
    /**
     * Delete option
     *
     * @param string $key Option key
     * @return bool Success
     */
    public function delete_option($key) {
        if (isset($this->options[$key])) {
            unset($this->options[$key]);
            return update_option(self::OPTIONS_KEY, $this->options);
        }
        
        return false;
    }
    
    /**
     * Reset options to defaults
     *
     * @return bool Success
     */
    public function reset_options() {
        $this->options = $this->default_options;
        return update_option(self::OPTIONS_KEY, $this->options);
    }
    
    /**
     * Create default options
     */
    public function create_default_options() {
        if (false === get_option(self::OPTIONS_KEY)) {
            add_option(self::OPTIONS_KEY, $this->default_options);
        }
    }
    
    /**
     * Get default options
     *
     * @return array Default options
     */
    public function get_default_options() {
        return $this->default_options;
    }
}