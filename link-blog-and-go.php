<?php
/**
 * Plugin Name: Link Blog and Go
 * Description: Transform your WordPress blog into a link blog - easily share and comment on interesting links you find across the web
 * Version: 1.1.0
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
    private $version = '1.1.0';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('init', array($this, 'create_link_category'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_create_links_category', array($this, 'ajax_create_links_category'));
        add_filter('the_content', array($this, 'style_link_posts'));
        add_action('wp_head', array($this, 'add_link_styles'));
        add_action('admin_notices', array($this, 'check_links_category'));

        // Only add RSS filters if enabled
        $options = get_option('link_blog_options');
        if (isset($options['modify_rss']) && $options['modify_rss']) {
            add_filter('the_content_feed', array($this, 'customize_link_feed'));
            add_filter('the_title_rss', array($this, 'customize_feed_title'));
        }
    }

    public function enqueue_admin_assets($hook) {
        if ('settings_page_link-blog-settings' !== $hook) {
            return;
        }

        wp_enqueue_style('link-blog-admin', plugins_url('css/admin.css', __FILE__));
        wp_enqueue_script('link-blog-admin', plugins_url('js/admin.js', __FILE__), array('jquery'), $this->version, true);
        
        wp_localize_script('link-blog-admin', 'linkBlogSettings', array(
            'nonce' => wp_create_nonce('link_blog_nonce')
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
        $this->options = get_option('link_blog_options');
        ?>
        <div class="wrap">
            <h1>Link Blog and Go <span class="version">v<?php echo esc_html($this->version); ?></span></h1>
            
            <div class="card guide-card">
                <h2>How to Write a Link Blog Post</h2>
                <div class="guide-steps">
                    <div class="guide-step">
                        <span class="step-number">1</span>
                        <h3>Create a New Post</h3>
                        <p>Click "Add New" in the Posts menu and write your post title.</p>
                    </div>
                    <div class="guide-step">
                        <span class="step-number">2</span>
                        <h3>Add to Links Category</h3>
                        <p>Select the "Links" category in the sidebar (or your custom category name).</p>
                    </div>
                    <div class="guide-step">
                        <span class="step-number">3</span>
                        <h3>Write Your Post</h3>
                        <p>Follow this format:</p>
                        <pre class="format-example">
Title: Name of what you're linking to

Your commentary about why this is interesting.

Include the URL you're linking to somewhere in your text:
https://example.com/article

Optional: Credit where you found the link with "via"</pre>
                    </div>
                    <div class="guide-step">
                        <span class="step-number">4</span>
                        <h3>Publish</h3>
                        <p>The plugin will automatically format your post with:</p>
                        <ul>
                            <li>Permalink symbol (if enabled)</li>
                            <li>Source attribution</li>
                            <li>RSS feed enhancements (if enabled)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php
            // Check if Links category exists
            $cat = get_category_by_slug('links');
            if (!$cat) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        The Links category doesn't exist yet. 
                        <button type="button" class="button button-secondary" id="create-links-category">Create Links Category</button>
                    </p>
                </div>
                <?php
            }
            ?>

            <div class="card settings-card">
                <form method="post" action="options.php">
                    <?php settings_fields('link_blog_options_group'); ?>
                    
                    <h2>General Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Link Category Name</th>
                            <td>
                                <input type="text" id="category_name" name="link_blog_options[category_name]" 
                                    value="<?php echo isset($this->options['category_name']) ? esc_attr($this->options['category_name']) : 'Links'; ?>" 
                                    class="regular-text preview-trigger" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Permalink Settings</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" id="show_permalink" name="link_blog_options[show_permalink]" 
                                            <?php checked(isset($this->options['show_permalink']) ? $this->options['show_permalink'] : true); ?> 
                                            class="preview-trigger" />
                                        Show permalink symbol
                                    </label>
                                    <br><br>
                                    <input type="text" id="permalink_symbol" name="link_blog_options[permalink_symbol]" 
                                        value="<?php echo isset($this->options['permalink_symbol']) ? esc_attr($this->options['permalink_symbol']) : '★'; ?>" 
                                        class="small-text preview-trigger" />
                                    <label for="permalink_symbol">Permalink symbol</label>
                                    <br><br>
                                    <select name="link_blog_options[permalink_position]" id="permalink_position" class="preview-trigger">
                                        <option value="before" <?php selected(isset($this->options['permalink_position']) ? $this->options['permalink_position'] : 'before', 'before'); ?>>Before title</option>
                                        <option value="after" <?php selected(isset($this->options['permalink_position']) ? $this->options['permalink_position'] : 'before', 'after'); ?>>After title</option>
                                    </select>
                                    <label for="permalink_position">Symbol position</label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">RSS Feed Settings</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" id="modify_rss" name="link_blog_options[modify_rss]" 
                                            <?php checked(isset($this->options['modify_rss']) ? $this->options['modify_rss'] : false); ?> 
                                            class="preview-trigger" />
                                        Enable RSS feed modifications
                                    </label>
                                    <br><br>
                                    <div class="rss-options" style="margin-left: 25px;">
                                        <label>
                                            <input type="checkbox" id="rss_show_symbol" name="link_blog_options[rss_show_symbol]" 
                                                <?php checked(isset($this->options['rss_show_symbol']) ? $this->options['rss_show_symbol'] : true); ?> 
                                                class="preview-trigger" />
                                            Show permalink symbol in RSS titles
                                        </label>
                                        <br><br>
                                        <select name="link_blog_options[rss_symbol_position]" id="rss_symbol_position" class="preview-trigger">
                                            <option value="before" <?php selected(isset($this->options['rss_symbol_position']) ? $this->options['rss_symbol_position'] : 'before', 'before'); ?>>Before title</option>
                                            <option value="after" <?php selected(isset($this->options['rss_symbol_position']) ? $this->options['rss_symbol_position'] : 'before', 'after'); ?>>After title</option>
                                        </select>
                                        <label for="rss_symbol_position">Symbol position in RSS</label>
                                        <br><br>
                                        <label>
                                            <input type="checkbox" id="rss_show_source" name="link_blog_options[rss_show_source]" 
                                                <?php checked(isset($this->options['rss_show_source']) ? $this->options['rss_show_source'] : true); ?> 
                                                class="preview-trigger" />
                                            Show source link in RSS description
                                        </label>
                                    </div>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>

            <div class="card preview-card">
                <h2>Live Preview</h2>
                <div class="preview-container">
                    <div class="preview-box">
                        <h4>Post Preview</h4>
                        <div class="preview-content" id="post-preview">
                            <h2 id="preview-title">Amazing New Technology Revealed</h2>
                            <p>This is fascinating! Company X has developed something incredible. Read more at <a href="https://example.com/tech-news">https://example.com/tech-news</a></p>
                            <p class="source-link">Source: <a href="https://example.com/tech-news">example.com</a></p>
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
        
        if(isset($input['show_permalink']))
            $new_input['show_permalink'] = (bool)$input['show_permalink'];
            
        if(isset($input['permalink_position']))
            $new_input['permalink_position'] = sanitize_text_field($input['permalink_position']);
        
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
            preg_match('/(https?:\/\/[^\s<>"]+)/i', $content, $matches);
            if (!empty($matches)) {
                $url = $matches[1];
                $domain = parse_url($url, PHP_URL_HOST);
                
                // Add source attribution
                $content .= sprintf(
                    '<p class="source-link">Source: <a href="%s">%s</a></p>',
                    esc_url($url),
                    esc_html($domain)
                );
                
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
            preg_match('/(https?:\/\/[^\s<>"]+)/i', $post->post_content, $match);
            $source_url = $match[1] ?? '';
            
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
}

// Initialize the plugin
if (is_admin()) {
    $link_blog_setup = new LinkBlogSetup();
}

// Add activation hook
function activate_link_blog_plugin() {
    $link_blog_setup = new LinkBlogSetup();
    $link_blog_setup->create_link_category();
}
register_activation_hook(__FILE__, 'activate_link_blog_plugin');