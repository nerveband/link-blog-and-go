<?php
/**
 * Plugin Name: Link Blog and Go
 * Description: Transform your WordPress blog into a link blog - easily share and comment on interesting links you find across the web. <a href="https://github.com/nerveband/link-blog-and-go">GitHub Repository</a>
 * Version: 1.2.4
 * Author: Ashraf Ali
 * Author URI: https://ashrafali.net
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Plugin URI: https://github.com/nerveband/link-blog-and-go
 * GitHub Plugin URI: https://github.com/nerveband/link-blog-and-go
 * Update URI: https://github.com/nerveband/link-blog-and-go
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the update checker
require_once plugin_dir_path(__FILE__) . 'includes/class-update-checker.php';

// Initialize update checker
if (is_admin()) {
    new LinkBlogUpdateChecker(__FILE__);
}

// Add activation hook in global namespace
function activate_link_blog_plugin() {
    $link_blog_setup = new LinkBlogSetup();
    $link_blog_setup->create_link_category();
}
register_activation_hook(__FILE__, 'activate_link_blog_plugin');

/**
 * Link Blog Provider for Bricks Builder
 */
class Provider_Link_Blog {
    private $provider_name;
    private $link_blog_setup;
    public $tags = [];

    public function __construct($provider) {
        $this->provider_name = $provider;
        $this->link_blog_setup = new LinkBlogSetup();
        $this->register_tags();
    }

    public static function load_me() {
        return true; // Always load for now
    }

    private function register_tags() {
        $this->tags['link_blog_link'] = [
            'name'     => 'link_blog_link',
            'label'    => 'Link Blog Link',
            'group'    => 'Link Blog',
            'provider' => $this->provider_name,
            'args'     => [
                'title' => [
                    'label' => 'Link Title',
                    'type'  => 'text',
                ],
                'newTab' => [
                    'label' => 'Open in New Tab',
                    'type'  => 'checkbox',
                ],
            ],
        ];

        $this->tags['link_blog_via'] = [
            'name'     => 'link_blog_via',
            'label'    => 'Link Blog Via',
            'group'    => 'Link Blog',
            'provider' => $this->provider_name,
            'args'     => [
                'title' => [
                    'label' => 'Via Title',
                    'type'  => 'text',
                ],
                'newTab' => [
                    'label' => 'Open in New Tab',
                    'type'  => 'checkbox',
                ],
            ],
        ];

        $this->tags['link_blog_domain'] = [
            'name'     => 'link_blog_domain',
            'label'    => 'Link Blog Domain',
            'group'    => 'Link Blog',
            'provider' => $this->provider_name,
            'args'     => [
                'linkText' => [
                    'label' => 'Link Text (leave empty for domain)',
                    'type'  => 'text',
                ],
                'newTab' => [
                    'label' => 'Open in New Tab',
                    'type'  => 'checkbox',
                ],
            ],
        ];

        $this->tags['link_blog_via_domain'] = [
            'name'     => 'link_blog_via_domain',
            'label'    => 'Link Blog Via Domain',
            'group'    => 'Link Blog',
            'provider' => $this->provider_name,
            'args'     => [
                'linkText' => [
                    'label' => 'Link Text (leave empty for domain)',
                    'type'  => 'text',
                ],
                'newTab' => [
                    'label' => 'Open in New Tab',
                    'type'  => 'checkbox',
                ],
            ],
        ];
    }

    public function get_tags() {
        return $this->tags;
    }

    public function get_tag_value($tag, $post, $args = [], $context = 'text') {
        $options = get_option('link_blog_options', []);
        
        switch ($tag) {
            case 'link_blog_link':
                $url = $this->link_blog_setup->extract_url_from_content($post->post_content, $post->ID);
                if (!$url) return '';

                if ($context === 'link' || !empty($args['title'])) {
                    $title = !empty($args['title']) ? $args['title'] : 
                            (isset($options['link_blog_title']) ? $options['link_blog_title'] : 'Link Blog Link');
                    
                    return sprintf('<a href="%s"%s>%s</a>',
                        esc_url($url),
                        !empty($args['newTab']) ? ' target="_blank"' : '',
                        esc_html($title)
                    );
                }
                
                return esc_url($url);

            case 'link_blog_via':
                $url = $this->link_blog_setup->extract_via_url_from_content($post->post_content, $post->ID);
                if (!$url) return '';

                if ($context === 'link' || !empty($args['title'])) {
                    $title = !empty($args['title']) ? $args['title'] : 
                            (isset($options['via_link_title']) ? $options['via_link_title'] : 'Via Link');
                    
                    return sprintf('<a href="%s"%s>%s</a>',
                        esc_url($url),
                        !empty($args['newTab']) ? ' target="_blank"' : '',
                        esc_html($title)
                    );
                }
                
                return esc_url($url);

            case 'link_blog_domain':
                $url = $this->link_blog_setup->extract_url_from_content($post->post_content, $post->ID);
                if (!$url) return '';
                
                $domain = $this->link_blog_setup->extract_domain_from_url($url);
                
                if ($context === 'link' || !empty($args['linkText'])) {
                    $linkText = !empty($args['linkText']) ? $args['linkText'] : $domain;
                    
                    return sprintf('<a href="%s"%s>%s</a>',
                        esc_url($url),
                        !empty($args['newTab']) ? ' target="_blank"' : '',
                        esc_html($linkText)
                    );
                }
                
                return esc_html($domain);

            case 'link_blog_via_domain':
                $url = $this->link_blog_setup->extract_via_url_from_content($post->post_content, $post->ID);
                if (!$url) return '';
                
                $domain = $this->link_blog_setup->extract_domain_from_url($url);
                
                if ($context === 'link' || !empty($args['linkText'])) {
                    $linkText = !empty($args['linkText']) ? $args['linkText'] : $domain;
                    
                    return sprintf('<a href="%s"%s>%s</a>',
                        esc_url($url),
                        !empty($args['newTab']) ? ' target="_blank"' : '',
                        esc_html($linkText)
                    );
                }
                
                return esc_html($domain);
        }

        return '';
    }
}

class LinkBlogSetup {
    private $options;
    private $version = '1.2.4';

    public function __construct() {
        // Initialize options
        $this->options = get_option('link_blog_options');
        if (false === $this->options) {
            $this->options = $this->get_default_options();
            update_option('link_blog_options', $this->options);
        }

        // Add Bricks echo function filter
        add_filter('bricks/code/echo_function_names', array($this, 'register_echo_functions'));
        
        // Register Bricks dynamic tags using the proper filter
        add_filter('bricks/dynamic_tags_list', array($this, 'register_bricks_dynamic_tags'));
        add_filter('bricks/dynamic_data/render_tag', array($this, 'render_bricks_dynamic_tag'), 20, 3);
        add_filter('bricks/dynamic_data/render_content', array($this, 'render_bricks_content'), 20, 3);
        add_filter('bricks/frontend/render_data', array($this, 'render_bricks_content'), 20, 2);

        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'create_link_category'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_create_links_category', array($this, 'ajax_create_links_category'));
        add_action('wp_ajax_check_plugin_updates', array($this, 'ajax_check_plugin_updates'));
        add_action('wp_ajax_force_plugin_update', array($this, 'ajax_force_plugin_update'));
        add_filter('the_content', array($this, 'style_link_posts'));
        add_action('wp_head', array($this, 'add_link_styles'));
        add_action('admin_notices', array($this, 'check_links_category'));

        // Register shortcodes
        add_shortcode('link_blog_link', array($this, 'link_blog_link_shortcode'));
        add_shortcode('via_link', array($this, 'via_link_shortcode'));
        add_shortcode('link_blog_domain', array($this, 'link_blog_domain_shortcode'));
        add_shortcode('via_domain', array($this, 'via_domain_shortcode'));
        
        // Add filter for variable replacement
        add_filter('the_content', array($this, 'replace_link_variables'));

        // Add meta boxes for custom links
        add_action('add_meta_boxes', array($this, 'add_custom_link_meta_boxes'));
        add_action('save_post', array($this, 'save_custom_link_meta'));

        // Bricks Builder Integration
        if (class_exists('\Bricks\Elements')) {
            add_action('init', [$this, 'init_bricks_integration']);
        }

        // Only add RSS filters if enabled
        if (isset($this->options['modify_rss']) && $this->options['modify_rss']) {
            add_filter('the_content_feed', array($this, 'customize_link_feed'));
            add_filter('the_title_rss', array($this, 'customize_feed_title'));
        }
    }

