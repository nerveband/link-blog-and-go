/**
 * Link Blog and Go - Admin JavaScript
 * Version: 1.1.0
 */

jQuery(document).ready(function($) {
    // Cache DOM elements
    const $showPermalink = $('#show_permalink');
    const $permalinkSymbol = $('#permalink_symbol');
    const $permalinkPosition = $('#permalink_position');
    const $modifyRss = $('#modify_rss');
    const $rssShowSymbol = $('#rss_show_symbol');
    const $rssSymbolPosition = $('#rss_symbol_position');
    const $rssShowSource = $('#rss_show_source');
    const $previewTitle = $('#preview-title');
    const $rssPreview = $('#rss-preview');
    const $rssPreviewContainer = $('#rss-preview-container');
    const $rssOptions = $('.rss-options');
    const $createLinksCategory = $('#create-links-category');

    // Sample data for preview
    const sampleTitle = 'Amazing New Technology Revealed';
    const sampleUrl = 'https://example.com/tech-news';
    const sampleDomain = 'example.com';
    const sampleExcerpt = 'This is fascinating! Company X has developed something incredible.';

    // Update previews when settings change
    $('.preview-trigger').on('change keyup', updatePreviews);
    
    // Toggle RSS options visibility
    $modifyRss.on('change', function() {
        $rssOptions.toggle($(this).prop('checked'));
        updatePreviews();
    });
    
    // Initial setup
    $rssOptions.toggle($modifyRss.prop('checked'));
    updatePreviews();

    // Handle category creation
    $createLinksCategory.on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'create_links_category',
                nonce: linkBlogSettings.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error creating Links category: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error creating Links category. Please try again.');
            }
        });
    });

    function updatePreviews() {
        updatePostPreview();
        updateRssPreview();
    }

    function updatePostPreview() {
        const showPermalink = $showPermalink.prop('checked');
        const symbol = $permalinkSymbol.val() || '★';
        const position = $permalinkPosition.val();
        
        // Remove any existing permalink symbols
        $previewTitle.find('.permalink-symbol').remove();
        
        if (showPermalink) {
            const symbolHtml = `<span class="permalink-symbol ${position}">${symbol}</span>`;
            if (position === 'before') {
                $previewTitle.prepend(symbolHtml);
            } else {
                $previewTitle.append(symbolHtml);
            }
        }
    }

    function updateRssPreview() {
        const modifyRss = $modifyRss.prop('checked');
        
        if (!modifyRss) {
            $rssPreviewContainer.hide();
            return;
        }

        $rssPreviewContainer.show();
        
        // Get RSS settings
        const showSymbol = $rssShowSymbol.prop('checked');
        const symbolPosition = $rssSymbolPosition.val();
        const showSource = $rssShowSource.prop('checked');
        const symbol = $permalinkSymbol.val() || '★';
        
        // Build title with symbol if enabled
        let title = sampleTitle;
        if (showSymbol) {
            title = symbolPosition === 'after' ? 
                `${sampleTitle} ${symbol}` : 
                `${symbol} ${sampleTitle}`;
        }
        
        // Build description with optional source
        let description = sampleExcerpt;
        if (showSource) {
            description += `\n        Source: <a href="${sampleUrl}">${sampleDomain}</a>`;
        }
        
        const rssHtml = 
`<item>
    <title>${title}</title>
    <link>${sampleUrl}</link>
    <description>
        ${description}
    </description>
</item>`;
        
        $rssPreview.text(rssHtml);
    }
}); 