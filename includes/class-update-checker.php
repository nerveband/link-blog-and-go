<?php
/**
 * GitHub Update Checker for Link Blog and Go
 * 
 * Enables automatic updates from GitHub releases without using WordPress.org
 */

if (!defined('ABSPATH')) {
    exit;
}

class LinkBlogUpdateChecker {
    private $plugin_slug;
    private $plugin_file;
    private $github_username;
    private $github_repo;
    private $github_api_url;
    private $plugin_data;
    private $github_data;

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        
        // Configure your GitHub repository here
        $this->github_username = 'your-github-username'; // Update this
        $this->github_repo = 'link-blog-and-go';
        $this->github_api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'fix_plugin_folder'), 10, 3);
        
        // Add custom update row
        add_action("after_plugin_row_{$this->plugin_slug}", array($this, 'show_update_notification'), 10, 2);
    }

    /**
     * Get plugin data from the main file
     */
    private function get_plugin_data() {
        if (is_null($this->plugin_data)) {
            $this->plugin_data = get_plugin_data($this->plugin_file);
        }
        return $this->plugin_data;
    }

    /**
     * Get latest release data from GitHub
     */
    private function get_github_data() {
        if (!empty($this->github_data)) {
            return $this->github_data;
        }

        // Check cache first
        $cache_key = 'link_blog_github_update_' . md5($this->github_api_url);
        $github_data = get_transient($cache_key);

        if ($github_data === false) {
            $response = wp_remote_get(
                $this->github_api_url,
                array(
                    'timeout' => 10,
                    'headers' => array(
                        'Accept' => 'application/vnd.github.v3+json',
                        'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
                    )
                )
            );

            if (is_wp_error($response)) {
                return false;
            }

            $github_data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (empty($github_data['tag_name'])) {
                return false;
            }

            // Cache for 6 hours
            set_transient($cache_key, $github_data, 6 * HOUR_IN_SECONDS);
        }

        $this->github_data = $github_data;
        return $github_data;
    }

    /**
     * Check if update is available
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_data = $this->get_plugin_data();
        $github_data = $this->get_github_data();

        if (!$github_data) {
            return $transient;
        }

        // Get version from tag (remove 'v' prefix if present)
        $latest_version = ltrim($github_data['tag_name'], 'v');
        $current_version = $plugin_data['Version'];

        // Compare versions
        if (version_compare($current_version, $latest_version, '<')) {
            $download_url = '';
            
            // Find the zip file in assets
            if (!empty($github_data['assets'])) {
                foreach ($github_data['assets'] as $asset) {
                    if ($asset['name'] === 'link-blog-and-go.zip') {
                        $download_url = $asset['browser_download_url'];
                        break;
                    }
                }
            }

            // If no asset found, use zipball URL
            if (empty($download_url)) {
                $download_url = $github_data['zipball_url'];
            }

            $plugin_update = array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $latest_version,
                'url' => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'package' => $download_url,
                'icons' => array(
                    '2x' => plugin_dir_url($this->plugin_file) . 'assets/icon-256x256.png',
                    '1x' => plugin_dir_url($this->plugin_file) . 'assets/icon-128x128.png',
                ),
                'banners' => array(
                    'low' => plugin_dir_url($this->plugin_file) . 'assets/banner-772x250.png',
                    'high' => plugin_dir_url($this->plugin_file) . 'assets/banner-1544x500.png',
                ),
                'tested' => '6.4', // Update this with your tested WP version
                'requires_php' => '7.2',
                'compatibility' => new stdClass(),
            );

            $transient->response[$this->plugin_slug] = (object) $plugin_update;
        }

        return $transient;
    }

    /**
     * Show plugin information in WordPress admin
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }

        $github_data = $this->get_github_data();
        if (!$github_data) {
            return $result;
        }

        $plugin_data = $this->get_plugin_data();

        $plugin_info = array(
            'name' => $plugin_data['Name'],
            'slug' => dirname($this->plugin_slug),
            'version' => ltrim($github_data['tag_name'], 'v'),
            'author' => $plugin_data['Author'],
            'author_profile' => $plugin_data['AuthorURI'],
            'last_updated' => $github_data['published_at'],
            'homepage' => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections' => array(
                'description' => $plugin_data['Description'],
                'changelog' => $this->parse_changelog($github_data['body']),
            ),
            'download_link' => $github_data['zipball_url'],
        );

        return (object) $plugin_info;
    }

    /**
     * Parse changelog from GitHub release body
     */
    private function parse_changelog($body) {
        // Convert markdown to HTML
        $changelog = nl2br($body);
        
        // Basic markdown parsing
        $changelog = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $changelog);
        $changelog = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $changelog);
        $changelog = preg_replace('/^- (.+?)$/m', '<li>$1</li>', $changelog);
        $changelog = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $changelog);
        
        return $changelog;
    }

    /**
     * Fix the plugin folder name after update
     */
    public function fix_plugin_folder($source, $remote_source, $upgrader) {
        global $wp_filesystem;

        if (strpos($source, $this->github_repo) === false) {
            return $source;
        }

        $corrected_source = trailingslashit($remote_source) . 'link-blog-and-go/';

        if ($wp_filesystem->move($source, $corrected_source)) {
            return $corrected_source;
        }

        return $source;
    }

    /**
     * Show update notification row
     */
    public function show_update_notification($file, $plugin) {
        if (!current_user_can('update_plugins')) {
            return;
        }

        $github_data = $this->get_github_data();
        if (!$github_data) {
            return;
        }

        $latest_version = ltrim($github_data['tag_name'], 'v');
        $current_version = $this->get_plugin_data()['Version'];

        if (version_compare($current_version, $latest_version, '>=')) {
            return;
        }

        echo '<tr class="plugin-update-tr active"><td colspan="4" class="plugin-update">';
        echo '<div class="update-message notice inline notice-warning notice-alt">';
        echo '<p>There is a new version of ' . esc_html($plugin['Name']) . ' available. ';
        echo '<a href="' . esc_url($github_data['html_url']) . '" target="_blank">View version ' . esc_html($latest_version) . ' details</a> or ';
        echo '<a href="' . wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $file), 'upgrade-plugin_' . $file) . '">update now</a>.';
        echo '</p></div></td></tr>';
    }
}