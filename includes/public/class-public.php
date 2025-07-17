<?php
/**
 * Public Class
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public Class
 */
class Link_Blog_Public {
    
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
     * Constructor
     *
     * @param Link_Blog_Options $options Options instance
     * @param Link_Blog_URL_Extractor $url_extractor URL extractor instance
     */
    public function __construct(Link_Blog_Options $options, Link_Blog_URL_Extractor $url_extractor) {
        $this->options = $options;
        $this->url_extractor = $url_extractor;
        
        $this->setup_hooks();
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Content filtering
        add_filter('the_content', array($this, 'filter_content'), 10);
        add_filter('the_content', array($this, 'replace_variables'), 20);
        
        // Title filtering
        add_filter('the_title', array($this, 'filter_title'), 10, 2);
        
        // RSS filtering
        if ($this->options->get_option('modify_rss', false)) {
            add_filter('the_content_feed', array($this, 'filter_rss_content'));
            add_filter('the_title_rss', array($this, 'filter_rss_title'));
        }
        
        // Shortcodes
        add_action('init', array($this, 'register_shortcodes'));
        
        // Add styles
        add_action('wp_head', array($this, 'add_styles'));
        
        // Bricks Builder integration
        if (class_exists('Bricks\Elements')) {
            add_action('init', array($this, 'init_bricks_integration'));
        }
    }
    
    /**
     * Filter content
     *
     * @param string $content Post content
     * @return string Filtered content
     */
    public function filter_content($content) {
        global $post;
        
        if (!$post || !$this->is_link_post($post->ID)) {
            return $content;
        }
        
        // Only auto-append if enabled
        if (!$this->options->get_option('auto_append_links', true)) {
            return $content;
        }
        
        $appended_content = $this->get_appended_content($post->ID);
        
        if ($appended_content) {
            $content .= $appended_content;
        }
        
        return $content;
    }
    
    /**
     * Replace variables in content
     *
     * @param string $content Post content
     * @return string Content with variables replaced
     */
    public function replace_variables($content) {
        global $post;
        
        if (!$post || strpos($content, '{') === false) {
            return $content;
        }
        
        // Replace link blog variables
        $content = str_replace('{link_blog_link}', $this->get_main_url($post->ID), $content);
        $content = str_replace('{via_link}', $this->get_via_url($post->ID), $content);
        $content = str_replace('{link_blog_domain}', $this->get_main_domain($post->ID), $content);
        $content = str_replace('{via_domain}', $this->get_via_domain($post->ID), $content);
        
        return $content;
    }
    
    /**
     * Filter title
     *
     * @param string $title Post title
     * @param int $post_id Post ID
     * @return string Filtered title
     */
    public function filter_title($title, $post_id = null) {
        if (!$post_id || !$this->is_link_post($post_id)) {
            return $title;
        }
        
        if (!$this->options->get_option('show_permalink', true)) {
            return $title;
        }
        
        $symbol = $this->options->get_option('permalink_symbol', '★');
        $position = $this->options->get_option('permalink_position', 'before');
        
        if ($position === 'after') {
            return $title . ' ' . $symbol;
        } else {
            return $symbol . ' ' . $title;
        }
    }
    
    /**
     * Filter RSS content
     *
     * @param string $content RSS content
     * @return string Filtered content
     */
    public function filter_rss_content($content) {
        global $post;
        
        if (!$post || !$this->is_link_post($post->ID)) {
            return $content;
        }
        
        if (!$this->options->get_option('rss_show_source', true)) {
            return $content;
        }
        
        $main_url = $this->get_main_url($post->ID);
        if ($main_url) {
            $domain = $this->url_extractor->extract_domain($main_url);
            $content .= sprintf(
                '<p><strong>%s:</strong> <a href="%s">%s</a></p>',
                __('Source', 'link-blog-and-go'),
                esc_url($main_url),
                esc_html($domain)
            );
        }
        
        return $content;
    }
    
    /**
     * Filter RSS title
     *
     * @param string $title RSS title
     * @return string Filtered title
     */
    public function filter_rss_title($title) {
        global $post;
        
        if (!$post || !$this->is_link_post($post->ID)) {
            return $title;
        }
        
        if (!$this->options->get_option('rss_show_symbol', true)) {
            return $title;
        }
        
        $symbol = $this->options->get_option('permalink_symbol', '★');
        $position = $this->options->get_option('rss_symbol_position', 'before');
        
        if ($position === 'after') {
            return $title . ' ' . $symbol;
        } else {
            return $symbol . ' ' . $title;
        }
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('link_blog_link', array($this, 'shortcode_link_blog_link'));
        add_shortcode('via_link', array($this, 'shortcode_via_link'));
        add_shortcode('link_blog_domain', array($this, 'shortcode_link_blog_domain'));
        add_shortcode('via_domain', array($this, 'shortcode_via_domain'));
    }
    
