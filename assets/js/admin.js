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
    const $checkUpdatesBtn = $('#check-updates-btn');
    const $forceUpdateBtn = $('#force-update-btn');
    const $updateProgress = $('#update-progress');
    const $updateProgressText = $('#update-progress-text');
    const $updateInfo = $('#update-info');

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
            url: linkBlogAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'link_blog_create_category',
                nonce: linkBlogAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(linkBlogAdmin.strings.error_creating_category + ': ' + response.data.message);
                }
            },
            error: function() {
                alert(linkBlogAdmin.strings.error_creating_category + '. ' + linkBlogAdmin.strings.error_network);
            }
        });
    });

    // Handle update checks
    $checkUpdatesBtn.on('click', function(e) {
        e.preventDefault();
        checkForUpdates();
    });
    
    // Handle force update
    $forceUpdateBtn.on('click', function(e) {
        e.preventDefault();
        forceUpdate();
    });
    
    function checkForUpdates() {
        $checkUpdatesBtn.prop('disabled', true);
        $updateProgress.show();
        $updateProgressText.text(linkBlogAdmin.strings.checking_updates);
        
        $.ajax({
            url: linkBlogAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'link_blog_check_updates',
                nonce: linkBlogAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.has_update) {
                        displayUpdateAvailable(response.data);
                    } else {
                        displayUpToDate(response.data);
                    }
                } else {
                    displayUpdateError(response.data.message);
                }
            },
            error: function() {
                displayUpdateError(linkBlogAdmin.strings.error_network);
            },
            complete: function() {
                $checkUpdatesBtn.prop('disabled', false);
                $updateProgress.hide();
            }
        });
    }
    
    function forceUpdate() {
        $forceUpdateBtn.prop('disabled', true);
        $updateProgress.show();
        $updateProgressText.text(linkBlogAdmin.strings.preparing_update);
        
        $.ajax({
            url: linkBlogAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'link_blog_force_update',
                nonce: linkBlogAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.redirect_url) {
                    $updateProgressText.text(linkBlogAdmin.strings.redirecting);
                    window.location.href = response.data.redirect_url;
                } else {
                    displayUpdateError(response.data.message || 'Update failed');
                    $forceUpdateBtn.prop('disabled', false);
                    $updateProgress.hide();
                }
            },
            error: function() {
                displayUpdateError(linkBlogAdmin.strings.error_network);
                $forceUpdateBtn.prop('disabled', false);
                $updateProgress.hide();
            }
        });
    }
    
    function displayUpdateAvailable(data) {
        const updateHtml = `
            <div class="notice notice-warning inline">
                <p><strong>Update Available!</strong></p>
                <p>
                    <strong>Latest Version:</strong> v${data.latestVersion} 
                    <span style="color: #666;">(Released: ${data.releaseDate})</span>
                </p>
                <p><strong>Release Notes:</strong> ${data.releaseNotes}</p>
                <p>
                    <a href="${data.releaseUrl}" target="_blank" class="button button-secondary">
                        View Full Release Notes
                    </a>
                </p>
            </div>
        `;
        $updateInfo.html(updateHtml);
        $forceUpdateBtn.show();
    }
    
    function displayUpToDate(data) {
        const upToDateHtml = `
            <div class="notice notice-success inline">
                <p><strong>✅ ${data.message}</strong></p>
            </div>
        `;
        $updateInfo.html(upToDateHtml);
        $forceUpdateBtn.hide();
    }
    
    function displayUpdateError(message) {
        const errorHtml = `
            <div class="notice notice-error inline">
                <p><strong>Error:</strong> ${message}</p>
            </div>
        `;
        $updateInfo.html(errorHtml);
        $forceUpdateBtn.hide();
    }

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