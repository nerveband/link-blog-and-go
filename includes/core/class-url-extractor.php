<?php
/**
 * URL Extractor
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL Extractor Class
 */
class Link_Blog_URL_Extractor {
    
    /**
     * Extract main URL from content
     *
     * @param string $content Post content
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    public function extract_main_url($content, $post_id = null) {
        // First check for custom field value
        if ($post_id) {
            $custom_link = get_post_meta($post_id, '_link_blog_custom_link', true);
            if (!empty($custom_link)) {
                return $this->validate_url($custom_link);
            }
        }
        
        // Extract from content
        return $this->extract_first_url($content);
    }
    
    /**
     * Extract via URL from content
     *
     * @param string $content Post content
     * @param int $post_id Post ID
     * @return string|false URL or false if not found
     */
    public function extract_via_url($content, $post_id = null) {
        // First check for custom field value
        if ($post_id) {
            $custom_via = get_post_meta($post_id, '_link_blog_custom_via', true);
            if (!empty($custom_via)) {
                return $this->validate_url($custom_via);
            }
        }
        
        // Extract from content after "via"
        return $this->extract_via_url_from_content($content);
    }
    
    /**
     * Extract first URL from content
     *
     * @param string $content Content to search
     * @return string|false URL or false if not found
     */
    private function extract_first_url($content) {
        // Sanitize content
        $content = wp_kses_post($content);
        
        // Pattern to match URLs
        $pattern = '/(https?:\/\/[^\s<>"\']+)/i';
        
        if (preg_match($pattern, $content, $matches)) {
            return $this->validate_url($matches[1]);
        }
        
        return false;
    }
    
    /**
     * Extract via URL from content
     *
     * @param string $content Content to search
     * @return string|false URL or false if not found
     */
    private function extract_via_url_from_content($content) {
        // Sanitize content
        $content = wp_kses_post($content);
        
        // Pattern to match URLs after "via"
        $pattern = '/\bvia\s+(https?:\/\/[^\s<>"\']+)/i';
        
        if (preg_match($pattern, $content, $matches)) {
            return $this->validate_url($matches[1]);
        }
        
        return false;
    }
    
    /**
     * Validate URL
     *
     * @param string $url URL to validate
     * @return string|false Validated URL or false if invalid
     */
    private function validate_url($url) {
        // Remove any trailing punctuation
        $url = rtrim($url, '.,;:!?');
        
        // Validate URL
        $validated = filter_var($url, FILTER_VALIDATE_URL);
        
        if ($validated && $this->is_allowed_protocol($validated)) {
            return esc_url_raw($validated);
        }
        
        return false;
    }
    
    /**
     * Check if URL protocol is allowed
     *
     * @param string $url URL to check
     * @return bool True if allowed
     */
    private function is_allowed_protocol($url) {
        $allowed_protocols = array('http', 'https');
        $protocol = parse_url($url, PHP_URL_SCHEME);
        
        return in_array($protocol, $allowed_protocols);
    }
    
    /**
     * Extract domain from URL
     *
     * @param string $url URL
     * @return string|false Domain or false if invalid
     */
    public function extract_domain($url) {
        if (empty($url)) {
            return false;
        }
        
        $validated_url = $this->validate_url($url);
        if (!$validated_url) {
            return false;
        }
        
        $domain = parse_url($validated_url, PHP_URL_HOST);
        
        if (!$domain) {
            return false;
        }
        
        // Remove www. prefix if present
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }
        
        return sanitize_text_field($domain);
    }
    
    /**
     * Get formatted domain link
     *
     * @param string $url URL
     * @param string $link_text Link text (optional)
     * @param string $target Link target (optional)
     * @return string Formatted link HTML
     */
    public function get_domain_link($url, $link_text = '', $target = '_blank') {
        $domain = $this->extract_domain($url);
        
        if (!$domain) {
            return '';
        }
        
        $display_text = !empty($link_text) ? sanitize_text_field($link_text) : $domain;
        
        return sprintf(
            '<a href="%s" target="%s" rel="noopener noreferrer">%s</a>',
            esc_url($url),
            esc_attr($target),
            esc_html($display_text)
        );
    }
    
    /**
     * Check if post is in link category
     *
     * @param int $post_id Post ID
     * @param string $category_name Category name
     * @return bool True if in link category
     */
    public function is_link_post($post_id, $category_name = 'Links') {
        $categories = get_the_category($post_id);
        
        foreach ($categories as $category) {
            if ($category->name === $category_name || $category->slug === sanitize_title($category_name)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get URL with validation
     *
     * @param string $url URL to validate
     * @return string|false Validated URL or false
     */
    public function get_validated_url($url) {
        return $this->validate_url($url);
    }
}