    /**
     * Link blog link shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function shortcode_link_blog_link($atts) {
        global $post;
        
        if (!$post) {
            return '';
        }
        
        $url = $this->get_main_url($post->ID);
        
        if (!$url) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'title' => $this->options->get_option('link_blog_title', 'Link Blog Link'),
            'show_title' => $this->options->get_option('show_link_title', true) ? 'true' : 'false'
        ), $atts);
        
        $output = '';
        
        if ($atts['show_title'] === 'true') {
            $output .= '<strong>' . esc_html($atts['title']) . ':</strong> ';
        }
        
        $output .= '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
        
        return $output;
    }
    
    /**
     * Via link shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function shortcode_via_link($atts) {
        global $post;
        
        if (!$post) {
            return '';
        }
        
        $url = $this->get_via_url($post->ID);
        
        if (!$url) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'title' => $this->options->get_option('via_link_title', 'Via Link'),
            'show_title' => $this->options->get_option('show_via_title', true) ? 'true' : 'false'
        ), $atts);
        
        $output = '';
        
        if ($atts['show_title'] === 'true') {
            $output .= '<strong>' . esc_html($atts['title']) . ':</strong> ';
        }
        
        $output .= '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
        
        return $output;
    }
    
    /**
     * Link blog domain shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function shortcode_link_blog_domain($atts) {
        global $post;
        
        if (!$post) {
            return '';
        }
        
        $url = $this->get_main_url($post->ID);
        $domain = $this->get_main_domain($post->ID);
        
        if (!$url || !$domain) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'before' => $this->options->get_option('domain_before_text', '→ '),
            'after' => $this->options->get_option('domain_after_text', '')
        ), $atts);
        
        return sprintf(
            '%s<a href="%s">%s</a>%s',
            esc_html($atts['before']),
            esc_url($url),
            esc_html($domain),
            esc_html($atts['after'])
        );
    }
    
    /**
     * Via domain shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function shortcode_via_domain($atts) {
        global $post;
        
        if (!$post) {
            return '';
        }
        
        $url = $this->get_via_url($post->ID);
        $domain = $this->get_via_domain($post->ID);
        
        if (!$url || !$domain) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'before' => $this->options->get_option('via_domain_before_text', 'via '),
            'after' => $this->options->get_option('via_domain_after_text', '')
        ), $atts);
        
        return sprintf(
            '%s<a href="%s">%s</a>%s',
            esc_html($atts['before']),
            esc_url($url),
            esc_html($domain),
            esc_html($atts['after'])
        );
    }
    
    /**
     * Add styles
     */
    public function add_styles() {
        ?>
        <style>
        .link-blog-custom-link,
        .link-blog-via-link {
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #007cba;
            border-radius: 4px;
        }
        
        .link-blog-custom-link strong,
        .link-blog-via-link strong {
            color: #007cba;
        }
        
        .link-blog-domain-links {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .link-blog-domain-links a {
            text-decoration: none;
            color: #007cba;
            margin-right: 10px;
        }
        
        .link-blog-domain-links a:hover {
            text-decoration: underline;
        }
        </style>
        <?php
    }
    
    /**
     * Initialize Bricks Builder integration
     */
    public function init_bricks_integration() {
        // This will be handled by a separate Bricks integration class
        if (class_exists('Link_Blog_Bricks_Integration')) {
            new Link_Blog_Bricks_Integration($this->options, $this->url_extractor);
        }
    }
    
    /**
     * Get appended content for link posts
     *
     * @param int $post_id Post ID
     * @return string Appended content
     */
    private function get_appended_content($post_id) {
        $content = '';
        
        $main_url = $this->get_main_url($post_id);
        $via_url = $this->get_via_url($post_id);
        
        if ($main_url || $via_url) {
            $content .= '<div class="link-blog-domain-links">';
            
            if ($main_url) {
                $domain = $this->url_extractor->extract_domain($main_url);
                $before = $this->options->get_option('domain_before_text', '→ ');
                $after = $this->options->get_option('domain_after_text', '');
                
                $content .= sprintf(
                    '%s<a href="%s">%s</a>%s',
                    esc_html($before),
                    esc_url($main_url),
                    esc_html($domain),
                    esc_html($after)
                );
            }
            
            if ($via_url) {
                $domain = $this->url_extractor->extract_domain($via_url);
                $before = $this->options->get_option('via_domain_before_text', 'via ');
                $after = $this->options->get_option('via_domain_after_text', '');
                
                $content .= sprintf(
                    ' %s<a href="%s">%s</a>%s',
                    esc_html($before),
                    esc_url($via_url),
                    esc_html($domain),
                    esc_html($after)
                );
            }
            
            $content .= '</div>';
        }
        
        return $content;
    }
    
    /**
     * Check if post is in link category
     *
     * @param int $post_id Post ID
     * @return bool True if in link category
     */
    private function is_link_post($post_id) {
        $category_name = $this->options->get_option('category_name', 'Links');
        return $this->url_extractor->is_link_post($post_id, $category_name);
    }
    
    /**
     * Get main URL for post
     *
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    private function get_main_url($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        return $this->url_extractor->extract_main_url($post->post_content, $post_id);
    }
    
    /**
     * Get via URL for post
     *
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    private function get_via_url($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        return $this->url_extractor->extract_via_url($post->post_content, $post_id);
    }
    
    /**
     * Get main domain for post
     *
     * @param int $post_id Post ID
     * @return string|false Domain or false if not found
     */
    private function get_main_domain($post_id) {
        $url = $this->get_main_url($post_id);
        return $url ? $this->url_extractor->extract_domain($url) : false;
    }
    
    /**
     * Get via domain for post
     *
     * @param int $post_id Post ID
     * @return string|false Domain or false if not found
     */
    private function get_via_domain($post_id) {
        $url = $this->get_via_url($post_id);
        return $url ? $this->url_extractor->extract_domain($url) : false;
    }
}