    public function init_bricks_integration() {
        add_filter('bricks/dynamic_data/register_providers', function($providers) {
            $providers[] = 'link-blog';
            return $providers;
        });

        add_filter('bricks/dynamic_data/provider_classes', function($classes) {
            $classes['link-blog'] = 'Provider_Link_Blog';
            return $classes;
        });
    }

    /**
     * Register functions that can be used with Bricks echo tag
     */
    public function register_echo_functions() {
        return array(
            'link_blog_get_main_url',
            'link_blog_get_via_url',
            'link_blog_get_main_link',
            'link_blog_get_via_link',
            'link_blog_get_main_domain',
            'link_blog_get_via_domain',
            'link_blog_get_domain_link',
            'link_blog_get_via_domain_link'
        );
    }
    
    /**
     * Register dynamic tags for Bricks Builder
     */
    public function register_bricks_dynamic_tags($tags) {
        $tags[] = [
            'name' => '{link_blog_link}',
            'label' => 'Link Blog URL',
            'group' => 'Link Blog',
        ];
        
        $tags[] = [
            'name' => '{link_blog_via}',
            'label' => 'Link Blog Via URL',
            'group' => 'Link Blog',
        ];
        
        $tags[] = [
            'name' => '{link_blog_domain}',
            'label' => 'Link Blog Domain',
            'group' => 'Link Blog',
        ];
        
        $tags[] = [
            'name' => '{link_blog_via_domain}',
            'label' => 'Link Blog Via Domain',
            'group' => 'Link Blog',
        ];
        
        return $tags;
    }
    
    /**
     * Render Bricks dynamic tag values
     */
    public function render_bricks_dynamic_tag($tag, $post, $context = 'text') {
        if (!is_string($tag)) {
            return $tag;
        }
        
        $clean_tag = str_replace(['{', '}'], '', $tag);
        
        switch ($clean_tag) {
            case 'link_blog_link':
                return $this->extract_url_from_content($post->post_content, $post->ID);
                
            case 'link_blog_via':
                return $this->extract_via_url_from_content($post->post_content, $post->ID);
                
            case 'link_blog_domain':
                $url = $this->extract_url_from_content($post->post_content, $post->ID);
                return $url ? $this->extract_domain_from_url($url) : '';
                
            case 'link_blog_via_domain':
                $via_url = $this->extract_via_url_from_content($post->post_content, $post->ID);
                return $via_url ? $this->extract_domain_from_url($via_url) : '';
        }
        
        return $tag;
    }
    
    /**
     * Render content with Bricks dynamic tags
     */
    public function render_bricks_content($content, $post = null, $context = 'text') {
        if (!$post && isset($GLOBALS['post'])) {
            $post = $GLOBALS['post'];
        }
        
        if (!$post || strpos($content, '{link_blog_') === false) {
            return $content;
        }
        
        // Replace link blog tags
        $url = $this->extract_url_from_content($post->post_content, $post->ID);
        if ($url) {
            $content = str_replace('{link_blog_link}', $url, $content);
            $domain = $this->extract_domain_from_url($url);
            $content = str_replace('{link_blog_domain}', $domain, $content);
        }
        
        $via_url = $this->extract_via_url_from_content($post->post_content, $post->ID);
        if ($via_url) {
            $content = str_replace('{link_blog_via}', $via_url, $content);
            $via_domain = $this->extract_domain_from_url($via_url);
            $content = str_replace('{link_blog_via_domain}', $via_domain, $content);
        }
        
        return $content;
    }

    /**
     * Get the main URL for a post (for Bricks echo tag)
     */
    public function link_blog_get_main_url($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }

        // First check for custom field value
        $custom_link = get_post_meta($post_id, '_link_blog_custom_link', true);
        if (!empty($custom_link)) {
            return $custom_link;
        }

