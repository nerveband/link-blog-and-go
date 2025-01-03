<?php
/**
 * Plugin Name: Link Blog and Go
 * Description: Transform your WordPress blog into a link blog - easily share and comment on interesting links you find across the web
 * Version: 1.0.0-beta
 * Author: Ashraf Ali
 * Author URI: https://ashrafali.net
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LinkBlogSetup {
    private $options;
    private $version = '1.0.0-beta';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'create_link_category'));
        add_filter('the_content', array($this, 'style_link_posts'));
        add_action('wp_head', array($this, 'add_link_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Only add RSS filters if enabled
        $options = get_option('link_blog_options');
        if (isset($options['modify_rss']) && $options['modify_rss']) {
            add_filter('the_content_feed', array($this, 'customize_link_feed'));
            add_filter('the_title_rss', array($this, 'customize_feed_title'));
        }
    }

    public function enqueue_admin_styles($hook) {
        if ('settings_page_link-blog-settings' !== $hook) {
            return;
        }
        wp_enqueue_style('link-blog-admin', plugins_url('css/admin.css', __FILE__));
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
        $this->options = get_option('link_blog_options');
        ?>
        <div class="wrap">
            <h1>Link Blog and Go <span class="version">v<?php echo esc_html($this->version); ?></span></h1>
            
            <div class="card">
                <h2>How to Use Link Blog and Go</h2>
                
                <h3>Creating Link Posts</h3>
                <ol>
                    <li>Create a new post in WordPress</li>
                    <li>Add it to your links category (default: "Links")</li>
                    <li>Use this format for your posts:
                        <pre>
Title: Name of what you're linking to

Your commentary about why this is interesting

Include the URL you're linking to somewhere in your text

Optional: Credit where you found the link with "via"</pre>
                    </li>
                </ol>

                <h3>Preview</h3>
                <div class="preview-container">
                    <div class="preview-box">
                        <h4>Original Post</h4>
                        <div class="preview-content">
                            <h2>Amazing New Technology Revealed</h2>
                            <p>This is fascinating! Company X has developed something incredible. Read more at https://example.com/tech-news</p>
                        </div>
                    </div>
                    <div class="preview-arrow">→</div>
                    <div class="preview-box">
                        <h4>Formatted Link Post</h4>
                        <div class="preview-content">
                            <h2>Amazing New Technology Revealed</h2>
                            <p>This is fascinating! Company X has developed something incredible. Read more at https://example.com/tech-news</p>
                            <p class="source-link">Source: <a href="https://example.com/tech-news">example.com</a></p>
                            <div class="permalink"><a href="#">★</a></div>
                        </div>
                    </div>
                </div>

                <?php if (isset($this->options['modify_rss']) && $this->options['modify_rss']): ?>
                <div class="preview-container">
                    <div class="preview-box">
                        <h4>Original RSS Feed</h4>
                        <div class="preview-content">
                            <pre>&lt;item&gt;
  &lt;title&gt;Amazing New Technology Revealed&lt;/title&gt;
  &lt;description&gt;This is fascinating...&lt;/description&gt;
&lt;/item&gt;</pre>
                        </div>
                    </div>
                    <div class="preview-arrow">→</div>
                    <div class="preview-box">
                        <h4>Modified RSS Feed</h4>
                        <div class="preview-content">
                            <pre>&lt;item&gt;
  &lt;title&gt;★ Amazing New Technology Revealed&lt;/title&gt;
  &lt;description&gt;This is fascinating...
    Source: &lt;a href="https://example.com"&gt;example.com&lt;/a&gt;
  &lt;/description&gt;
&lt;/item&gt;</pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('link_blog_options_group');
                do_settings_sections('link-blog-settings');
                submit_button();
                ?>
            </form>

            <div class="card">
                <h3>About the Author</h3>
                <p>Link Blog and Go is developed by <a href="https://ashrafali.net">Ashraf Ali</a>. Feel free to reach out if you need assistance!</p>
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

        add_settings_field(
            'category_name',
            'Link Category Name',
            array($this, 'category_name_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'permalink_symbol',
            'Permalink Symbol',
            array($this, 'permalink_symbol_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );

        add_settings_field(
            'modify_rss',
            'Modify RSS Feed',
            array($this, 'modify_rss_callback'),
            'link-blog-settings',
            'link_blog_settings_section'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        
        if(isset($input['category_name']))
            $new_input['category_name'] = sanitize_text_field($input['category_name']);
        
        if(isset($input['permalink_symbol']))
            $new_input['permalink_symbol'] = sanitize_text_field($input['permalink_symbol']);
        
        if(isset($input['modify_rss']))
            $new_input['modify_rss'] = (bool)$input['modify_rss'];
        
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

    public function modify_rss_callback() {
        printf(
            '<input type="checkbox" id="modify_rss" name="link_blog_options[modify_rss]" value="1" %s />
            <label for="modify_rss">Modify RSS feed for link posts (adds source and symbol)</label>',
            isset($this->options['modify_rss']) && $this->options['modify_rss'] ? 'checked' : ''
        );
    }

    // Remaining class methods
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

    public function style_link_posts($content) {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';
        $permalink_symbol = isset($options['permalink_symbol']) ? $options['permalink_symbol'] : '★';

        if (is_single() && has_category($category_name)) {
            // Get the source URL from the first URL in the post
            $post_content = get_the_content();
            preg_match('/(https?:\/\/[^\s<>"]+|www\.[^\s<>"]+)/', $post_content, $match);
            $source_url = $match[0] ?? '';
            
            // Add source link and permalink star
            $formatted_content = $content;
            if ($source_url) {
                $formatted_content .= sprintf(
                    '<p class="source-link">Source: <a href="%s">%s</a></p>',
                    esc_url($source_url),
                    parse_url($source_url, PHP_URL_HOST)
                );
            }
            $formatted_content .= sprintf(
                '<div class="permalink"><a href="%s">&nbsp;%s&nbsp;</a></div>',
                get_permalink(),
                $permalink_symbol
            );
            
            return $formatted_content;
        }
        return $content;
    }

    public function customize_link_feed($content) {
        $options = get_option('link_blog_options');
        $category_name = isset($options['category_name']) ? $options['category_name'] : 'Links';

        if (has_category($category_name)) {
            global $post;
            
            // Get the source URL
            preg_match('/(https?:\/\/[^\s<>"]+|www\.[^\s<>"]+)/', $post->post_content, $match);
            $source_url = $match[0] ?? '';
            
            // Format the feed content
            $feed_content = '<p>' . get_the_excerpt() . '</p>';
            if ($source_url) {
                $feed_content .= sprintf(
                    '<p>Source: <a href="%s">%s</a></p>',
                    esc_url($source_url),
                    parse_url($source_url, PHP_URL_HOST)
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
        ?>
        <style type="text/css">
            .source-link {
                margin-top: 2em;
                font-style: italic;
            }
            .permalink {
                margin-top: 1em;
                text-align: right;
            }
            .permalink a {
                text-decoration: none;
                color: #666;
            }
        </style>
        <?php
    }
}

// Create the CSS file
function create_admin_css() {
    $css = <<<CSS
.version {
    font-size: 12px;
    color: #666;
    font-weight: normal;
}

.card {
    max-width: 800px;
    padding: 20px;
    background: #fff;
    margin: 20px 0;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.preview-container {
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.preview-box {
    flex: 1;
    min-width: 300px;
    border: 1px solid #ddd;
    padding: 15px;
    background: #f9f9f9;
}

.preview-arrow {
    font-size: 24px;
    color: #666;
}

.preview-content {
    background: white;
    padding: 15px;
    border: 1px solid #eee;
}

pre {
    background: #f5f5f5;
    padding: 10px;
    margin: 10px 0;
    overflow-x: auto;
}

ol, ul {
    margin-left: 20px;
}

ol {
    list-style-type: decimal;
}

ul {
    list-style-type: disc;
}
CSS;

    $css_dir = plugin_dir_path(__FILE__) . 'css';
    if (!file_exists($css_dir)) {
        mkdir($css_dir, 0755, true);
    }
    file_put_contents($css_dir . '/admin.css', $css);
}

// Initialize the plugin
if (is_admin()) {
    $link_blog_setup = new LinkBlogSetup();
}

// Add activation hook
function activate_link_blog_plugin() {
    $link_blog_setup = new LinkBlogSetup();
    $link_blog_setup->create_link_category();
    create_admin_css();
}
register_activation_hook(__FILE__, 'activate_link_blog_plugin');