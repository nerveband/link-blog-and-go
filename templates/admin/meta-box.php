<?php
/**
 * Meta Box Template
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from admin class
// $post - WP_Post object
// $custom_link - custom link URL
// $custom_via - custom via URL
?>

<div class="link-blog-meta-box">
    <p class="description">
        <?php _e('Override the automatically detected URLs with custom values. Leave empty to use auto-detection.', 'link-blog-and-go'); ?>
    </p>
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="link_blog_custom_link">
                    <?php _e('Custom Link URL', 'link-blog-and-go'); ?>
                </label>
            </th>
            <td>
                <input type="url" 
                       id="link_blog_custom_link" 
                       name="link_blog_custom_link" 
                       value="<?php echo esc_attr($custom_link); ?>" 
                       class="regular-text"
                       placeholder="<?php _e('Enter URL or leave empty to auto-detect', 'link-blog-and-go'); ?>" />
                <p class="description">
                    <?php _e('The main URL will be auto-detected from the first URL in your post content if left empty.', 'link-blog-and-go'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row">
                <label for="link_blog_custom_via">
                    <?php _e('Custom Via URL', 'link-blog-and-go'); ?>
                </label>
            </th>
            <td>
                <input type="url" 
                       id="link_blog_custom_via" 
                       name="link_blog_custom_via" 
                       value="<?php echo esc_attr($custom_via); ?>" 
                       class="regular-text"
                       placeholder="<?php _e('Enter URL or leave empty to auto-detect', 'link-blog-and-go'); ?>" />
                <p class="description">
                    <?php _e('The via URL will be auto-detected from content after "via" if left empty.', 'link-blog-and-go'); ?>
                </p>
            </td>
        </tr>
    </table>
    
    <div class="link-blog-meta-box-actions">
        <button type="button" class="button" id="link-blog-detect-urls">
            <span class="dashicons dashicons-search"></span>
            <?php _e('Re-detect URLs from Content', 'link-blog-and-go'); ?>
        </button>
        <button type="button" class="button" id="link-blog-clear-urls">
            <span class="dashicons dashicons-trash"></span>
            <?php _e('Clear Custom URLs', 'link-blog-and-go'); ?>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    /**
     * Detect URLs from post content
     */
    function detectUrls() {
        var content = '';
        
        // Try to get content from different editors
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            // Gutenberg editor
            content = wp.data.select('core/editor').getEditedPostContent();
        } else if (typeof wp !== 'undefined' && wp.editor && wp.editor.getContent) {
            // Classic editor with wp.editor API
            content = wp.editor.getContent('content');
        } else if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
            // TinyMCE editor
            content = tinyMCE.get('content').getContent();
        } else if ($('#content').length) {
            // Fallback to textarea
            content = $('#content').val();
        }
        
        if (!content) {
            return;
        }
        
        // Remove HTML tags for URL detection
        content = $('<div>').html(content).text();
        
        // Detect main URL (first URL in content)
        var urlMatch = content.match(/(https?:\/\/[^\s<>"]+)/i);
        if (urlMatch && urlMatch[1]) {
            $('#link_blog_custom_link').val(urlMatch[1]);
        }
        
        // Detect via URL (URL after "via")
        var viaMatch = content.match(/via\s+(https?:\/\/[^\s<>"]+)/i);
        if (viaMatch && viaMatch[1]) {
            $('#link_blog_custom_via').val(viaMatch[1]);
        }
    }
    
    /**
     * Clear custom URLs
     */
    function clearUrls() {
        $('#link_blog_custom_link, #link_blog_custom_via').val('');
    }
    
    // Event handlers
    $('#link-blog-detect-urls').on('click', function(e) {
        e.preventDefault();
        detectUrls();
    });
    
    $('#link-blog-clear-urls').on('click', function(e) {
        e.preventDefault();
        clearUrls();
    });
    
    // Auto-detect URLs when the meta box loads if fields are empty
    if (!$('#link_blog_custom_link').val() && !$('#link_blog_custom_via').val()) {
        detectUrls();
    }
    
    // Listen for content changes and update URLs if fields are empty
    var contentUpdateTimer;
    
    function scheduleUrlDetection() {
        clearTimeout(contentUpdateTimer);
        contentUpdateTimer = setTimeout(function() {
            if (!$('#link_blog_custom_link').val() && !$('#link_blog_custom_via').val()) {
                detectUrls();
            }
        }, 1000);
    }
    
    // Classic editor
    $(document).on('input change', '#content', scheduleUrlDetection);
    
    // TinyMCE editor
    if (typeof tinyMCE !== 'undefined') {
        $(document).on('tinymce-editor-init', function(event, editor) {
            editor.on('NodeChange KeyUp', scheduleUrlDetection);
        });
    }
    
    // Gutenberg editor
    if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
        var previousContent = '';
        wp.data.subscribe(function() {
            if (wp.data.select('core/editor')) {
                var currentContent = wp.data.select('core/editor').getEditedPostContent();
                if (currentContent !== previousContent) {
                    previousContent = currentContent;
                    scheduleUrlDetection();
                }
            }
        });
    }
});
</script>

<style>
.link-blog-meta-box {
    padding: 12px;
}

.link-blog-meta-box .form-table th {
    width: 150px;
    vertical-align: top;
    padding-top: 8px;
}

.link-blog-meta-box .form-table td {
    padding-top: 8px;
}

.link-blog-meta-box-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.link-blog-meta-box-actions .button {
    margin-right: 10px;
}

.link-blog-meta-box-actions .dashicons {
    margin-right: 5px;
}

.link-blog-meta-box .description {
    margin-top: 5px;
    font-style: italic;
    color: #666;
}
</style>