        // If no custom field, get from content
        $post = get_post($post_id);
        return $this->extract_url_from_content($post->post_content, $post_id);
    }

    /**
     * Extract domain from URL
     */
    public function extract_domain_from_url($url) {
        if (empty($url)) {
            return '';
        }
        
        $domain = parse_url($url, PHP_URL_HOST);
        
        // Remove www. prefix if present
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }
        
        return $domain;
    }

    /**
     * Get the main domain for a post (for Bricks echo tag)
     */
    public function link_blog_get_main_domain($post_id = null) {
        $url = $this->link_blog_get_main_url($post_id);
        return $this->extract_domain_from_url($url);
    }

    /**
     * Get the via domain for a post (for Bricks echo tag)
     */
    public function link_blog_get_via_domain($post_id = null) {
        $url = $this->link_blog_get_via_url($post_id);
        return $this->extract_domain_from_url($url);
    }

    /**
     * Get the via URL for a post (for Bricks echo tag)
     */
    public function link_blog_get_via_url($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }

        // First check for custom field value
        $custom_via = get_post_meta($post_id, '_link_blog_custom_via', true);
        if (!empty($custom_via)) {
            return $custom_via;
        }

        // If no custom field, get from content
        $post = get_post($post_id);
        return $this->extract_via_url_from_content($post->post_content, $post_id);
    }

    /**
     * Get formatted main link HTML (for Bricks echo tag)
     */
    public function link_blog_get_main_link($post_id = null, $title = '', $use_domain = false) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }
        $url = $this->extract_url_from_content(get_post($post_id)->post_content, $post_id);
        if (!$url) return '';

        $options = get_option('link_blog_options', []);
        $link_title = $title ?: (isset($options['link_blog_title']) ? $options['link_blog_title'] : 'Link Blog Link');
        $display_text = $use_domain ? $this->extract_domain_from_url($url) : $url;
        
        return sprintf('<div class="link-blog-custom-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
            esc_html($link_title),
            esc_url($url),
            esc_html($display_text)
        );
    }

    /**
     * Get formatted via link HTML (for Bricks echo tag)
     */
    public function link_blog_get_via_link($post_id = null, $title = '', $use_domain = false) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }
        $url = $this->extract_via_url_from_content(get_post($post_id)->post_content, $post_id);
        if (!$url) return '';

        $options = get_option('link_blog_options', []);
        $via_title = $title ?: (isset($options['via_link_title']) ? $options['via_link_title'] : 'Via Link');
        $display_text = $use_domain ? $this->extract_domain_from_url($url) : $url;
        
        return sprintf('<div class="link-blog-via-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
            esc_html($via_title),
            esc_url($url),
            esc_html($display_text)
        );
    }

    /**
     * Get domain link HTML (for Bricks echo tag)
     */
    public function link_blog_get_domain_link($post_id = null, $target = '_blank') {
        $url = $this->link_blog_get_main_url($post_id);
        if (!$url) return '';
        
        $domain = $this->extract_domain_from_url($url);
        return sprintf('<a href="%s" target="%s">%s</a>', 
            esc_url($url), 
            esc_attr($target), 
            esc_html($domain)
        );
    }

    /**
     * Get via domain link HTML (for Bricks echo tag)
     */
    public function link_blog_get_via_domain_link($post_id = null, $target = '_blank') {
        $url = $this->link_blog_get_via_url($post_id);
        if (!$url) return '';
        
        $domain = $this->extract_domain_from_url($url);
        return sprintf('<a href="%s" target="%s">%s</a>', 
            esc_url($url), 
            esc_attr($target), 
            esc_html($domain)
        );
    }

    /**
     * Get default plugin options
     */
    private function get_default_options() {
        return array(
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
    }

    public function enqueue_admin_assets($hook) {
        if ('settings_page_link-blog-settings' !== $hook) {
            return;
        }

        wp_enqueue_style('link-blog-admin', plugins_url('css/admin.css', __FILE__));
        wp_enqueue_script('link-blog-admin', plugins_url('js/admin.js', __FILE__), array('jquery'), $this->version, true);
        
        wp_localize_script('link-blog-admin', 'linkBlogSettings', array(
            'nonce' => wp_create_nonce('link_blog_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'currentVersion' => $this->version
        ));
    }

    public function add_plugin_page() {
        add_options_page(
            'Link Blog and Go Settings',
            'Link Blog and Go',
            'manage_options',
            'link-blog-settings',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap link-blog-admin">
            <div class="admin-header">
                <h1>🔗 Link Blog and Go <span class="version">v<?php echo esc_html($this->version); ?></span></h1>
                <p class="subtitle">Transform your WordPress blog into a link blog with automatic domain extraction and beautiful formatting.</p>
            </div>
            
            <div class="admin-grid">
                <!-- Settings Column -->
                <div class="admin-main">
                    <?php
                    // Check if Links category exists
                    $cat = get_category_by_slug('links');
                    if (!$cat) {
                        ?>
                        <div class="notice notice-warning">
                            <p>
                                <strong>Setup Required:</strong> The Links category doesn't exist yet. 
                                <button type="button" class="button button-secondary" id="create-links-category">Create Links Category</button>
                            </p>
                        </div>
                        <?php
                    }
                    ?>
                    
                    <div class="settings-section">
                        <div class="section-header">
                            <h2>⚙️ Configuration</h2>
                            <p>Configure how your link blog works and looks</p>
                        </div>
                        
                        <form method="post" action="options.php" class="link-blog-form">
                            <?php settings_fields('link_blog_options_group'); ?>
                            
                            <!-- Basic Settings -->
                            <div class="settings-card">
                                <div class="card-header">
                                    <h3>📁 Basic Settings</h3>
                                    <p>Essential configuration for your link blog</p>
                                </div>
                                <div class="card-content">
                                    <div class="setting-group">
                                        <label class="setting-label" for="category_name">
                                            <strong>Link Category Name</strong>
                                            <span class="help-text">Posts in this category will be formatted as link posts</span>
                                        </label>
                                        <input type="text" id="category_name" name="link_blog_options[category_name]" 
                                            value="<?php echo isset($this->options['category_name']) ? esc_attr($this->options['category_name']) : 'Links'; ?>" 
                                            class="regular-text preview-trigger" 
                                            placeholder="Links" />
                                    </div>
                                    
                                    <div class="setting-group">
                                        <label class="setting-label">
                                            <strong>Auto-Link Behavior</strong>
                                            <span class="help-text">Control how domain links are added to your posts</span>
                                        </label>
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="auto_append_links" name="link_blog_options[auto_append_links]" 
                                                <?php checked(isset($this->options['auto_append_links']) ? $this->options['auto_append_links'] : true); ?> 
                                                class="preview-trigger" />
                                            <span class="checkmark"></span>
                                            Automatically append domain links to posts
                                        </label>
                                        <div class="help-details">
                                            <p><strong>✅ Enabled:</strong> Domain links appear automatically at the end of posts</p>
                                            <p><strong>❌ Disabled:</strong> You must manually place shortcodes or variables</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Permalink Settings -->
                            <div class="settings-card">
                                <div class="card-header">
                                    <h3>🔗 Permalink Settings</h3>
                                    <p>Customize the permalink symbol display</p>
                                </div>
                                <div class="card-content">
                                    <div class="setting-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="show_permalink" name="link_blog_options[show_permalink]" 
                                                <?php checked(isset($this->options['show_permalink']) ? $this->options['show_permalink'] : true); ?> 
                                                class="preview-trigger" />
                                            <span class="checkmark"></span>
                                            Show permalink symbol
                                        </label>
                                    </div>
                                    
                                    <div class="setting-row">
                                        <div class="setting-group half">
                                            <label class="setting-label" for="permalink_symbol">
                                                <strong>Symbol</strong>
                                            </label>
                                            <input type="text" id="permalink_symbol" name="link_blog_options[permalink_symbol]" 
                                                value="<?php echo isset($this->options['permalink_symbol']) ? esc_attr($this->options['permalink_symbol']) : '★'; ?>" 
                                                class="small-text preview-trigger" 
                                                placeholder="★" />
                                        </div>
                                        
                                        <div class="setting-group half">
                                            <label class="setting-label" for="permalink_position">
                                                <strong>Position</strong>
                                            </label>
                                            <select name="link_blog_options[permalink_position]" id="permalink_position" class="preview-trigger">
                                                <option value="before" <?php selected(isset($this->options['permalink_position']) ? $this->options['permalink_position'] : 'before', 'before'); ?>>Before title</option>
                                                <option value="after" <?php selected(isset($this->options['permalink_position']) ? $this->options['permalink_position'] : 'before', 'after'); ?>>After title</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Domain Customization -->
                            <div class="settings-card">
                                <div class="card-header">
                                    <h3>🎨 Domain Text Customization</h3>
                                    <p>Customize how domain names appear in your posts</p>
                                </div>
                                <div class="card-content">
                                    <div class="setting-row">
                                        <div class="setting-group half">
                                            <label class="setting-label" for="domain_before_text">
                                                <strong>Main Domain Before Text</strong>
                                            </label>
                                            <input type="text" id="domain_before_text" name="link_blog_options[domain_before_text]" 
                                                value="<?php echo isset($this->options['domain_before_text']) ? esc_attr($this->options['domain_before_text']) : '→ '; ?>" 
                                                class="regular-text preview-trigger" 
                                                placeholder="→ " />
                                        </div>
                                        
                                        <div class="setting-group half">
                                            <label class="setting-label" for="domain_after_text">
                                                <strong>Main Domain After Text</strong>
                                            </label>
                                            <input type="text" id="domain_after_text" name="link_blog_options[domain_after_text]" 
                                                value="<?php echo isset($this->options['domain_after_text']) ? esc_attr($this->options['domain_after_text']) : ''; ?>" 
                                                class="regular-text preview-trigger" 
                                                placeholder="(optional)" />
                                        </div>
                                    </div>
                                    
                                    <div class="setting-row">
                                        <div class="setting-group half">
                                            <label class="setting-label" for="via_domain_before_text">
                                                <strong>Via Domain Before Text</strong>
                                            </label>
                                            <input type="text" id="via_domain_before_text" name="link_blog_options[via_domain_before_text]" 
                                                value="<?php echo isset($this->options['via_domain_before_text']) ? esc_attr($this->options['via_domain_before_text']) : 'via '; ?>" 
                                                class="regular-text preview-trigger" 
                                                placeholder="via " />
                                        </div>
                                        
                                        <div class="setting-group half">
                                            <label class="setting-label" for="via_domain_after_text">
                                                <strong>Via Domain After Text</strong>
                                            </label>
                                            <input type="text" id="via_domain_after_text" name="link_blog_options[via_domain_after_text]" 
                                                value="<?php echo isset($this->options['via_domain_after_text']) ? esc_attr($this->options['via_domain_after_text']) : ''; ?>" 
                                                class="regular-text preview-trigger" 
                                                placeholder="(optional)" />
                                        </div>
                                    </div>
                                    
                                    <div class="examples-box">
                                        <h4>Examples:</h4>
                                        <p><code>→ example.com</code> (default main domain format)</p>
                                        <p><code>via example.com</code> (default via domain format)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- RSS Feed Settings -->
                            <div class="settings-card">
                                <div class="card-header">
                                    <h3>📡 RSS Feed Settings</h3>
                                    <p>Customize how your link posts appear in RSS feeds</p>
                                </div>
                                <div class="card-content">
                                    <div class="setting-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="modify_rss" name="link_blog_options[modify_rss]" 
                                                <?php checked(isset($this->options['modify_rss']) ? $this->options['modify_rss'] : false); ?> 
                                                class="preview-trigger" />
                                            <span class="checkmark"></span>
                                            Modify RSS feed
                                        </label>
                                    </div>
                                    
                                    <div class="rss-options" style="margin-left: 30px; margin-top: 15px;">
                                        <div class="setting-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="rss_show_symbol" name="link_blog_options[rss_show_symbol]" 
                                                    <?php checked(isset($this->options['rss_show_symbol']) ? $this->options['rss_show_symbol'] : true); ?> 
                                                    class="preview-trigger" />
                                                <span class="checkmark"></span>
                                                Show symbol in RSS titles
                                            </label>
                                        </div>
                                        
                                        <div class="setting-row">
                                            <div class="setting-group half">
                                                <label class="setting-label" for="rss_symbol_position">
                                                    <strong>Symbol Position</strong>
                                                </label>
                                                <select name="link_blog_options[rss_symbol_position]" id="rss_symbol_position" class="preview-trigger">
                                                    <option value="before" <?php selected(isset($this->options['rss_symbol_position']) ? $this->options['rss_symbol_position'] : 'before', 'before'); ?>>Before title</option>
                                                    <option value="after" <?php selected(isset($this->options['rss_symbol_position']) ? $this->options['rss_symbol_position'] : 'before', 'after'); ?>>After title</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="setting-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="rss_show_source" name="link_blog_options[rss_show_source]" 
                                                    <?php checked(isset($this->options['rss_show_source']) ? $this->options['rss_show_source'] : true); ?> 
                                                    class="preview-trigger" />
                                                <span class="checkmark"></span>
                                                Show source link in RSS description
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Advanced Settings -->
                            <div class="settings-card">
                                <div class="card-header">
                                    <h3>🛠️ Advanced Settings</h3>
                                    <p>Custom fields and advanced features</p>
                                </div>
                                <div class="card-content">
                                    <div class="setting-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="enable_custom_fields" name="link_blog_options[enable_custom_fields]" 
                                                <?php checked(isset($this->options['enable_custom_fields']) ? $this->options['enable_custom_fields'] : false); ?> 
                                                class="preview-trigger" />
                                            <span class="checkmark"></span>
                                            Enable custom link fields
                                        </label>
                                        <div class="help-details">
                                            <p>Adds meta boxes to post editor for custom link and via URLs</p>
                                        </div>
                                    </div>
                                    
                                    <div class="custom-fields-options" style="margin-left: 30px; margin-top: 15px;">
                                        <div class="setting-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="show_link_title" name="link_blog_options[show_link_title]" 
                                                    <?php checked(isset($this->options['show_link_title']) ? $this->options['show_link_title'] : true); ?> 
                                                    class="preview-trigger" />
                                                <span class="checkmark"></span>
                                                Show "Link Blog Link" title
                                            </label>
                                        </div>
                                        
                                        <div class="setting-group">
                                            <label class="setting-label" for="link_blog_title">
                                                <strong>Link Blog Link Title</strong>
                                            </label>
                                            <input type="text" id="link_blog_title" name="link_blog_options[link_blog_title]" 
                                                value="<?php echo isset($this->options['link_blog_title']) ? esc_attr($this->options['link_blog_title']) : 'Link Blog Link'; ?>" 
                                                class="regular-text preview-trigger" />
                                        </div>
                                        
                                        <div class="setting-group">
                                            <label class="checkbox-label">
                                                <input type="checkbox" id="show_via_title" name="link_blog_options[show_via_title]" 
                                                    <?php checked(isset($this->options['show_via_title']) ? $this->options['show_via_title'] : true); ?> 
                                                    class="preview-trigger" />
                                                <span class="checkmark"></span>
                                                Show "Via Link" title
                                            </label>
                                        </div>
                                        
                                        <div class="setting-group">
                                            <label class="setting-label" for="via_link_title">
                                                <strong>Via Link Title</strong>
                                            </label>
                                            <input type="text" id="via_link_title" name="link_blog_options[via_link_title]" 
                                                value="<?php echo isset($this->options['via_link_title']) ? esc_attr($this->options['via_link_title']) : 'Via Link'; ?>" 
                                                class="regular-text preview-trigger" />
                                        </div>
                                        
                                        <div class="shortcodes-info">
                                            <h4>Available Shortcodes & Variables:</h4>
                                            <div class="shortcode-grid">
                                                <div class="shortcode-item">
                                                    <strong>Shortcodes:</strong>
                                                    <code>[link_blog_link]</code>
                                                    <code>[via_link]</code>
                                                    <code>[link_blog_domain]</code>
                                                    <code>[via_domain]</code>
                                                </div>
                                                <div class="shortcode-item">
                                                    <strong>Variables:</strong>
                                                    <code>{link_blog_link}</code>
                                                    <code>{via_link}</code>
                                                    <code>{link_blog_domain}</code>
                                                    <code>{via_domain}</code>
                                                </div>
                                                <div class="shortcode-item">
                                                    <strong>Bricks Builder Tags:</strong>
                                                    <code>{link_blog_link}</code>
                                                    <code>{link_blog_via}</code>
                                                    <code>{link_blog_domain}</code>
                                                    <code>{link_blog_via_domain}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="submit-section">
                                <?php submit_button('Save Settings', 'primary', 'submit', false, ['class' => 'save-button']); ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Live Preview -->
                    <div class="preview-section">
                        <div class="section-header">
                            <h2>👀 Live Preview</h2>
                            <p>See how your settings will look</p>
                        </div>
                        
                        <div class="preview-container">
                            <div class="preview-box">
                                <h4>Post Preview</h4>
                                <div class="preview-content" id="post-preview">
                                    <h2 id="preview-title">Amazing New Technology Revealed</h2>
                                    <p>This is fascinating! Company X has developed something incredible. Read more at <a href="https://example.com/tech-news">https://example.com/tech-news</a></p>
                                    <p class="source-link">→ <a href="https://example.com/tech-news">example.com</a></p>
                                    <p><small><em>Auto-added since no manual shortcodes used</em></small></p>
                                </div>
                            </div>
                            
                            <div class="preview-box" id="rss-preview-container">
                                <h4>RSS Feed Preview</h4>
                                <div class="preview-content">
                                    <pre id="rss-preview"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="admin-sidebar">
                    <!-- Quick Guide -->
                    <div class="sidebar-card">
                        <h3>📝 Quick Guide</h3>
                        <div class="guide-steps">
                            <div class="guide-step">
                                <span class="step-number">1</span>
                                <div>
                                    <strong>Create Post</strong>
                                    <p>Add a new post and assign it to your Links category</p>
                                </div>
                            </div>
                            <div class="guide-step">
                                <span class="step-number">2</span>
                                <div>
                                    <strong>Add URL</strong>
                                    <p>Include the URL you want to link to in your post content</p>
                                </div>
                            </div>
                            <div class="guide-step">
                                <span class="step-number">3</span>
                                <div>
                                    <strong>Publish</strong>
                                    <p>The plugin automatically formats your post with domain links</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Plugin Updates -->
                    <div class="sidebar-card">
                        <h3>🔄 Plugin Updates</h3>
                        <div id="update-status" class="update-status">
                            <p><strong>Current Version:</strong> v<?php echo esc_html($this->version); ?></p>
                            <div id="update-info"></div>
                        </div>
                        <p>
                            <button type="button" id="check-updates-btn" class="button button-secondary">
                                <span class="dashicons dashicons-update"></span>
                                Check for Updates
                            </button>
                            <button type="button" id="force-update-btn" class="button button-primary" style="display: none; margin-top: 10px; width: 100%;">
                                <span class="dashicons dashicons-download"></span>
                                Update Now
                            </button>
                        </p>
                        <div id="update-progress" style="display: none;">
                            <p><span class="spinner is-active"></span> <span id="update-progress-text">Checking for updates...</span></p>
                        </div>
                    </div>
                    
                    <!-- Changelog -->
                    <div class="sidebar-card">
                        <h3>📋 Recent Changes</h3>
                        <div class="changelog">
                            <div class="changelog-item">
                                <div class="version-badge">v1.2.4</div>
                                <div class="changelog-content">
                                    <strong>Manual Update System</strong>
                                    <ul>
                                        <li>Added manual update check button</li>
                                        <li>Real-time update status display</li>
                                        <li>Enhanced admin interface</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="changelog-item">
                                <div class="version-badge">v1.2.3</div>
                                <div class="changelog-content">
                                    <strong>Auto-Link Control & Bricks Builder</strong>
                                    <ul>
                                        <li>Added auto-link behavior toggle</li>
                                        <li>Fixed Bricks Builder dynamic tags</li>
                                        <li>Title display control options</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="changelog-item">
                                <div class="version-badge">v1.2.2</div>
                                <div class="changelog-content">
                                    <strong>Domain Text Customization</strong>
                                    <ul>
                                        <li>Customizable before/after text</li>
                                        <li>Enhanced shortcode attributes</li>
                                        <li>Better admin interface</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <a href="https://github.com/nerveband/link-blog-and-go/blob/main/CHANGELOG.md" target="_blank" class="view-all-link">View Full Changelog →</a>
                    </div>
                    
                    <!-- Support -->
                    <div class="sidebar-card">
                        <h3>💬 Support & Links</h3>
                        <div class="support-links">
                            <a href="https://github.com/nerveband/link-blog-and-go" target="_blank" class="support-link">
                                <span class="dashicons dashicons-github"></span>
                                GitHub Repository
                            </a>
                            <a href="https://github.com/nerveband/link-blog-and-go/issues" target="_blank" class="support-link">
                                <span class="dashicons dashicons-bug"></span>
                                Report Bug
                            </a>
                            <a href="https://github.com/nerveband/link-blog-and-go/issues" target="_blank" class="support-link">
                                <span class="dashicons dashicons-lightbulb"></span>
                                Request Feature
                            </a>
                            <a href="https://github.com/nerveband/link-blog-and-go/releases" target="_blank" class="support-link">
                                <span class="dashicons dashicons-download"></span>
                                View Releases
                            </a>
                        </div>
                        <div class="author-info">
                            <p><strong>Developed by:</strong> <a href="https://ashrafali.net" target="_blank">Ashraf Ali</a></p>
                            <p>⭐ Star the repository if you find this plugin useful!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'link_blog_options_group',
            'link_blog_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'link_blog_settings_section',
            'General Settings',
            array($this, 'section_info'),
            'link-blog-settings'
        );

        // Category settings
        add_settings_field(
            'category_name',
            'Link Category Name',
            array($this, 'category_name_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        // Permalink settings
        add_settings_field(
            'permalink_symbol',
            'Permalink Symbol',
            array($this, 'permalink_symbol_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'show_permalink',
            'Show Permalink Symbol',
            array($this, 'show_permalink_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'permalink_position',
            'Permalink Symbol Position',
            array($this, 'permalink_position_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        // RSS Feed settings
        add_settings_field(
            'modify_rss',
            'Modify RSS Feed',
            array($this, 'modify_rss_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'rss_show_symbol',
            'Show Symbol in RSS',
            array($this, 'rss_show_symbol_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'rss_symbol_position',
            'Symbol Position in RSS',
            array($this, 'rss_symbol_position_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'rss_show_source',
            'Show Source in RSS',
            array($this, 'rss_show_source_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        // Custom fields settings
        add_settings_field(
            'enable_custom_fields',
            'Enable Custom Fields',
            array($this, 'enable_custom_fields_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'link_blog_title',
            'Link Blog Title',
            array($this, 'link_blog_title_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'via_link_title',
            'Via Link Title',
            array($this, 'via_link_title_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        $defaults = $this->get_default_options();
        
        // Category settings
        $new_input['category_name'] = !empty($input['category_name']) ? 
            sanitize_text_field($input['category_name']) : $defaults['category_name'];
        
        // Permalink settings
        $new_input['permalink_symbol'] = !empty($input['permalink_symbol']) ? 
            sanitize_text_field($input['permalink_symbol']) : $defaults['permalink_symbol'];
        
        $new_input['show_permalink'] = isset($input['show_permalink']) ? 
            (bool)$input['show_permalink'] : $defaults['show_permalink'];
            
        $new_input['permalink_position'] = isset($input['permalink_position']) ? 
            sanitize_text_field($input['permalink_position']) : $defaults['permalink_position'];
        
        // RSS Feed settings
        $new_input['modify_rss'] = isset($input['modify_rss']) ? 
            (bool)$input['modify_rss'] : $defaults['modify_rss'];
            
        $new_input['rss_show_symbol'] = isset($input['rss_show_symbol']) ? 
            (bool)$input['rss_show_symbol'] : $defaults['rss_show_symbol'];
            
        $new_input['rss_symbol_position'] = isset($input['rss_symbol_position']) ? 
            sanitize_text_field($input['rss_symbol_position']) : $defaults['rss_symbol_position'];
            
        $new_input['rss_show_source'] = isset($input['rss_show_source']) ? 
            (bool)$input['rss_show_source'] : $defaults['rss_show_source'];
            
        // Auto-append links setting (checkbox - false if not set)
        $new_input['auto_append_links'] = isset($input['auto_append_links']) ? true : false;
            
        // Custom fields settings
        $new_input['enable_custom_fields'] = isset($input['enable_custom_fields']) ? 
            (bool)$input['enable_custom_fields'] : $defaults['enable_custom_fields'];
            
        $new_input['show_link_title'] = isset($input['show_link_title']) ? 
            (bool)$input['show_link_title'] : $defaults['show_link_title'];
            
        $new_input['show_via_title'] = isset($input['show_via_title']) ? 
            (bool)$input['show_via_title'] : $defaults['show_via_title'];
            
        $new_input['link_blog_title'] = !empty($input['link_blog_title']) ? 
            sanitize_text_field($input['link_blog_title']) : $defaults['link_blog_title'];
            
        $new_input['via_link_title'] = !empty($input['via_link_title']) ? 
            sanitize_text_field($input['via_link_title']) : $defaults['via_link_title'];
            
        // Domain formatting options
        $new_input['domain_before_text'] = isset($input['domain_before_text']) ? 
            sanitize_text_field($input['domain_before_text']) : $defaults['domain_before_text'];
            
        $new_input['domain_after_text'] = isset($input['domain_after_text']) ? 
            sanitize_text_field($input['domain_after_text']) : $defaults['domain_after_text'];
            
        $new_input['via_domain_before_text'] = isset($input['via_domain_before_text']) ? 
            sanitize_text_field($input['via_domain_before_text']) : $defaults['via_domain_before_text'];
            
        $new_input['via_domain_after_text'] = isset($input['via_domain_after_text']) ? 
            sanitize_text_field($input['via_domain_after_text']) : $defaults['via_domain_after_text'];
        
        return $new_input;
    }

    public function section_info() {
        echo 'Configure your link blog settings below:';
    }

    public function category_name_callback() {
        printf(
            '<input type="text" id="category_name" name="link_blog_options[category_name]" value="%s" />',
            isset($this->options['category_name']) ? esc_attr($this->options['category_name']) : 'Links'
        );
    }

    public function permalink_symbol_callback() {
        printf(
            '<input type="text" id="permalink_symbol" name="link_blog_options[permalink_symbol]" value="%s" />',
            isset($this->options['permalink_symbol']) ? esc_attr($this->options['permalink_symbol']) : '★'
        );
    }

    public function show_permalink_callback() {
        $show_permalink = isset($this->options['show_permalink']) ? $this->options['show_permalink'] : true;
        printf(
            '<input type="checkbox" id="show_permalink" name="link_blog_options[show_permalink]" %s />',
            checked($show_permalink, true, false)
        );
        echo '<label for="show_permalink"> Enable permalink symbol in post titles</label>';
    }

    public function permalink_position_callback() {
        $position = isset($this->options['permalink_position']) ? $this->options['permalink_position'] : 'prefix';
        ?>
        <select name="link_blog_options[permalink_position]" id="permalink_position">
            <option value="prefix" <?php selected($position, 'prefix'); ?>>Before title (Prefix)</option>
            <option value="suffix" <?php selected($position, 'suffix'); ?>>After title (Suffix)</option>
        </select>
        <?php
    }

    public function modify_rss_callback() {
        printf(
            '<input type="checkbox" id="modify_rss" name="link_blog_options[modify_rss]" value="1" %s />
            <label for="modify_rss">Modify RSS feed for link posts (adds source and symbol)</label>',
            isset($this->options['modify_rss']) && $this->options['modify_rss'] ? 'checked' : ''
        );
    }

    public function rss_show_symbol_callback() {
        printf(
            '<input type="checkbox" id="rss_show_symbol" name="link_blog_options[rss_show_symbol]" %s />
            <label for="rss_show_symbol">Show permalink symbol in RSS titles</label>',
            isset($this->options['rss_show_symbol']) && $this->options['rss_show_symbol'] ? 'checked' : ''
        );
    }

    public function rss_symbol_position_callback() {
        $position = isset($this->options['rss_symbol_position']) ? $this->options['rss_symbol_position'] : 'before';
        ?>
        <select name="link_blog_options[rss_symbol_position]" id="rss_symbol_position">
            <option value="before" <?php selected($position, 'before'); ?>>Before title</option>
            <option value="after" <?php selected($position, 'after'); ?>>After title</option>
        </select>
        <?php
    }

    public function rss_show_source_callback() {
        printf(
            '<input type="checkbox" id="rss_show_source" name="link_blog_options[rss_show_source]" %s />
            <label for="rss_show_source">Show source link in RSS description</label>',
            isset($this->options['rss_show_source']) && $this->options['rss_show_source'] ? 'checked' : ''
        );
    }

    public function enable_custom_fields_callback() {
        printf(
            '<input type="checkbox" id="enable_custom_fields" name="link_blog_options[enable_custom_fields]" %s />
            <label for="enable_custom_fields">Enable custom link fields</label>',
            isset($this->options['enable_custom_fields']) && $this->options['enable_custom_fields'] ? 'checked' : ''
        );
    }

    public function link_blog_title_callback() {
        printf(
            '<input type="text" id="link_blog_title" name="link_blog_options[link_blog_title]" value="%s" class="regular-text" />',
            isset($this->options['link_blog_title']) ? esc_attr($this->options['link_blog_title']) : 'Link Blog Link'
        );
    }

    public function via_link_title_callback() {
        printf(
            '<input type="text" id="via_link_title" name="link_blog_options[via_link_title]" value="%s" class="regular-text" />',
            isset($this->options['via_link_title']) ? esc_attr($this->options['via_link_title']) : 'Via Link'
        );
    }

    public function check_links_category() {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';
        
        $category = get_term_by('name', $category_name, 'category');
        if (!$category) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Link Blog and Go:</strong> The "<?php echo esc_html($category_name); ?>" category does not exist. 
                    This category is required for the plugin to work properly. 
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=category')); ?>">Would you like to create it now?</a>
                </p>
            </div>
            <?php
        }
    }

    public function style_link_posts($content) {
        global $post;
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';
        $show_permalink = isset($options['show_permalink']) ? $options['show_permalink'] : true;
        $permalink_position = isset($options['permalink_position']) ? $options['permalink_position'] : 'prefix';
        
        // Check if post is in the links category
        if (has_category($category_name, $post)) {
            // Extract first URL from content
            $url = $this->extract_url_from_content($content, $post->ID);
            if ($url) {
                $domain = $this->extract_domain_from_url($url);
                
                // Check if user has manually placed link blog shortcodes or variables
                $has_manual_placement = $this->has_manual_link_placement($content);
                
                // Check if auto-append is enabled and no manual placement exists
                $auto_append = isset($options['auto_append_links']) ? $options['auto_append_links'] : true;
                if ($auto_append && !$has_manual_placement) {
                    $before_text = isset($options['domain_before_text']) ? $options['domain_before_text'] : '→ ';
                    $after_text = isset($options['domain_after_text']) ? $options['domain_after_text'] : '';
                    
                    $content .= sprintf(
                        '<p class="source-link">%s<a href="%s">%s</a>%s</p>',
                        esc_html($before_text),
                        esc_url($url),
                        esc_html($domain),
                        esc_html($after_text)
                    );
                }
                
                // Add permalink if enabled
                if ($show_permalink) {
                    $permalink_symbol = isset($options['permalink_symbol']) ? $options['permalink_symbol'] : '★';
                    $permalink_html = sprintf(
                        '<a href="%s" class="permalink-symbol">%s</a>',
                        get_permalink(),
                        esc_html($permalink_symbol)
                    );
                    
                    // Add permalink to content instead of modifying title
                    if ($permalink_position === 'prefix') {
                        $content = $permalink_html . ' ' . $content;
                    } else {
                        $content .= ' ' . $permalink_html;
                    }
                }
            }
        }
        return $content;
    }

    /**
     * Check if content contains manual link blog placement (shortcodes or variables)
     */
    private function has_manual_link_placement($content) {
        // Check for shortcodes
        $shortcodes = array(
            '[link_blog_link',
            '[link_blog_domain',
            '[via_link',
            '[via_domain'
        );
        
        foreach ($shortcodes as $shortcode) {
            if (strpos($content, $shortcode) !== false) {
                return true;
            }
        }
        
        // Check for variables
        $variables = array(
            '{link_blog_link}',
            '{link_blog_domain}',
            '{via_link}',
            '{via_domain}'
        );
        
        foreach ($variables as $variable) {
            if (strpos($content, $variable) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function create_link_category() {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';
        
        if(!term_exists($category_name, 'category')) {
            wp_insert_term(
                $category_name,
                'category',
                array(
                    'slug' => sanitize_title($category_name)
                )
            );
        }
    }

    public function customize_link_feed($content) {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';

        if (has_category($category_name)) {
            global $post;
            
            // Get the source URL
            $source_url = $this->extract_url_from_content($post->post_content, $post->ID);
            
            // Format the feed content
            $feed_content = '<p>' . get_the_excerpt() . '</p>';
            if ($source_url) {
                $domain = $this->extract_domain_from_url($source_url);
                $feed_content .= sprintf(
                    '<p>→ <a href="%s">%s</a></p>',
                    esc_url($source_url),
                    esc_html($domain)
                );
            }
            
            return $feed_content;
        }
        return $content;
    }

    public function customize_feed_title($title) {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';
        $permalink_symbol = isset($options['permalink_symbol']) ? $options['permalink_symbol'] : '★';

        if (has_category($category_name)) {
            return $permalink_symbol . ' ' . $title;
        }
        return $title;
    }

    public function add_link_styles() {
        $options = get_option('link_blog_options');
        ?>
        <style type="text/css">
            .link-blog-post {
                margin-bottom: 2em;
            }
            .link-blog-post .source-link {
                font-size: 0.9em;
                color: #666;
                margin-top: 1em;
            }
            .link-blog-custom-link,
            .link-blog-via-link {
                font-size: 0.9em;
                color: #666;
                margin-top: 0.5em;
            }
            .link-blog-custom-link strong,
            .link-blog-via-link strong {
                color: #333;
            }
            .link-blog-custom-link a,
            .link-blog-via-link a {
                color: #0073aa;
                text-decoration: none;
            }
            .link-blog-custom-link a:hover,
            .link-blog-via-link a:hover {
                text-decoration: underline;
            }
        </style>
        <?php
    }

    public function ajax_create_links_category() {
        check_ajax_referer('link_blog_nonce', 'nonce');
        
        if (!current_user_can('manage_categories')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $category_name = isset($this->options['category_name']) ? $this->options['category_name'] : 'Links';
        $category_slug = sanitize_title($category_name);

        if (term_exists($category_slug, 'category')) {
            wp_send_json_error(array('message' => 'Category already exists'));
            return;
        }

        $result = wp_insert_term(
            $category_name,
            'category',
            array(
                'slug' => $category_slug
            )
        );

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Category created successfully'));
        }
    }

    /**
     * Check for plugin updates manually via AJAX
     */
    public function ajax_check_plugin_updates() {
        check_ajax_referer('link_blog_nonce', 'nonce');
        
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        // Clear transient to force fresh check
        $cache_key = 'link_blog_github_update_' . md5('https://api.github.com/repos/nerveband/link-blog-and-go/releases/latest');
        delete_transient($cache_key);
        
        // Initialize update checker if not already done
        if (!class_exists('LinkBlogUpdateChecker')) {
            require_once plugin_dir_path(__FILE__) . 'includes/class-update-checker.php';
        }
        
        $update_checker = new LinkBlogUpdateChecker(__FILE__);
        $github_data = $this->get_github_release_data();
        
        if (!$github_data) {
            wp_send_json_error(array(
                'message' => 'Unable to check for updates. Please check your internet connection and try again.'
            ));
        }
        
        $latest_version = ltrim($github_data['tag_name'], 'v');
        $current_version = $this->version;
        
        if (version_compare($current_version, $latest_version, '<')) {
            wp_send_json_success(array(
                'hasUpdate' => true,
                'currentVersion' => $current_version,
                'latestVersion' => $latest_version,
                'releaseDate' => date('F j, Y', strtotime($github_data['published_at'])),
                'releaseNotes' => wp_trim_words(strip_tags($github_data['body']), 50),
                'downloadUrl' => $this->get_release_download_url($github_data),
                'releaseUrl' => $github_data['html_url']
            ));
        } else {
            wp_send_json_success(array(
                'hasUpdate' => false,
                'currentVersion' => $current_version,
                'message' => 'You are running the latest version of Link Blog and Go.'
            ));
        }
    }
    
    /**
     * Force plugin update via AJAX
     */
    public function ajax_force_plugin_update() {
        check_ajax_referer('link_blog_nonce', 'nonce');
        
        if (!current_user_can('update_plugins')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }
        
        // Get latest release info
        $github_data = $this->get_github_release_data();
        if (!$github_data) {
            wp_send_json_error(array('message' => 'Unable to fetch update information'));
        }
        
        $download_url = $this->get_release_download_url($github_data);
        if (!$download_url) {
            wp_send_json_error(array('message' => 'Download URL not found'));
        }
        
        // Trigger WordPress update
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $plugin_slug = plugin_basename(__FILE__);
        $upgrade_url = wp_nonce_url(
            self_admin_url('update.php?action=upgrade-plugin&plugin=' . urlencode($plugin_slug)),
            'upgrade-plugin_' . $plugin_slug
        );
        
        wp_send_json_success(array(
            'redirectUrl' => $upgrade_url,
            'message' => 'Redirecting to WordPress update page...'
        ));
    }
    
    /**
     * Get GitHub release data
     */
    private function get_github_release_data() {
        $api_url = 'https://api.github.com/repos/nerveband/link-blog-and-go/releases/latest';
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($data['tag_name']) ? $data : false;
    }
    
    /**
     * Get release download URL
     */
    private function get_release_download_url($github_data) {
        if (!empty($github_data['assets'])) {
            foreach ($github_data['assets'] as $asset) {
                if ($asset['name'] === 'link-blog-and-go.zip') {
                    return $asset['browser_download_url'];
                }
            }
        }
        return $github_data['zipball_url'] ?? '';
    }

    /**
     * Handle the link_blog_link shortcode
     */
    public function link_blog_link_shortcode($atts) {
        global $post;
        $options = get_option('link_blog_options');
        if (!isset($options['enable_custom_fields']) || !$options['enable_custom_fields']) {
            return '';
        }

        $atts = shortcode_atts(array(
            'title' => isset($options['link_blog_title']) ? $options['link_blog_title'] : 'Link Blog Link'
        ), $atts);

        $url = $this->extract_url_from_content(get_the_content(), $post->ID);
        if (!$url) {
            return '';
        }

        $show_title = isset($options['show_link_title']) ? $options['show_link_title'] : true;
        if (!$show_title) {
            return sprintf('<div class="link-blog-custom-link"><a href="%s">%s</a></div>',
                esc_url($url),
                esc_url($url)
            );
        }

        return sprintf('<div class="link-blog-custom-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
            esc_html($atts['title']),
            esc_url($url),
            esc_url($url)
        );
    }

    /**
     * Handle the via_link shortcode
     */
    public function via_link_shortcode($atts) {
        global $post;
        $options = get_option('link_blog_options');
        if (!isset($options['enable_custom_fields']) || !$options['enable_custom_fields']) {
            return '';
        }

        $atts = shortcode_atts(array(
            'title' => isset($options['via_link_title']) ? $options['via_link_title'] : 'Via Link'
        ), $atts);

        $via_url = $this->extract_via_url_from_content(get_the_content(), $post->ID);
        if (!$via_url) {
            return '';
        }

        $show_title = isset($options['show_via_title']) ? $options['show_via_title'] : true;
        if (!$show_title) {
            return sprintf('<div class="link-blog-via-link"><a href="%s">%s</a></div>',
                esc_url($via_url),
                esc_url($via_url)
            );
        }

        return sprintf('<div class="link-blog-via-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
            esc_html($atts['title']),
            esc_url($via_url),
            esc_url($via_url)
        );
    }

    /**
     * Handle the link_blog_domain shortcode
     */
    public function link_blog_domain_shortcode($atts) {
        global $post;
        $options = get_option('link_blog_options');
        
        $atts = shortcode_atts(array(
            'link' => 'true',
            'target' => '_blank',
            'before' => isset($options['domain_before_text']) ? $options['domain_before_text'] : '→ ',
            'after' => isset($options['domain_after_text']) ? $options['domain_after_text'] : ''
        ), $atts);

        $url = $this->extract_url_from_content(get_the_content(), $post->ID);
        if (!$url) {
            return '';
        }

        $domain = $this->extract_domain_from_url($url);
        
        if ($atts['link'] === 'true') {
            return sprintf('%s<a href="%s" target="%s">%s</a>%s',
                esc_html($atts['before']),
                esc_url($url),
                esc_attr($atts['target']),
                esc_html($domain),
                esc_html($atts['after'])
            );
        }
        
        return esc_html($atts['before'] . $domain . $atts['after']);
    }

    /**
     * Handle the via_domain shortcode
     */
    public function via_domain_shortcode($atts) {
        global $post;
        $options = get_option('link_blog_options');
        
        $atts = shortcode_atts(array(
            'link' => 'true',
            'target' => '_blank',
            'before' => isset($options['via_domain_before_text']) ? $options['via_domain_before_text'] : 'via ',
            'after' => isset($options['via_domain_after_text']) ? $options['via_domain_after_text'] : ''
        ), $atts);

        $via_url = $this->extract_via_url_from_content(get_the_content(), $post->ID);
        if (!$via_url) {
            return '';
        }

        $domain = $this->extract_domain_from_url($via_url);
        
        if ($atts['link'] === 'true') {
            return sprintf('%s<a href="%s" target="%s">%s</a>%s',
                esc_html($atts['before']),
                esc_url($via_url),
                esc_attr($atts['target']),
                esc_html($domain),
                esc_html($atts['after'])
            );
        }
        
        return esc_html($atts['before'] . $domain . $atts['after']);
    }

    /**
     * Replace link variables in content
     */
    public function replace_link_variables($content) {
        global $post;
        $options = get_option('link_blog_options');
        if (!isset($options['enable_custom_fields']) || !$options['enable_custom_fields']) {
            return $content;
        }

        $url = $this->extract_url_from_content($content, $post->ID);
        $via_url = $this->extract_via_url_from_content($content, $post->ID);

        if ($url) {
            $show_title = isset($options['show_link_title']) ? $options['show_link_title'] : true;
            $link_title = isset($options['link_blog_title']) ? $options['link_blog_title'] : 'Link Blog Link';
            
            if ($show_title) {
                $link_html = sprintf('<div class="link-blog-custom-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
                    esc_html($link_title),
                    esc_url($url),
                    esc_url($url)
                );
            } else {
                $link_html = sprintf('<div class="link-blog-custom-link"><a href="%s">%s</a></div>',
                    esc_url($url),
                    esc_url($url)
                );
            }
            $content = str_replace('{link_blog_link}', $link_html, $content);
            
            // Add domain variable support
            $domain = $this->extract_domain_from_url($url);
            $before_text = isset($options['domain_before_text']) ? $options['domain_before_text'] : '→ ';
            $after_text = isset($options['domain_after_text']) ? $options['domain_after_text'] : '';
            $domain_html = sprintf('%s<a href="%s" target="_blank">%s</a>%s', 
                esc_html($before_text), 
                esc_url($url), 
                esc_html($domain),
                esc_html($after_text)
            );
            $content = str_replace('{link_blog_domain}', $domain_html, $content);
        }

        if ($via_url) {
            $show_title = isset($options['show_via_title']) ? $options['show_via_title'] : true;
            $via_title = isset($options['via_link_title']) ? $options['via_link_title'] : 'Via Link';
            
            if ($show_title) {
                $via_html = sprintf('<div class="link-blog-via-link"><strong>%s:</strong> <a href="%s">%s</a></div>',
                    esc_html($via_title),
                    esc_url($via_url),
                    esc_url($via_url)
                );
            } else {
                $via_html = sprintf('<div class="link-blog-via-link"><a href="%s">%s</a></div>',
                    esc_url($via_url),
                    esc_url($via_url)
                );
            }
            $content = str_replace('{via_link}', $via_html, $content);
            
            // Add via domain variable support
            $via_domain = $this->extract_domain_from_url($via_url);
            $via_before_text = isset($options['via_domain_before_text']) ? $options['via_domain_before_text'] : 'via ';
            $via_after_text = isset($options['via_domain_after_text']) ? $options['via_domain_after_text'] : '';
            $via_domain_html = sprintf('%s<a href="%s" target="_blank">%s</a>%s', 
                esc_html($via_before_text), 
                esc_url($via_url), 
                esc_html($via_domain),
                esc_html($via_after_text)
            );
            $content = str_replace('{via_domain}', $via_domain_html, $content);
        }

        return $content;
    }

    /**
     * Add meta boxes for custom link fields
     */
    public function add_custom_link_meta_boxes() {
        // Only add meta box if custom fields are enabled
        if (!isset($this->options['enable_custom_fields']) || !$this->options['enable_custom_fields']) {
            return;
        }

        add_meta_box(
            'link_blog_custom_links',
            'Custom Link Settings',
            array($this, 'render_custom_link_meta_box'),
            'post',
            'normal',
            'high'
        );
    }

    /**
     * Save custom link meta data
     */
    public function save_custom_link_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['link_blog_custom_links_nonce'])) {
            return;
        }

        // Verify the nonce
        if (!wp_verify_nonce($_POST['link_blog_custom_links_nonce'], 'link_blog_custom_links')) {
            return;
        }

        // If this is an autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Make sure custom fields are enabled
        if (!isset($this->options['enable_custom_fields']) || !$this->options['enable_custom_fields']) {
            return;
        }

        // Save custom link data
        $custom_link = isset($_POST['link_blog_custom_link']) ? $_POST['link_blog_custom_link'] : '';
        $custom_via = isset($_POST['link_blog_custom_via']) ? $_POST['link_blog_custom_via'] : '';

        // Only save if URLs are valid
        if (!empty($custom_link)) {
            $custom_link = esc_url_raw($custom_link);
            if ($custom_link) {
                update_post_meta($post_id, '_link_blog_custom_link', $custom_link);
            }
        } else {
            delete_post_meta($post_id, '_link_blog_custom_link');
        }

        if (!empty($custom_via)) {
            $custom_via = esc_url_raw($custom_via);
            if ($custom_via) {
                update_post_meta($post_id, '_link_blog_custom_via', $custom_via);
            }
        } else {
            delete_post_meta($post_id, '_link_blog_custom_via');
        }
    }

    /**
     * Render the custom link meta box
     */
    public function render_custom_link_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('link_blog_custom_links', 'link_blog_custom_links_nonce');

        // Get saved values
        $custom_link = get_post_meta($post->ID, '_link_blog_custom_link', true);
        $custom_via = get_post_meta($post->ID, '_link_blog_custom_via', true);

        // Auto-detect URLs if no custom values are set
        $content = $post->post_content;
        if (empty($custom_link)) {
            preg_match('/(https?:\/\/[^\s<>"]+)/i', $content, $matches);
            $custom_link = isset($matches[1]) ? $matches[1] : '';
        }

        if (empty($custom_via)) {
            if (preg_match('/via\s+(https?:\/\/[^\s<>"]+)/i', $content, $matches)) {
                $custom_via = $matches[1];
            }
        }

        // Get field titles from options
        $link_title = isset($this->options['link_blog_title']) ? $this->options['link_blog_title'] : 'Link Blog Link';
        $via_title = isset($this->options['via_link_title']) ? $this->options['via_link_title'] : 'Via Link';
        ?>
        <div class="link-blog-meta-box">
            <p>
                <label for="link_blog_custom_link"><strong><?php echo esc_html($link_title); ?>:</strong></label><br>
                <input type="url" id="link_blog_custom_link" name="link_blog_custom_link" 
                    value="<?php echo esc_attr($custom_link); ?>" class="widefat" 
                    placeholder="Enter URL or leave empty to auto-detect">
                <span class="description">URL will be auto-detected from content if left empty</span>
            </p>
            <p>
                <label for="link_blog_custom_via"><strong><?php echo esc_html($via_title); ?>:</strong></label><br>
                <input type="url" id="link_blog_custom_via" name="link_blog_custom_via" 
                    value="<?php echo esc_attr($custom_via); ?>" class="widefat"
                    placeholder="Enter URL or leave empty to auto-detect">
                <span class="description">URL will be auto-detected from content after 'via' if left empty</span>
            </p>
            <div class="link-blog-meta-box-actions">
                <button type="button" class="button" id="link-blog-detect-urls">
                    Re-detect URLs from Content
                </button>
                <button type="button" class="button" id="link-blog-clear-urls">
                    Clear Custom URLs
                </button>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            function detectUrls() {
                var content = '';
                if (wp.editor && wp.editor.getContent) {
                    content = wp.editor.getContent('content');
                } else if (document.getElementById('content')) {
                    content = document.getElementById('content').value;
                }
                
                if (!content) return;

                // Detect main URL
                var urlMatch = content.match(/(https?:\/\/[^\s<>"]+)/i);
                if (urlMatch) {
                    $('#link_blog_custom_link').val(urlMatch[1]);
                }
                
                // Detect via URL
                var viaMatch = content.match(/via\s+(https?:\/\/[^\s<>"]+)/i);
                if (viaMatch) {
                    $('#link_blog_custom_via').val(viaMatch[1]);
                }
            }

            $('#link-blog-detect-urls').click(function(e) {
                e.preventDefault();
                detectUrls();
            });

            $('#link-blog-clear-urls').click(function(e) {
                e.preventDefault();
                $('#link_blog_custom_link, #link_blog_custom_via').val('');
            });

            // Auto-detect URLs when the meta box loads if fields are empty
            if (!$('#link_blog_custom_link').val() && !$('#link_blog_custom_via').val()) {
                detectUrls();
            }

            // Listen for content changes and update URLs if fields are empty
            var contentUpdateTimer;
            $(document).on('input change', '#content', function() {
                clearTimeout(contentUpdateTimer);
                contentUpdateTimer = setTimeout(function() {
                    if (!$('#link_blog_custom_link').val() && !$('#link_blog_custom_via').val()) {
                        detectUrls();
                    }
                }, 1000);
            });

            // Also listen for Gutenberg editor changes
            if (wp.data && wp.data.subscribe) {
                wp.data.subscribe(function() {
                    var content = wp.data.select('core/editor').getEditedPostContent();
                    if (content && !$('#link_blog_custom_link').val() && !$('#link_blog_custom_via').val()) {
                        clearTimeout(contentUpdateTimer);
                        contentUpdateTimer = setTimeout(function() {
                            detectUrls();
                        }, 1000);
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Extract the main URL from content
     */
    public function extract_url_from_content($content, $post_id = null) {
        if ($post_id) {
            // First check for custom field value
            $custom_link = get_post_meta($post_id, '_link_blog_custom_link', true);
            if (!empty($custom_link)) {
                return $custom_link;
            }
        }
        
        // If no custom field or no post_id, try to find URL in content
        if (preg_match('/(https?:\/\/[^\s<>"]+)/i', $content, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Extract the via URL from content
     */
    public function extract_via_url_from_content($content, $post_id = null) {
        if ($post_id) {
            // First check for custom field value
            $custom_via = get_post_meta($post_id, '_link_blog_custom_via', true);
            if (!empty($custom_via)) {
                return $custom_via;
            }
        }

        // If no custom field or no post_id, try to find URL after "via"
        if (preg_match('/via\s+(https?:\/\/[^\s<>"]+)/i', $content, $matches)) {
            return $matches[1];
        }
        return false;
    }
}

// Global echo functions for Bricks Builder
if (!function_exists('link_blog_get_main_url')) {
    function link_blog_get_main_url($post_id = null) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_main_url($post_id);
    }
}

if (!function_exists('link_blog_get_via_url')) {
    function link_blog_get_via_url($post_id = null) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_via_url($post_id);
    }
}

if (!function_exists('link_blog_get_main_link')) {
    function link_blog_get_main_link($post_id = null, $title = '', $use_domain = false) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_main_link($post_id, $title, $use_domain);
    }
}

if (!function_exists('link_blog_get_via_link')) {
    function link_blog_get_via_link($post_id = null, $title = '', $use_domain = false) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_via_link($post_id, $title, $use_domain);
    }
}

if (!function_exists('link_blog_get_main_domain')) {
    function link_blog_get_main_domain($post_id = null) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_main_domain($post_id);
    }
}

if (!function_exists('link_blog_get_via_domain')) {
    function link_blog_get_via_domain($post_id = null) {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_via_domain($post_id);
    }
}

if (!function_exists('link_blog_get_domain_link')) {
    function link_blog_get_domain_link($post_id = null, $target = '_blank') {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_domain_link($post_id, $target);
    }
}

if (!function_exists('link_blog_get_via_domain_link')) {
    function link_blog_get_via_domain_link($post_id = null, $target = '_blank') {
        $setup = new LinkBlogSetup();
        return $setup->link_blog_get_via_domain_link($post_id, $target);
    }
}

// Initialize the plugin
$link_blog_setup = new LinkBlogSetup();