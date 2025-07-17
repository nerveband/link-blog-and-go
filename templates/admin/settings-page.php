<?php
/**
 * Admin Settings Page Template
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from admin class
// $options - array of plugin options
// $version - plugin version
?>

<div class="wrap link-blog-admin">
    <div class="admin-header">
        <h1>
            🔗 <?php _e('Link Blog and Go', 'link-blog-and-go'); ?>
            <span class="version">v<?php echo esc_html($version); ?></span>
        </h1>
        <p class="subtitle">
            <?php _e('Transform your WordPress blog into a link blog with automatic domain extraction and beautiful formatting.', 'link-blog-and-go'); ?>
        </p>
    </div>
    
    <div class="admin-grid">
        <!-- Settings Column -->
        <div class="admin-main">
            <?php
            // Check if Links category exists
            $category_name = $options['category_name'];
            $category = get_category_by_slug(sanitize_title($category_name));
            if (!$category) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <strong><?php _e('Setup Required:', 'link-blog-and-go'); ?></strong>
                        <?php 
                        echo sprintf(
                            __('The "%s" category doesn\'t exist yet.', 'link-blog-and-go'),
                            esc_html($category_name)
                        );
                        ?>
                        <button type="button" class="button button-secondary" id="create-links-category">
                            <?php _e('Create Links Category', 'link-blog-and-go'); ?>
                        </button>
                    </p>
                </div>
                <?php
            }
            ?>
            
            <div class="settings-section">
                <div class="section-header">
                    <h2>⚙️ <?php _e('Configuration', 'link-blog-and-go'); ?></h2>
                    <p><?php _e('Configure how your link blog works and looks', 'link-blog-and-go'); ?></p>
                </div>
                
                <form method="post" action="options.php" class="link-blog-form">
                    <?php 
                    settings_fields('link_blog_options_group');
                    do_settings_sections('link_blog_options_group');
                    ?>
                    
                    <!-- Basic Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>📁 <?php _e('Basic Settings', 'link-blog-and-go'); ?></h3>
                            <p><?php _e('Essential configuration for your link blog', 'link-blog-and-go'); ?></p>
                        </div>
                        <div class="card-content">
                            <div class="setting-group">
                                <label class="setting-label" for="category_name">
                                    <strong><?php _e('Link Category Name', 'link-blog-and-go'); ?></strong>
                                    <span class="help-text"><?php _e('Posts in this category will be formatted as link posts', 'link-blog-and-go'); ?></span>
                                </label>
                                <input type="text" 
                                       id="category_name" 
                                       name="link_blog_options[category_name]" 
                                       value="<?php echo esc_attr($options['category_name']); ?>" 
                                       class="regular-text preview-trigger" 
                                       placeholder="<?php _e('Links', 'link-blog-and-go'); ?>" />
                            </div>
                            
                            <div class="setting-group">
                                <label class="setting-label">
                                    <strong><?php _e('Auto-Link Behavior', 'link-blog-and-go'); ?></strong>
                                    <span class="help-text"><?php _e('Control how domain links are added to your posts', 'link-blog-and-go'); ?></span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           id="auto_append_links" 
                                           name="link_blog_options[auto_append_links]" 
                                           <?php checked($options['auto_append_links']); ?> 
                                           class="preview-trigger" />
                                    <span class="checkmark"></span>
                                    <?php _e('Automatically append domain links to posts', 'link-blog-and-go'); ?>
                                </label>
                                <div class="help-details">
                                    <p><strong>✅ <?php _e('Enabled:', 'link-blog-and-go'); ?></strong> <?php _e('Domain links appear automatically at the end of posts', 'link-blog-and-go'); ?></p>
                                    <p><strong>❌ <?php _e('Disabled:', 'link-blog-and-go'); ?></strong> <?php _e('You must manually place shortcodes or variables', 'link-blog-and-go'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permalink Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>🔗 <?php _e('Permalink Settings', 'link-blog-and-go'); ?></h3>
                            <p><?php _e('Customize the permalink symbol display', 'link-blog-and-go'); ?></p>
                        </div>
                        <div class="card-content">
                            <div class="setting-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           id="show_permalink" 
                                           name="link_blog_options[show_permalink]" 
                                           <?php checked($options['show_permalink']); ?> 
                                           class="preview-trigger" />
                                    <span class="checkmark"></span>
                                    <?php _e('Show permalink symbol', 'link-blog-and-go'); ?>
                                </label>
                            </div>
                            
                            <div class="setting-row">
                                <div class="setting-group half">
                                    <label class="setting-label" for="permalink_symbol">
                                        <strong><?php _e('Symbol', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <input type="text" 
                                           id="permalink_symbol" 
                                           name="link_blog_options[permalink_symbol]" 
                                           value="<?php echo esc_attr($options['permalink_symbol']); ?>" 
                                           class="small-text preview-trigger" 
                                           placeholder="★" />
                                </div>
                                
                                <div class="setting-group half">
                                    <label class="setting-label" for="permalink_position">
                                        <strong><?php _e('Position', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <select name="link_blog_options[permalink_position]" 
                                            id="permalink_position" 
                                            class="preview-trigger">
                                        <option value="before" <?php selected($options['permalink_position'], 'before'); ?>>
                                            <?php _e('Before title', 'link-blog-and-go'); ?>
                                        </option>
                                        <option value="after" <?php selected($options['permalink_position'], 'after'); ?>>
                                            <?php _e('After title', 'link-blog-and-go'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Domain Customization -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>🎨 <?php _e('Domain Text Customization', 'link-blog-and-go'); ?></h3>
                            <p><?php _e('Customize how domain names appear in your posts', 'link-blog-and-go'); ?></p>
                        </div>
                        <div class="card-content">
                            <div class="setting-row">
                                <div class="setting-group half">
                                    <label class="setting-label" for="domain_before_text">
                                        <strong><?php _e('Main Domain Before Text', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <input type="text" 
                                           id="domain_before_text" 
                                           name="link_blog_options[domain_before_text]" 
                                           value="<?php echo esc_attr($options['domain_before_text']); ?>" 
                                           class="regular-text preview-trigger" 
                                           placeholder="→ " />
                                </div>
                                
                                <div class="setting-group half">
                                    <label class="setting-label" for="domain_after_text">
                                        <strong><?php _e('Main Domain After Text', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <input type="text" 
                                           id="domain_after_text" 
                                           name="link_blog_options[domain_after_text]" 
                                           value="<?php echo esc_attr($options['domain_after_text']); ?>" 
                                           class="regular-text preview-trigger" 
                                           placeholder="<?php _e('(optional)', 'link-blog-and-go'); ?>" />
                                </div>
                            </div>
                            
                            <div class="setting-row">
                                <div class="setting-group half">
                                    <label class="setting-label" for="via_domain_before_text">
                                        <strong><?php _e('Via Domain Before Text', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <input type="text" 
                                           id="via_domain_before_text" 
                                           name="link_blog_options[via_domain_before_text]" 
                                           value="<?php echo esc_attr($options['via_domain_before_text']); ?>" 
                                           class="regular-text preview-trigger" 
                                           placeholder="via " />
                                </div>
                                
                                <div class="setting-group half">
                                    <label class="setting-label" for="via_domain_after_text">
                                        <strong><?php _e('Via Domain After Text', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <input type="text" 
                                           id="via_domain_after_text" 
                                           name="link_blog_options[via_domain_after_text]" 
                                           value="<?php echo esc_attr($options['via_domain_after_text']); ?>" 
                                           class="regular-text preview-trigger" 
                                           placeholder="<?php _e('(optional)', 'link-blog-and-go'); ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- RSS Feed Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>📡 <?php _e('RSS Feed Settings', 'link-blog-and-go'); ?></h3>
                            <p><?php _e('Customize how your link posts appear in RSS feeds', 'link-blog-and-go'); ?></p>
                        </div>
                        <div class="card-content">
                            <div class="setting-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           id="modify_rss" 
                                           name="link_blog_options[modify_rss]" 
                                           <?php checked($options['modify_rss']); ?> 
                                           class="preview-trigger" />
                                    <span class="checkmark"></span>
                                    <?php _e('Modify RSS feed', 'link-blog-and-go'); ?>
                                </label>
                            </div>
                            
                            <div class="rss-options" style="margin-left: 30px; margin-top: 15px;">
                                <div class="setting-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" 
                                               id="rss_show_symbol" 
                                               name="link_blog_options[rss_show_symbol]" 
                                               <?php checked($options['rss_show_symbol']); ?> 
                                               class="preview-trigger" />
                                        <span class="checkmark"></span>
                                        <?php _e('Show symbol in RSS titles', 'link-blog-and-go'); ?>
                                    </label>
                                </div>
                                
                                <div class="setting-group">
                                    <label class="setting-label" for="rss_symbol_position">
                                        <strong><?php _e('Symbol Position', 'link-blog-and-go'); ?></strong>
                                    </label>
                                    <select name="link_blog_options[rss_symbol_position]" 
                                            id="rss_symbol_position" 
                                            class="preview-trigger">
                                        <option value="before" <?php selected($options['rss_symbol_position'], 'before'); ?>>
                                            <?php _e('Before title', 'link-blog-and-go'); ?>
                                        </option>
                                        <option value="after" <?php selected($options['rss_symbol_position'], 'after'); ?>>
                                            <?php _e('After title', 'link-blog-and-go'); ?>
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="setting-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" 
                                               id="rss_show_source" 
                                               name="link_blog_options[rss_show_source]" 
                                               <?php checked($options['rss_show_source']); ?> 
                                               class="preview-trigger" />
                                        <span class="checkmark"></span>
                                        <?php _e('Show source link in RSS description', 'link-blog-and-go'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advanced Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>🛠️ <?php _e('Advanced Settings', 'link-blog-and-go'); ?></h3>
                            <p><?php _e('Custom fields and advanced features', 'link-blog-and-go'); ?></p>
                        </div>
                        <div class="card-content">
                            <div class="setting-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           id="enable_custom_fields" 
                                           name="link_blog_options[enable_custom_fields]" 
                                           <?php checked($options['enable_custom_fields']); ?> 
                                           class="preview-trigger" />
                                    <span class="checkmark"></span>
                                    <?php _e('Enable custom link fields', 'link-blog-and-go'); ?>
                                </label>
                                <div class="help-details">
                                    <p><?php _e('Adds meta boxes to post editor for custom link and via URLs', 'link-blog-and-go'); ?></p>
                                </div>
                            </div>
                            
                            <div class="shortcodes-info">
                                <h4><?php _e('Available Shortcodes & Variables:', 'link-blog-and-go'); ?></h4>
                                <div class="shortcode-grid">
                                    <div class="shortcode-item">
                                        <strong><?php _e('Shortcodes:', 'link-blog-and-go'); ?></strong>
                                        <code>[link_blog_link]</code>
                                        <code>[via_link]</code>
                                        <code>[link_blog_domain]</code>
                                        <code>[via_domain]</code>
                                    </div>
                                    <div class="shortcode-item">
                                        <strong><?php _e('Variables:', 'link-blog-and-go'); ?></strong>
                                        <code>{link_blog_link}</code>
                                        <code>{via_link}</code>
                                        <code>{link_blog_domain}</code>
                                        <code>{via_domain}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="submit-section">
                        <?php submit_button(__('Save Settings', 'link-blog-and-go'), 'primary', 'submit', false, array('class' => 'save-button')); ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <!-- Quick Guide -->
            <div class="sidebar-card">
                <h3>📝 <?php _e('Quick Guide', 'link-blog-and-go'); ?></h3>
                <div class="guide-steps">
                    <div class="guide-step">
                        <span class="step-number">1</span>
                        <div>
                            <strong><?php _e('Create Post', 'link-blog-and-go'); ?></strong>
                            <p><?php _e('Add a new post and assign it to your Links category', 'link-blog-and-go'); ?></p>
                        </div>
                    </div>
                    <div class="guide-step">
                        <span class="step-number">2</span>
                        <div>
                            <strong><?php _e('Add URL', 'link-blog-and-go'); ?></strong>
                            <p><?php _e('Include the URL you want to link to in your post content', 'link-blog-and-go'); ?></p>
                        </div>
                    </div>
                    <div class="guide-step">
                        <span class="step-number">3</span>
                        <div>
                            <strong><?php _e('Publish', 'link-blog-and-go'); ?></strong>
                            <p><?php _e('The plugin automatically formats your post with domain links', 'link-blog-and-go'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Plugin Updates -->
            <div class="sidebar-card">
                <h3>🔄 <?php _e('Plugin Updates', 'link-blog-and-go'); ?></h3>
                <div id="update-status" class="update-status">
                    <p><strong><?php _e('Current Version:', 'link-blog-and-go'); ?></strong> v<?php echo esc_html($version); ?></p>
                    <div id="update-info"></div>
                </div>
                <p>
                    <button type="button" id="check-updates-btn" class="button button-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Check for Updates', 'link-blog-and-go'); ?>
                    </button>
                    <button type="button" id="force-update-btn" class="button button-primary" style="display: none; margin-top: 10px; width: 100%;">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Update Now', 'link-blog-and-go'); ?>
                    </button>
                </p>
                <div id="update-progress" style="display: none;">
                    <p><span class="spinner is-active"></span> <span id="update-progress-text"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>