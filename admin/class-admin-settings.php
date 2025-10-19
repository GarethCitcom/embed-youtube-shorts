<?php

/**
 * Admin Settings Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EYSS_Admin_Settings
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_eyss_test_api', array($this, 'test_api_connection'));
        add_action('wp_ajax_eyss_debug_videos', array($this, 'debug_videos'));
        add_action('wp_ajax_eyss_import_videos', array($this, 'ajax_import_videos'));
        add_action('wp_ajax_eyss_get_import_progress', array($this, 'ajax_get_import_progress'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_options_page(
            __('Embed YouTube Shorts', 'embed-youtube-shorts'),
            __('YouTube Shorts', 'embed-youtube-shorts'),
            'manage_options',
            'embed-youtube-shorts',
            array($this, 'settings_page')
        );
    }

    /**
     * Initialize settings
     */
    public function init_settings()
    {
        register_setting('eyss_settings_group', 'eyss_settings', array($this, 'sanitize_settings'));

        // API Settings Section
        add_settings_section(
            'eyss_api_section',
            __('YouTube API Settings', 'embed-youtube-shorts'),
            array($this, 'api_section_callback'),
            'embed-youtube-shorts'
        );

        add_settings_field(
            'api_key',
            __('YouTube API Key', 'embed-youtube-shorts'),
            array($this, 'api_key_callback'),
            'embed-youtube-shorts',
            'eyss_api_section'
        );

        add_settings_field(
            'channel_id',
            __('Channel ID', 'embed-youtube-shorts'),
            array($this, 'channel_id_callback'),
            'embed-youtube-shorts',
            'eyss_api_section'
        );

        // Display Settings Section
        add_settings_section(
            'eyss_display_section',
            __('Display Settings', 'embed-youtube-shorts'),
            array($this, 'display_section_callback'),
            'embed-youtube-shorts'
        );

        add_settings_field(
            'default_layout',
            __('Default Layout', 'embed-youtube-shorts'),
            array($this, 'default_layout_callback'),
            'embed-youtube-shorts',
            'eyss_display_section'
        );

        add_settings_field(
            'videos_per_page',
            __('Videos Per Page', 'embed-youtube-shorts'),
            array($this, 'videos_per_page_callback'),
            'embed-youtube-shorts',
            'eyss_display_section'
        );

        add_settings_field(
            'max_short_duration',
            __('Max Short Duration (seconds)', 'embed-youtube-shorts'),
            array($this, 'max_short_duration_callback'),
            'embed-youtube-shorts',
            'eyss_display_section'
        );

        add_settings_field(
            'cache_duration',
            __('Cache Duration (seconds)', 'embed-youtube-shorts'),
            array($this, 'cache_duration_callback'),
            'embed-youtube-shorts',
            'eyss_display_section'
        );

        add_settings_field(
            'scroll_type',
            __('Load More Style', 'embed-youtube-shorts'),
            array($this, 'scroll_type_callback'),
            'embed-youtube-shorts',
            'eyss_display_section'
        );

        // Video Import Section
        add_settings_section(
            'eyss_import_section',
            __('Video Import & Management', 'embed-youtube-shorts'),
            array($this, 'import_section_callback'),
            'embed-youtube-shorts'
        );

        add_settings_field(
            'import_videos',
            __('Import Videos', 'embed-youtube-shorts'),
            array($this, 'import_videos_callback'),
            'embed-youtube-shorts',
            'eyss_import_section'
        );

        add_settings_field(
            'auto_import_enabled',
            __('Automatic Daily Import', 'embed-youtube-shorts'),
            array($this, 'auto_import_enabled_callback'),
            'embed-youtube-shorts',
            'eyss_import_section'
        );

        add_settings_field(
            'auto_import_time',
            __('Import Time', 'embed-youtube-shorts'),
            array($this, 'auto_import_time_callback'),
            'embed-youtube-shorts',
            'eyss_import_section'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        $sanitized['channel_id'] = sanitize_text_field($input['channel_id']);
        $sanitized['default_layout'] = in_array($input['default_layout'], array('grid', 'carousel', 'list')) ? $input['default_layout'] : 'grid';
        $sanitized['videos_per_page'] = absint($input['videos_per_page']) ?: 12;
        $sanitized['max_short_duration'] = max(60, min(600, absint($input['max_short_duration']) ?: 180)); // Between 60-600 seconds
        $sanitized['cache_duration'] = absint($input['cache_duration']) ?: 3600;
        $sanitized['scroll_type'] = in_array($input['scroll_type'], array('load_more', 'infinite_scroll')) ? $input['scroll_type'] : 'load_more';

        // Auto-import settings
        $sanitized['auto_import_enabled'] = isset($input['auto_import_enabled']) ? (bool) $input['auto_import_enabled'] : false;
        $sanitized['auto_import_time'] = isset($input['auto_import_time']) ? sanitize_text_field($input['auto_import_time']) : '03:00';

        // Validate time format (HH:MM)
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $sanitized['auto_import_time'])) {
            $sanitized['auto_import_time'] = '03:00';
        }

        // Handle cron scheduling
        $this->handle_cron_scheduling($sanitized['auto_import_enabled'], $sanitized['auto_import_time']);

        return $sanitized;
    }

    /**
     * Handle cron job scheduling/unscheduling
     */
    private function handle_cron_scheduling($enabled, $time)
    {
        // Clear existing scheduled event
        $timestamp = wp_next_scheduled('eyss_daily_import');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'eyss_daily_import');
        }

        // Schedule new event if enabled
        if ($enabled) {
            // Parse the time
            list($hour, $minute) = explode(':', $time);

            // Calculate next occurrence
            $next_run = strtotime("today {$hour}:{$minute}");
            if ($next_run <= time()) {
                $next_run = strtotime("tomorrow {$hour}:{$minute}");
            }

            wp_schedule_event($next_run, 'daily', 'eyss_daily_import');
        }
    }

    /**
     * API section callback
     */
    public function api_section_callback()
    {
        echo '<p>' . esc_html__('Configure your YouTube API settings. You can get an API key from the Google Developers Console.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Display section callback
     */
    public function display_section_callback()
    {
        echo '<p>' . esc_html__('Configure how the YouTube Shorts will be displayed on your website.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * API Key field callback
     */
    public function api_key_callback()
    {
        $options = get_option('eyss_settings');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';

        echo '<input type="password" id="api_key" name="eyss_settings[api_key]" value="' . esc_attr($api_key) . '" size="50" />';
        echo '<button type="button" id="eyss-test-api" class="button button-secondary" style="margin-left: 10px;">' . esc_html__('Test Connection', 'embed-youtube-shorts') . '</button>';
        echo '<button type="button" id="eyss-debug-videos" class="button button-secondary" style="margin-left: 10px;">' . esc_html__('Debug Videos', 'embed-youtube-shorts') . '</button>';
        echo '<p class="description">' . wp_kses(__('Your YouTube Data API v3 key. <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">Get your API key here</a>.', 'embed-youtube-shorts'), array('a' => array('href' => array(), 'target' => array()))) . '</p>';
        echo '<div id="eyss-api-test-result"></div>';
        echo '<div id="eyss-debug-result" style="margin-top: 15px;"></div>';
    }

    /**
     * Channel ID field callback
     */
    public function channel_id_callback()
    {
        $options = get_option('eyss_settings');
        $channel_id = isset($options['channel_id']) ? $options['channel_id'] : '';

        echo '<input type="text" id="channel_id" name="eyss_settings[channel_id]" value="' . esc_attr($channel_id) . '" size="30" />';
        echo '<p class="description">' . esc_html__('The YouTube Channel ID to fetch shorts from. Format: UCxxxxxxxxxxxxxxxxxxxxx', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Default Layout field callback
     */
    public function default_layout_callback()
    {
        $options = get_option('eyss_settings');
        $default_layout = isset($options['default_layout']) ? $options['default_layout'] : 'grid';

        echo '<select id="default_layout" name="eyss_settings[default_layout]">';
        echo '<option value="grid"' . selected($default_layout, 'grid', false) . '>' . esc_html__('Grid', 'embed-youtube-shorts') . '</option>';
        echo '<option value="carousel"' . selected($default_layout, 'carousel', false) . '>' . esc_html__('Carousel', 'embed-youtube-shorts') . '</option>';
        echo '<option value="list"' . selected($default_layout, 'list', false) . '>' . esc_html__('List', 'embed-youtube-shorts') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('The default layout for displaying YouTube Shorts.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Videos Per Page field callback
     */
    public function videos_per_page_callback()
    {
        $options = get_option('eyss_settings');
        $videos_per_page = isset($options['videos_per_page']) ? $options['videos_per_page'] : 12;

        echo '<input type="number" id="videos_per_page" name="eyss_settings[videos_per_page]" value="' . esc_attr($videos_per_page) . '" min="1" max="50" />';
        echo '<p class="description">' . esc_html__('Maximum number of videos to display per page (1-50).', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Max Short Duration field callback
     */
    public function max_short_duration_callback()
    {
        $options = get_option('eyss_settings');
        $max_short_duration = isset($options['max_short_duration']) ? $options['max_short_duration'] : 180;

        echo '<input type="number" id="max_short_duration" name="eyss_settings[max_short_duration]" value="' . esc_attr($max_short_duration) . '" min="60" max="600" />';
        echo '<p class="description">' . esc_html__('Maximum duration (in seconds) for a video to be considered a Short. YouTube allows up to 3 minutes (180 seconds). Range: 60-600 seconds.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Cache Duration field callback
     */
    public function cache_duration_callback()
    {
        $options = get_option('eyss_settings');
        $cache_duration = isset($options['cache_duration']) ? $options['cache_duration'] : 3600;

        echo '<input type="number" id="cache_duration" name="eyss_settings[cache_duration]" value="' . esc_attr($cache_duration) . '" min="300" />';
        echo '<p class="description">' . esc_html__('How long to cache YouTube API responses (in seconds). Minimum 300 seconds (5 minutes).', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Scroll Type field callback
     */
    public function scroll_type_callback()
    {
        $options = get_option('eyss_settings');
        $scroll_type = isset($options['scroll_type']) ? $options['scroll_type'] : 'load_more';

        echo '<label><input type="radio" name="eyss_settings[scroll_type]" value="load_more" ' . checked($scroll_type, 'load_more', false) . ' /> ' . esc_html__('Load More Button', 'embed-youtube-shorts') . '</label><br>';
        echo '<label><input type="radio" name="eyss_settings[scroll_type]" value="infinite_scroll" ' . checked($scroll_type, 'infinite_scroll', false) . ' /> ' . esc_html__('Infinite Scroll', 'embed-youtube-shorts') . '</label>';
        echo '<p class="description">' . esc_html__('Choose how additional videos are loaded when there are more than the initial count.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Settings page
     */
    public function settings_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div id="eyss-settings-container">
                <div id="eyss-settings-form">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('eyss_settings_group');
                        do_settings_sections('embed-youtube-shorts');
                        submit_button();
                        ?>
                    </form>
                </div>

                <div id="eyss-settings-sidebar">
                    <div class="eyss-sidebar-box">
                        <h3><?php esc_html_e('Shortcode Usage', 'embed-youtube-shorts'); ?></h3>
                        <p><?php esc_html_e('Use the following shortcode to display YouTube Shorts:', 'embed-youtube-shorts'); ?></p>
                        <code>[youtube_shorts]</code>

                        <h4><?php esc_html_e('Basic Parameters:', 'embed-youtube-shorts'); ?></h4>
                        <ul>
                            <li><code>layout</code> - grid, carousel, or list (default: from settings)</li>
                            <li><code>count</code> - number of videos to show (default: from settings)</li>
                            <li><code>channel</code> - specific channel ID (default: from settings)</li>
                            <li><code>autoplay</code> - true/false for modal autoplay</li>
                            <li><code>show_title</code> - true/false to show video titles</li>
                            <li><code>show_duration</code> - true/false to show video duration</li>
                            <li><code>show_views</code> - true/false to show view counts</li>
                            <li><code>show_search</code> - true/false to show search functionality</li>
                        </ul>

                        <h4><?php esc_html_e('Playlist Filtering:', 'embed-youtube-shorts'); ?></h4>
                        <ul>
                            <li><code>playlist</code> - single playlist (ID, slug, or name)</li>
                            <li><code>playlists</code> - multiple playlists (comma-separated)</li>
                            <li><code>exclude_playlist</code> - exclude specific playlists</li>
                            <li><code>show_playlists</code> - true/false to show playlist names</li>
                        </ul>

                        <h4><?php esc_html_e('Basic Examples:', 'embed-youtube-shorts'); ?></h4>
                        <p><code>[youtube_shorts]</code><br>
                            <small>Default layout with all imported videos</small>
                        </p>

                        <p><code>[youtube_shorts layout="carousel" count="8"]</code><br>
                            <small>Carousel layout showing 8 videos</small>
                        </p>

                        <p><code>[youtube_shorts layout="list" show_search="true"]</code><br>
                            <small>List layout with search functionality</small>
                        </p>

                        <h4><?php esc_html_e('Playlist Examples:', 'embed-youtube-shorts'); ?></h4>
                        <p><code>[youtube_shorts playlist="cooking-basics"]</code><br>
                            <small>Videos from "cooking-basics" playlist</small>
                        </p>

                        <p><code>[youtube_shorts playlists="tutorials,reviews,tips"]</code><br>
                            <small>Videos from multiple playlists</small>
                        </p>

                        <p><code>[youtube_shorts exclude_playlist="private,drafts"]</code><br>
                            <small>All videos except private and draft playlists</small>
                        </p>

                        <p><code>[youtube_shorts playlist="featured" show_playlists="true"]</code><br>
                            <small>Featured playlist with playlist names displayed</small>
                        </p>

                        <h4><?php esc_html_e('Advanced Examples:', 'embed-youtube-shorts'); ?></h4>
                        <p><code>[youtube_shorts playlists="beginner,intermediate" layout="carousel" count="6" show_playlists="true"]</code><br>
                            <small>Multiple playlists in carousel with playlist info</small>
                        </p>
                    </div>

                    <div class="eyss-sidebar-box">
                        <h3><?php esc_html_e('Playlist Management', 'embed-youtube-shorts'); ?></h3>
                        <p><?php esc_html_e('Videos are automatically organized by their YouTube playlist membership when imported.', 'embed-youtube-shorts'); ?></p>

                        <h4><?php esc_html_e('Playlist Identifiers:', 'embed-youtube-shorts'); ?></h4>
                        <ul>
                            <li><strong>YouTube ID:</strong> <code>PLxxx123456789</code></li>
                            <li><strong>Slug:</strong> <code>my-cooking-videos</code></li>
                            <li><strong>Name:</strong> <code>Cooking Basics</code></li>
                        </ul>

                        <p><a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=youtube_playlist&post_type=youtube_short')); ?>" class="button button-secondary"><?php esc_html_e('Manage Playlists', 'embed-youtube-shorts'); ?></a></p>
                        <p><a href="<?php echo esc_url(admin_url('edit.php?post_type=youtube_short')); ?>" class="button button-secondary"><?php esc_html_e('View Videos', 'embed-youtube-shorts'); ?></a></p>
                        <p><a href="<?php echo esc_url(add_query_arg(array('page' => 'embed-youtube-shorts', 'playlist_test' => '1'), admin_url('options-general.php'))); ?>" class="button button-secondary"><?php esc_html_e('Test Playlists', 'embed-youtube-shorts'); ?></a></p>
                    </div>

                    <div class="eyss-sidebar-box">
                        <h3><?php esc_html_e('Need Help?', 'embed-youtube-shorts'); ?></h3>
                        <p><?php esc_html_e('To get your YouTube API key:', 'embed-youtube-shorts'); ?></p>
                        <ol>
                            <li><?php esc_html_e('Visit the Google Developers Console', 'embed-youtube-shorts'); ?></li>
                            <li><?php esc_html_e('Create a new project or select existing', 'embed-youtube-shorts'); ?></li>
                            <li><?php esc_html_e('Enable the YouTube Data API v3', 'embed-youtube-shorts'); ?></li>
                            <li><?php esc_html_e('Create credentials (API key)', 'embed-youtube-shorts'); ?></li>
                        </ol>
                        <p><a href="https://developers.google.com/youtube/v3/getting-started" target="_blank" class="button button-primary"><?php esc_html_e('Get API Key', 'embed-youtube-shorts'); ?></a></p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            #eyss-settings-container {
                display: flex;
                gap: 20px;
            }

            #eyss-settings-form {
                flex: 2;
            }

            #eyss-settings-sidebar {
                flex: 1;
            }

            .eyss-sidebar-box {
                background: #fff;
                border: 1px solid #ccd0d4;
                padding: 15px;
                margin-bottom: 15px;
            }

            .eyss-sidebar-box h3 {
                margin-top: 0;
            }

            .eyss-sidebar-box code {
                background: #f1f1f1;
                padding: 2px 4px;
                font-size: 11px;
            }

            .eyss-sidebar-box ul,
            .eyss-sidebar-box ol {
                margin-left: 20px;
            }

            #eyss-api-test-result {
                margin-top: 10px;
            }

            #eyss-api-test-result.success {
                color: #00a32a;
            }

            #eyss-api-test-result.error {
                color: #d63638;
            }
        </style>
    <?php
    }

    /**
     * Test API connection via AJAX
     */
    public function test_api_connection()
    {
        try {
            // Check if nonce exists
            if (!isset($_POST['nonce'])) {
                wp_send_json_error(__('Security nonce is missing.', 'embed-youtube-shorts'));
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eyss_nonce')) {
                wp_send_json_error(__('Security check failed. Please refresh the page and try again.', 'embed-youtube-shorts'));
                return;
            }

            // Check if required fields exist
            if (!isset($_POST['api_key'])) {
                wp_send_json_error(__('API key parameter is missing.', 'embed-youtube-shorts'));
                return;
            }

            $api_key = sanitize_text_field(wp_unslash($_POST['api_key']));
            $channel_id = isset($_POST['channel_id']) ? sanitize_text_field(wp_unslash($_POST['channel_id'])) : '';

            if (empty($api_key)) {
                wp_send_json_error(__('API key is required.', 'embed-youtube-shorts'));
                return;
            }

            // Include YouTube API class
            if (!class_exists('EYSS_YouTube_API')) {
                require_once EYSS_PLUGIN_PATH . 'includes/class-youtube-api.php';
            }

            $youtube_api = new EYSS_YouTube_API();
            $result = $youtube_api->test_connection($api_key, $channel_id);

            if ($result['success']) {
                wp_send_json_success($result['message']);
            } else {
                wp_send_json_error($result['message']);
            }
        } catch (Exception $e) {
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Debug videos via AJAX
     */
    public function debug_videos()
    {
        try {
            // Check if nonce exists
            if (!isset($_POST['nonce'])) {
                wp_send_json_error(__('Security nonce is missing.', 'embed-youtube-shorts'));
                return;
            }

            // Verify nonce
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eyss_nonce')) {
                wp_send_json_error(__('Security check failed. Please refresh the page and try again.', 'embed-youtube-shorts'));
                return;
            }

            // Check if required fields exist
            if (!isset($_POST['api_key'])) {
                wp_send_json_error(__('API key parameter is missing.', 'embed-youtube-shorts'));
                return;
            }

            $api_key = sanitize_text_field(wp_unslash($_POST['api_key']));
            $channel_id = isset($_POST['channel_id']) ? sanitize_text_field(wp_unslash($_POST['channel_id'])) : '';

            if (empty($api_key)) {
                wp_send_json_error(__('API key is required.', 'embed-youtube-shorts'));
                return;
            }

            if (empty($channel_id)) {
                wp_send_json_error(__('Channel ID is required.', 'embed-youtube-shorts'));
                return;
            }

            // Include YouTube API class
            if (!class_exists('EYSS_YouTube_API')) {
                require_once EYSS_PLUGIN_PATH . 'includes/class-youtube-api.php';
            }

            $youtube_api = new EYSS_YouTube_API();
            $debug_result = $youtube_api->debug_channel_videos($channel_id, 20);

            if (isset($debug_result['error'])) {
                wp_send_json_error($debug_result['error']);
            } else {
                wp_send_json_success($debug_result);
            }
        } catch (Exception $e) {
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Import section callback
     */
    public function import_section_callback()
    {
        echo '<p>' . esc_html__('Import videos from YouTube channels to your WordPress database. This allows for unlimited results and better performance.', 'embed-youtube-shorts') . '</p>';
        echo '<p>' . esc_html__('You can import manually or set up automatic daily imports to keep your video library up-to-date.', 'embed-youtube-shorts') . '</p>';
    }

    /**
     * Import videos callback
     */
    public function import_videos_callback()
    {
        $settings = get_option('eyss_settings', array());
        $channel_id = isset($settings['channel_id']) ? $settings['channel_id'] : '';

        // Get current video count
        $current_count = wp_count_posts('youtube_short');
        $published_count = $current_count->publish ?? 0;
    ?>

        <div id="eyss-import-container">
            <p>
                <strong><?php esc_html_e('Current Status:', 'embed-youtube-shorts'); ?></strong>
                <?php
                // translators: %d is the number of imported videos
                printf(esc_html__('%d videos imported', 'embed-youtube-shorts'), esc_html($published_count)); ?>
            </p>

            <div class="eyss-import-controls">
                <button type="button" id="eyss-import-btn" class="button button-primary"
                    <?php echo empty($channel_id) ? 'disabled' : ''; ?>>
                    <?php esc_html_e('Import All Videos', 'embed-youtube-shorts'); ?>
                </button>

                <button type="button" id="eyss-force-import-btn" class="button button-secondary"
                    <?php echo empty($channel_id) ? 'disabled' : ''; ?>>
                    <?php esc_html_e('Force Re-import', 'embed-youtube-shorts'); ?>
                </button>

                <a href="<?php echo esc_url(admin_url('edit.php?post_type=youtube_short')); ?>" class="button">
                    <?php esc_html_e('Manage Videos', 'embed-youtube-shorts'); ?>
                </a>
            </div>

            <?php if (empty($channel_id)): ?>
                <p class="description" style="color: #d63638;">
                    <?php esc_html_e('Please configure your Channel ID above before importing videos.', 'embed-youtube-shorts'); ?>
                </p>
            <?php else: ?>
                <p class="description">
                    <?php
                    // translators: %s is the YouTube channel ID
                    printf(esc_html__('This will import all YouTube Shorts from channel: %s', 'embed-youtube-shorts'), '<code>' . esc_html($channel_id) . '</code>'); ?>
                </p>
            <?php endif; ?>

            <div id="eyss-import-progress" style="display: none;">
                <h4><?php esc_html_e('Import Progress', 'embed-youtube-shorts'); ?></h4>
                <div id="eyss-progress-bar-container">
                    <div id="eyss-progress-bar" style="width: 0%; height: 20px; background: #007cba; transition: width 0.3s ease;"></div>
                </div>
                <div id="eyss-progress-info">
                    <p id="eyss-progress-status"><?php esc_html_e('Starting import...', 'embed-youtube-shorts'); ?></p>
                    <p id="eyss-progress-details"></p>
                </div>
                <div id="eyss-progress-results" style="display: none;">
                    <h4><?php esc_html_e('Import Results', 'embed-youtube-shorts'); ?></h4>
                    <ul id="eyss-import-stats"></ul>
                </div>
            </div>
        </div>

        <style>
            #eyss-import-container {
                max-width: 600px;
            }

            .eyss-import-controls {
                margin: 15px 0;
            }

            .eyss-import-controls .button {
                margin-right: 10px;
            }

            #eyss-progress-bar-container {
                background: #f0f0f1;
                border: 1px solid #c3c4c7;
                border-radius: 3px;
                overflow: hidden;
                margin: 10px 0;
            }

            #eyss-import-progress {
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 15px;
                margin-top: 15px;
                border-radius: 4px;
            }
        </style>
    <?php
    }

    /**
     * Auto import enabled callback
     */
    public function auto_import_enabled_callback()
    {
        $settings = get_option('eyss_settings', array());
        $enabled = isset($settings['auto_import_enabled']) ? $settings['auto_import_enabled'] : false;
        $next_scheduled = wp_next_scheduled('eyss_daily_import');
    ?>
        <label>
            <input type="checkbox" name="eyss_settings[auto_import_enabled]" value="1" <?php checked($enabled, true); ?> />
            <?php esc_html_e('Enable automatic daily video import', 'embed-youtube-shorts'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, the plugin will automatically check for new videos daily and import them.', 'embed-youtube-shorts'); ?>
            <?php if ($next_scheduled): ?>
                <br><strong><?php
                            // translators: %s is the formatted date and time of the next scheduled import
                            printf(esc_html__('Next scheduled import: %s', 'embed-youtube-shorts'), esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_scheduled))); ?></strong>
            <?php elseif ($enabled): ?>
                <br><em><?php esc_html_e('Auto-import will be scheduled after saving settings.', 'embed-youtube-shorts'); ?></em>
            <?php endif; ?>
        </p>
    <?php
    }

    /**
     * Auto import time callback
     */
    public function auto_import_time_callback()
    {
        $settings = get_option('eyss_settings', array());
        $time = isset($settings['auto_import_time']) ? $settings['auto_import_time'] : '03:00';
    ?>
        <input type="time" name="eyss_settings[auto_import_time]" value="<?php echo esc_attr($time); ?>" />
        <p class="description">
            <?php esc_html_e('Set the time when automatic imports should run daily (server time).', 'embed-youtube-shorts'); ?>
            <br><em><?php
                    // translators: %s is the current server time in the site's time format
                    printf(esc_html__('Current server time: %s', 'embed-youtube-shorts'), esc_html(date_i18n(get_option('time_format')))); ?></em>
        </p>
<?php
    }

    /**
     * AJAX handler for importing videos
     */
    public function ajax_import_videos()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'embed-youtube-shorts'));
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eyss_nonce')) {
            wp_send_json_error(__('Security check failed.', 'embed-youtube-shorts'));
        }

        $settings = get_option('eyss_settings', array());
        $channel_id = isset($settings['channel_id']) ? $settings['channel_id'] : '';
        $force_refresh = isset($_POST['force_refresh']) && $_POST['force_refresh'] === 'true';

        if (empty($channel_id)) {
            wp_send_json_error(__('Channel ID is required in settings.', 'embed-youtube-shorts'));
        }

        // Start background import
        $importer = new EYSS_Video_Importer();
        wp_schedule_single_event(time() + 5, 'eyss_bg_import', array($channel_id, $force_refresh));

        wp_send_json_success(array(
            'message' => __('Import started in background. Progress will update below.', 'embed-youtube-shorts'),
            'progress_key' => 'eyss_import_progress_' . $channel_id
        ));
    }

    /**
     * AJAX handler for getting import progress
     */
    public function ajax_get_import_progress()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'embed-youtube-shorts'));
        }

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eyss_nonce')) {
            wp_send_json_error(__('Security check failed.', 'embed-youtube-shorts'));
        }

        $progress_key = isset($_GET['progress_key']) ? sanitize_text_field(wp_unslash($_GET['progress_key'])) : '';

        if (empty($progress_key)) {
            wp_send_json_error(__('Progress key is required.', 'embed-youtube-shorts'));
        }

        $progress = get_option($progress_key, array());

        if (empty($progress)) {
            wp_send_json_error(__('No import in progress.', 'embed-youtube-shorts'));
        }

        wp_send_json_success($progress);
    }
}
