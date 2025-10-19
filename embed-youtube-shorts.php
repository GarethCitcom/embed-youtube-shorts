<?php

/**
 * Plugin Name: Embed YouTube Shorts
 * Plugin URI: https://plugins.citcom.support/eyss
 * Description: Display YouTube Shorts from a channel playlist in various layouts using the YouTube API.
 * Version: 2.3.1
 * Author: Gareth Hale, CitCom.
 * Author URI: https://citcom.co.uk
 * License: GPL v2 or later
 * Text Domain: embed-youtube-shorts
 * Domain Path: /languages
 * Requires at least: 6.2
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check WordPress version compatibility
if (version_compare(get_bloginfo('version'), '6.2', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        printf(
            /* translators: %1$s is the current WordPress version, %2$s is the required version */
            esc_html__('Embed YouTube Shorts requires WordPress %2$s or higher. You are running version %1$s. Please update WordPress.', 'embed-youtube-shorts'),
            esc_html(get_bloginfo('version')),
            '6.2'
        );
        echo '</p></div>';
    });
    return;
}

// Define plugin constants
define('EYSS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EYSS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('EYSS_PLUGIN_VERSION', '2.3.1');

/**
 * Main plugin class
 */
class EmbedYouTubeShorts
{

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Add cron hooks
        add_action('eyss_bg_import', array($this, 'handle_background_import'), 10, 2);
        add_action('eyss_sync_videos_cron', array($this, 'handle_scheduled_sync'));
        add_action('eyss_daily_import', array($this, 'handle_daily_import'));

        register_uninstall_hook(__FILE__, array('EmbedYouTubeShorts', 'uninstall'));
    }

    /**
     * Initialize the plugin
     */
    public function init()
    {
        // Include required files
        $this->include_files();

        // Initialize components
        $this->init_components();
    }

    /**
     * Include required files
     */
    private function include_files()
    {
        require_once EYSS_PLUGIN_PATH . 'includes/class-youtube-api.php';
        require_once EYSS_PLUGIN_PATH . 'includes/class-post-type.php';
        require_once EYSS_PLUGIN_PATH . 'includes/class-video-importer.php';
        require_once EYSS_PLUGIN_PATH . 'includes/class-shortcode.php';
        require_once EYSS_PLUGIN_PATH . 'admin/class-admin-settings.php';
    }

    /**
     * Initialize plugin components
     */
    private function init_components()
    {
        // Initialize post type
        new EYSS_Post_Type();

        // Initialize video importer
        new EYSS_Video_Importer();

        // Initialize admin settings
        if (is_admin()) {
            new EYSS_Admin_Settings();
        }

        // Initialize shortcode
        new EYSS_Shortcode();

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets()
    {
        // Enqueue Splide.js CSS from local vendor files
        wp_enqueue_style(
            'splide-css',
            EYSS_PLUGIN_URL . 'assets/vendor/splide/splide.min.css',
            array(),
            '4.1.4'
        );

        wp_enqueue_style(
            'eyss-frontend-style',
            EYSS_PLUGIN_URL . 'assets/frontend.css',
            array('splide-css'),
            EYSS_PLUGIN_VERSION
        );

        // Enqueue Splide.js from local vendor files
        wp_enqueue_script(
            'splide-js',
            EYSS_PLUGIN_URL . 'assets/vendor/splide/splide.min.js',
            array(),
            '4.1.4',
            true
        );

        wp_enqueue_script(
            'eyss-frontend-script',
            EYSS_PLUGIN_URL . 'assets/frontend.js',
            array('jquery', 'splide-js'),
            EYSS_PLUGIN_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('eyss-frontend-script', 'eyss_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eyss_nonce')
        ));
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        // Only load on our admin page
        if ('settings_page_embed-youtube-shorts' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'eyss-admin-style',
            EYSS_PLUGIN_URL . 'assets/admin.css',
            array(),
            EYSS_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'eyss-admin-script',
            EYSS_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            EYSS_PLUGIN_VERSION,
            true
        );

        // Localize script for admin AJAX
        wp_localize_script('eyss-admin-script', 'eyss_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eyss_nonce')
        ));
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Set default options
        $default_options = array(
            'api_key' => '',
            'channel_id' => '',
            'default_layout' => 'grid',
            'videos_per_page' => 12,
            'max_short_duration' => 180, // 3 minutes
            'cache_duration' => 3600, // 1 hour
        );

        add_option('eyss_settings', $default_options);

        // Create database table if needed (for caching)
        $this->create_cache_table();

        // Schedule automatic sync (daily)
        if (!wp_next_scheduled('eyss_sync_videos_cron')) {
            wp_schedule_event(time(), 'daily', 'eyss_sync_videos_cron');
        }

        // Register post type and flush rewrite rules
        $this->register_post_types_on_activation();
        flush_rewrite_rules();
    }

    /**
     * Register post types during activation
     */
    private function register_post_types_on_activation()
    {
        // Direct registration without class dependencies
        $this->register_youtube_short_post_type();
    }

    /**
     * Direct post type registration function
     */
    private function register_youtube_short_post_type()
    {
        if (post_type_exists('youtube_short')) {
            return;
        }

        $labels = array(
            'name'                  => 'YouTube Shorts',
            'singular_name'         => 'YouTube Short',
            'menu_name'             => 'YouTube Shorts',
            'add_new_item'          => 'Add New YouTube Short',
            'edit_item'             => 'Edit YouTube Short',
            'view_item'             => 'View YouTube Short',
            'all_items'             => 'All YouTube Shorts',
            'search_items'          => 'Search YouTube Shorts',
            'not_found'             => 'No YouTube Shorts found.',
            'not_found_in_trash'    => 'No YouTube Shorts found in Trash.',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'query_var'          => 'youtube_short',
            'rewrite'            => array('slug' => 'youtube-shorts'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-video-alt3',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );

        register_post_type('youtube_short', $args);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Clear scheduled events
        wp_clear_scheduled_hook('eyss_clear_cache');
        wp_clear_scheduled_hook('eyss_sync_videos_cron');
        wp_clear_scheduled_hook('eyss_bg_import');
        wp_clear_scheduled_hook('eyss_daily_import');

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall()
    {
        // Remove options
        delete_option('eyss_settings');

        // Drop cache table
        global $wpdb;
        $table_name = $wpdb->prefix . 'eyss_cache';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Direct query needed for plugin uninstall cleanup, no caching required for uninstall operations
        $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $table_name));

        // Clear scheduled events
        wp_clear_scheduled_hook('eyss_clear_cache');
        wp_clear_scheduled_hook('eyss_daily_import');

        // Remove auto-import related options
        delete_option('eyss_last_full_auto_import');
    }

    /**
     * Handle background import
     */
    public function handle_background_import($channel_id, $force_refresh = false)
    {
        $importer = new EYSS_Video_Importer();
        $result = $importer->import_channel_videos($channel_id, $force_refresh);
    }

    /**
     * Handle scheduled sync
     */
    public function handle_scheduled_sync()
    {
        $settings = get_option('eyss_settings', array());
        $channel_id = isset($settings['channel_id']) ? $settings['channel_id'] : '';

        if (!empty($channel_id)) {
            // Light sync - only check for new videos from the last 7 days
            $this->sync_recent_videos($channel_id, 7);
        }
    }

    /**
     * Sync recent videos (lighter version for scheduled updates)
     */
    private function sync_recent_videos($channel_id, $days_back = 7)
    {
        $importer = new EYSS_Video_Importer();

        // Get videos published in the last N days
        $youtube_api = new EYSS_YouTube_API();
        $uploads_playlist = $youtube_api->get_uploads_playlist_id($channel_id);

        if (is_wp_error($uploads_playlist)) {
            return;
        }

        // Get recent videos and import only new ones
        $recent_videos = $youtube_api->get_playlist_videos($uploads_playlist, 100);

        if (is_wp_error($recent_videos) || empty($recent_videos)) {
            return;
        }

        $video_ids = array();
        foreach ($recent_videos as $video) {
            $video_ids[] = $video['contentDetails']['videoId'];

            // Only check the first 50 most recent
            if (count($video_ids) >= 50) {
                break;
            }
        }

        $video_details = $youtube_api->get_video_details($video_ids);

        if (is_wp_error($video_details)) {
            return;
        }

        $imported_count = 0;
        foreach ($video_details as $video_data) {
            // Check if video was published within the time range
            $published_date = strtotime($video_data['snippet']['publishedAt']);
            $cutoff_date = time() - ($days_back * 24 * 60 * 60);

            if ($published_date < $cutoff_date) {
                continue; // Skip older videos
            }

            // Check if video already exists
            $existing = get_posts(array(
                'post_type' => 'youtube_short',
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to check for existing videos by YouTube ID to prevent duplicates during import
                'meta_query' => array(
                    array(
                        'key' => '_eyss_video_id',
                        'value' => $video_data['id'],
                        'compare' => '='
                    )
                ),
                'posts_per_page' => 1
            ));

            if (empty($existing)) {
                // Import new video
                $result = $importer->import_single_video($video_data, $channel_id, false);
                if ($result === 'imported') {
                    $imported_count++;
                }
            }
        }
    }

    /**
     * Handle daily auto-import cron job
     */
    public function handle_daily_import()
    {
        // Check if auto-import is enabled
        $settings = get_option('eyss_settings', array());
        $auto_import_enabled = isset($settings['auto_import_enabled']) ? $settings['auto_import_enabled'] : false;

        if (!$auto_import_enabled) {
            return; // Auto-import is disabled, skip
        }

        $channel_id = isset($settings['channel_id']) ? $settings['channel_id'] : '';
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';

        if (empty($channel_id) || empty($api_key)) {
            return;
        }

        // Use the existing importer to perform a light sync (new videos only)
        $this->sync_recent_videos($channel_id, 1); // Check for videos from last 24 hours

        // Optionally, we could do a more comprehensive check weekly
        $last_full_import = get_option('eyss_last_full_auto_import', 0);
        $one_week_ago = time() - (7 * 24 * 60 * 60);

        if ($last_full_import < $one_week_ago) {
            // Do a more comprehensive import weekly
            $importer = new EYSS_Video_Importer();
            $result = $importer->import_channel_videos($channel_id, false);

            if (!is_wp_error($result)) {
                update_option('eyss_last_full_auto_import', time());
            }
        }
    }

    /**
     * Create cache table
     */
    private function create_cache_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'eyss_cache';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_data longtext NOT NULL,
            expiry_time datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);

        // Schedule cache cleanup
        if (!wp_next_scheduled('eyss_clear_cache')) {
            wp_schedule_event(time(), 'daily', 'eyss_clear_cache');
        }

        // Add cleanup hook
        add_action('eyss_clear_cache', array($this, 'cleanup_expired_cache'));
    }

    /**
     * Cleanup expired cache entries
     */
    public function cleanup_expired_cache()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'eyss_cache';

        // Delete expired entries
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for cache maintenance, not suitable for WP caching
        $wpdb->query($wpdb->prepare("DELETE FROM %i WHERE expiry_time < NOW()", $table_name));

        // Optimize table if too many deletions
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for table optimization
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i", $table_name));
        if ($count > 1000) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for table optimization, not suitable for WP caching
            $wpdb->query($wpdb->prepare("OPTIMIZE TABLE %i", $table_name));
        }
    }
}

// Initialize the plugin
function eyss_init()
{
    return EmbedYouTubeShorts::get_instance();
}

// Start the plugin
eyss_init();

// Activation hook to flush rewrite rules and ensure proper registration
register_activation_hook(__FILE__, function () {
    // Ensure classes are available
    if (!class_exists('EYSS_Post_Type')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-post-type.php';
    }

    // Initialize post type and taxonomy
    $post_type = new EYSS_Post_Type();

    // Flush rewrite rules to ensure taxonomy and post type are properly registered
    flush_rewrite_rules();
});
