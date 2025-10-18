<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EYSS_Shortcode
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_shortcode('youtube_shorts', array($this, 'youtube_shorts_shortcode'));
        add_action('wp_ajax_eyss_load_more', array($this, 'load_more_videos'));
        add_action('wp_ajax_nopriv_eyss_load_more', array($this, 'load_more_videos'));
        add_action('wp_ajax_eyss_search_videos', array($this, 'search_videos'));
        add_action('wp_ajax_nopriv_eyss_search_videos', array($this, 'search_videos'));
    }

    /**
     * YouTube Shorts shortcode
     */
    public function youtube_shorts_shortcode($atts)
    {
        // Get plugin settings
        $settings = get_option('eyss_settings', array());
        $scroll_type = isset($settings['scroll_type']) ? $settings['scroll_type'] : 'load_more';

        // Parse shortcode attributes
        $original_atts = $atts; // Store original to check if count was explicitly set
        $atts = shortcode_atts(array(
            'layout' => isset($settings['default_layout']) ? $settings['default_layout'] : 'grid',
            'count' => isset($settings['videos_per_page']) ? $settings['videos_per_page'] : 12,
            'channel' => isset($settings['channel_id']) ? $settings['channel_id'] : '',
            'playlist' => '', // Single playlist ID, slug, or title
            'playlists' => '', // Comma-separated list of playlists
            'exclude_playlist' => '', // Exclude specific playlist(s)
            'autoplay' => 'false',
            'show_title' => 'true',
            'show_duration' => 'true',
            'show_views' => 'true',
            'show_playlists' => 'false',
            'show_search' => 'false',
            'search_placeholder' => 'Search videos...',
            'search' => '',
        ), $atts, 'youtube_shorts');

        // Validate layout
        if (!in_array($atts['layout'], array('grid', 'carousel', 'list'))) {
            $atts['layout'] = 'grid';
        }

        // Set default count based on layout
        if ($atts['layout'] === 'carousel' && !isset($original_atts['count'])) {
            // For carousel, default to 8 videos if count wasn't explicitly set in shortcode
            $atts['count'] = 8;
        }

        // Validate count
        $atts['count'] = max(1, min(50, intval($atts['count'])));

        // Get videos from WordPress posts
        $videos = $this->get_videos_from_posts($atts);

        if (empty($videos)) {
            return $this->render_error(__('No YouTube Shorts found. Please import videos first from Settings > YouTube Shorts.', 'embed-youtube-shorts'));
        }

        // Generate unique ID for this shortcode instance
        $instance_id = 'eyss-' . wp_generate_uuid4();

        // Render the shortcode output
        return $this->render_shortcode_output($videos, $atts, $instance_id, $scroll_type);
    }

    /**
     * Get videos from WordPress posts
     */
    private function get_videos_from_posts($atts)
    {
        $args = array(
            'post_type' => 'youtube_short',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['count']),
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array()
        );

        // Add search support
        if (!empty($atts['search'])) {
            $args['s'] = sanitize_text_field($atts['search']);
        }

        // Filter by channel if specified
        if (!empty($atts['channel'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_channel_id',
                'value' => $atts['channel'],
                'compare' => '='
            );
        }

        // Handle playlist filtering using taxonomy
        $tax_queries = array();

        // Single playlist filter
        if (!empty($atts['playlist'])) {
            $playlist_field = $this->determine_playlist_field($atts['playlist']);
            $tax_queries[] = array(
                'taxonomy' => 'youtube_playlist',
                'field' => $playlist_field,
                'terms' => $atts['playlist']
            );
        }

        // Multiple playlists filter
        if (!empty($atts['playlists'])) {
            $playlists = array_map('trim', explode(',', $atts['playlists']));
            $playlist_terms = array();
            $playlist_field = 'slug'; // Default field

            foreach ($playlists as $playlist) {
                if (!empty($playlist)) {
                    $playlist_terms[] = $playlist;
                    // Use 'name' field if any playlist looks like an ID
                    if (strpos($playlist, 'PL') === 0) {
                        $playlist_field = 'name';
                    }
                }
            }

            if (!empty($playlist_terms)) {
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $playlist_field,
                    'terms' => $playlist_terms,
                    'operator' => 'IN'
                );
            }
        }

        // Exclude specific playlists
        if (!empty($atts['exclude_playlist'])) {
            $exclude_playlists = array_map('trim', explode(',', $atts['exclude_playlist']));
            $exclude_terms = array();
            $exclude_field = 'slug';

            foreach ($exclude_playlists as $playlist) {
                if (!empty($playlist)) {
                    $exclude_terms[] = $playlist;
                    if (strpos($playlist, 'PL') === 0) {
                        $exclude_field = 'name';
                    }
                }
            }

            if (!empty($exclude_terms)) {
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $exclude_field,
                    'terms' => $exclude_terms,
                    'operator' => 'NOT IN'
                );
            }
        }

        // Apply taxonomy queries
        if (!empty($tax_queries)) {
            if (count($tax_queries) > 1) {
                $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_queries);
            } else {
                $args['tax_query'] = $tax_queries;
            }
        }

        // Ensure we have at least one meta query condition if no specific filters
        if (empty($args['meta_query'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_video_id',
                'value' => '',
                'compare' => '!='
            );
        }

        $query = new WP_Query($args);
        $videos = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get playlist information for this video
                $playlists = wp_get_post_terms($post_id, 'youtube_playlist', array('fields' => 'names'));
                $playlist_names = is_array($playlists) ? $playlists : array();

                $video_data = array(
                    'id' => get_post_meta($post_id, '_eyss_video_id', true),
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'thumbnail' => get_post_meta($post_id, '_eyss_thumbnail_url', true) ?: get_the_post_thumbnail_url($post_id, 'medium'),
                    'published_at' => get_post_meta($post_id, '_eyss_published_at', true) ?: get_the_date('Y-m-d H:i:s'),
                    'duration' => intval(get_post_meta($post_id, '_eyss_duration', true)),
                    'view_count' => intval(get_post_meta($post_id, '_eyss_view_count', true)),
                    'like_count' => intval(get_post_meta($post_id, '_eyss_like_count', true)),
                    'url' => get_post_meta($post_id, '_eyss_youtube_url', true),
                    'channel_title' => get_post_meta($post_id, '_eyss_channel_title', true),
                    'playlists' => $playlist_names,
                    'post_id' => $post_id
                );

                $videos[] = $video_data;
            }
            wp_reset_postdata();
        }

        return $videos;
    }

    /**
     * Render shortcode output
     */
    private function render_shortcode_output($videos, $atts, $instance_id, $scroll_type)
    {
        $layout = $atts['layout'];
        $show_title = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $show_duration = filter_var($atts['show_duration'], FILTER_VALIDATE_BOOLEAN);
        $show_views = filter_var($atts['show_views'], FILTER_VALIDATE_BOOLEAN);
        $show_playlists = filter_var($atts['show_playlists'], FILTER_VALIDATE_BOOLEAN);
        $autoplay = filter_var($atts['autoplay'], FILTER_VALIDATE_BOOLEAN);
        $show_search = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        $search_placeholder = esc_attr($atts['search_placeholder']);

        ob_start();
?>
        <div id="<?php echo esc_attr($instance_id); ?>" class="eyss-container eyss-layout-<?php echo esc_attr($layout); ?>"
            data-layout="<?php echo esc_attr($layout); ?>"
            data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
            data-scroll-type="<?php echo esc_attr($scroll_type); ?>"
            data-channel="<?php echo esc_attr($atts['channel']); ?>"
            data-playlist="<?php echo esc_attr($atts['playlist']); ?>"
            data-playlists="<?php echo esc_attr($atts['playlists']); ?>"
            data-exclude-playlist="<?php echo esc_attr($atts['exclude_playlist']); ?>">

            <?php if ($show_search && $layout !== 'carousel'): ?>
                <div class="eyss-search-container">
                    <div class="eyss-search-input-wrapper">
                        <input type="text"
                            class="eyss-search-input"
                            placeholder="<?php echo $search_placeholder; ?>"
                            data-target="<?php echo esc_attr($instance_id); ?>">
                        <button type="button" class="eyss-search-clear" title="<?php _e('Clear search', 'embed-youtube-shorts'); ?>" style="display: none;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="eyss-search-results-count">
                        <span class="eyss-total-count"><?php echo $this->get_total_video_count($atts); ?></span>
                        <?php _e('videos found', 'embed-youtube-shorts'); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($layout === 'carousel'): ?>
                <?php
                // Prepare Splide configuration
                $splide_config = array(
                    'type' => 'loop',
                    'focus' => 'center',
                    'perPage' => 6,
                    'perMove' => 1,
                    'gap' => '20px',
                    'padding' => '0',
                    'breakpoints' => array(
                        '1240' => array('perPage' => 4),
                        '768' => array('perPage' => 2),
                        '480' => array('perPage' => 1)
                    ),
                    'pagination' => false,
                    'arrows' => true
                );
                $splide_json = wp_json_encode($splide_config);
                ?>
                <div class="splide eyss-carousel-splide" data-splide='<?php echo esc_attr($splide_json); ?>'>
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($videos as $video): ?>
                                <li class="splide__slide">
                                    <div class="eyss-video-item"
                                        data-video-id="<?php echo esc_attr($video['id']); ?>"
                                        data-video-title="<?php echo esc_attr(strtolower($video['title'])); ?>">
                                        <div class="eyss-video-thumbnail">
                                            <img src="<?php echo esc_url($video['thumbnail']); ?>"
                                                alt="<?php echo esc_attr($video['title']); ?>"
                                                loading="lazy">
                                            <div class="eyss-play-button">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M8 5v14l11-7z" />
                                                </svg>
                                            </div>
                                            <?php if ($show_duration): ?>
                                                <div class="eyss-duration"><?php echo esc_html($this->format_duration($video['duration'])); ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($show_title || $show_views || $show_playlists): ?>
                                            <div class="eyss-video-info">
                                                <?php if ($show_title): ?>
                                                    <h3 class="eyss-video-title"><?php echo esc_html($this->truncate_title($video['title'], $layout)); ?></h3>
                                                <?php endif; ?>
                                                <?php if ($show_playlists && !empty($video['playlists'])): ?>
                                                    <div class="eyss-video-playlists">
                                                        <span class="eyss-playlists-label"><?php _e('Playlists:', 'embed-youtube-shorts'); ?></span>
                                                        <span class="eyss-playlists-list"><?php echo esc_html(implode(', ', $video['playlists'])); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($show_views && isset($video['view_count'])): ?>
                                                    <div class="eyss-video-stats">
                                                        <span class="eyss-views"><?php echo esc_html($this->format_number($video['view_count'])); ?> <?php _e('views', 'embed-youtube-shorts'); ?></span>
                                                        <span class="eyss-date"><?php echo esc_html(human_time_diff(strtotime($video['published_at']))); ?> <?php _e('ago', 'embed-youtube-shorts'); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?php echo esc_url($video['url']); ?>"
                                            class="eyss-video-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="<?php printf(__('Watch %s on YouTube', 'embed-youtube-shorts'), esc_attr($video['title'])); ?>">
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="eyss-videos <?php echo esc_attr('eyss-videos-' . $layout); ?>">
                    <?php foreach ($videos as $video): ?>
                        <div class="eyss-video-item"
                            data-video-id="<?php echo esc_attr($video['id']); ?>"
                            data-video-title="<?php echo esc_attr(strtolower($video['title'])); ?>">
                            <div class="eyss-video-thumbnail">
                                <img src="<?php echo esc_url($video['thumbnail']); ?>"
                                    alt="<?php echo esc_attr($video['title']); ?>"
                                    loading="lazy">
                                <div class="eyss-play-button">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                                <?php if ($show_duration): ?>
                                    <div class="eyss-duration"><?php echo esc_html($this->format_duration($video['duration'])); ?></div>
                                <?php endif; ?>
                            </div>

                            <?php if ($show_title || $show_views || $show_playlists): ?>
                                <div class="eyss-video-info">
                                    <?php if ($show_title): ?>
                                        <h3 class="eyss-video-title"><?php echo esc_html($this->truncate_title($video['title'], $layout)); ?></h3>
                                    <?php endif; ?>
                                    <?php if ($show_playlists && !empty($video['playlists'])): ?>
                                        <div class="eyss-video-playlists">
                                            <span class="eyss-playlists-label"><?php _e('Playlists:', 'embed-youtube-shorts'); ?></span>
                                            <span class="eyss-playlists-list"><?php echo esc_html(implode(', ', $video['playlists'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($show_views && isset($video['view_count'])): ?>
                                        <div class="eyss-video-stats">
                                            <span class="eyss-views"><?php echo esc_html($this->format_number($video['view_count'])); ?> <?php _e('views', 'embed-youtube-shorts'); ?></span>
                                            <span class="eyss-date"><?php echo esc_html(human_time_diff(strtotime($video['published_at']))); ?> <?php _e('ago', 'embed-youtube-shorts'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <a href="<?php echo esc_url($video['url']); ?>"
                                class="eyss-video-link"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="<?php printf(__('Watch %s on YouTube', 'embed-youtube-shorts'), esc_attr($video['title'])); ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_search): ?>
                <div class="eyss-no-results" style="display: none;">
                    <p><?php _e('No videos found matching your search.', 'embed-youtube-shorts'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Load More Button (disabled for carousel layout) -->
            <?php if ($layout !== 'carousel'): ?>
                <div class="eyss-load-more-container" style="text-align: center; margin-top: 20px;">
                    <button class="eyss-load-more-btn"
                        data-channel="<?php echo esc_attr($atts['channel']); ?>"
                        data-playlist="<?php echo esc_attr($atts['playlist']); ?>"
                        data-offset="<?php echo count($videos); ?>"
                        data-count="<?php echo esc_attr($atts['count']); ?>"
                        data-target="<?php echo esc_attr($instance_id); ?>">
                        <?php _e('Load More Videos', 'embed-youtube-shorts'); ?>
                    </button>
                </div>
            <?php endif; ?>

        </div>

        <!-- Video Modal -->
        <div id="eyss-modal" class="eyss-modal" style="display: none;">
            <div class="eyss-modal-content">
                <span class="eyss-modal-close">&times;</span>
                <div class="eyss-modal-video">
                    <iframe id="eyss-modal-iframe"
                        width="100%"
                        height="100%"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render error message
     */
    private function render_error($message)
    {
        return '<div class="eyss-error"><p>' . esc_html($message) . '</p></div>';
    }

    /**
     * Format duration in seconds to readable format
     */
    private function format_duration($seconds)
    {
        if ($seconds < 60) {
            return '0:' . sprintf('%02d', $seconds);
        }

        $minutes = floor($seconds / 60);
        $remaining_seconds = $seconds % 60;

        return $minutes . ':' . sprintf('%02d', $remaining_seconds);
    }

    /**
     * Format large numbers
     */
    private function format_number($number)
    {
        if ($number < 1000) {
            return $number;
        } elseif ($number < 1000000) {
            return round($number / 1000, 1) . 'K';
        } else {
            return round($number / 1000000, 1) . 'M';
        }
    }

    /**
     * Truncate title based on layout
     */
    private function truncate_title($title, $layout)
    {
        $max_length = ($layout === 'list') ? 100 : 60;

        if (strlen($title) <= $max_length) {
            return $title;
        }

        return substr($title, 0, $max_length - 3) . '...';
    }

    /**
     * Determine the appropriate field for playlist taxonomy queries
     */
    private function determine_playlist_field($playlist_value)
    {
        // If it looks like a playlist ID (starts with PL), use 'name' field
        if (strpos($playlist_value, 'PL') === 0) {
            return 'name';
        }

        // Check if it's a numeric term ID
        if (is_numeric($playlist_value)) {
            return 'term_id';
        }

        // Default to slug for readable playlist names
        return 'slug';
    }

    /**
     * Get total count of videos matching the shortcode criteria
     */
    private function get_total_video_count($atts)
    {
        $args = array(
            'post_type' => 'youtube_short',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Get all posts
            'fields' => 'ids', // Only get IDs for performance
            'meta_query' => array()
        );

        // Filter by channel if specified
        if (!empty($atts['channel'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_channel_id',
                'value' => $atts['channel'],
                'compare' => '='
            );
        }

        // Handle playlist filtering using taxonomy
        $tax_queries = array();

        // Single playlist filter
        if (!empty($atts['playlist'])) {
            $playlist_field = $this->determine_playlist_field($atts['playlist']);
            $tax_queries[] = array(
                'taxonomy' => 'youtube_playlist',
                'field' => $playlist_field,
                'terms' => $atts['playlist']
            );
        }

        // Multiple playlists filter
        if (!empty($atts['playlists'])) {
            $playlists = array_map('trim', explode(',', $atts['playlists']));
            $playlist_terms = array();
            $playlist_field = 'slug';

            foreach ($playlists as $playlist) {
                if (!empty($playlist)) {
                    $playlist_terms[] = $playlist;
                    if (strpos($playlist, 'PL') === 0) {
                        $playlist_field = 'name';
                    }
                }
            }

            if (!empty($playlist_terms)) {
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $playlist_field,
                    'terms' => $playlist_terms,
                    'operator' => 'IN'
                );
            }
        }

        // Exclude specific playlists
        if (!empty($atts['exclude_playlist'])) {
            $exclude_playlists = array_map('trim', explode(',', $atts['exclude_playlist']));
            $exclude_terms = array();
            $exclude_field = 'slug';

            foreach ($exclude_playlists as $playlist) {
                if (!empty($playlist)) {
                    $exclude_terms[] = $playlist;
                    if (strpos($playlist, 'PL') === 0) {
                        $exclude_field = 'name';
                    }
                }
            }

            if (!empty($exclude_terms)) {
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $exclude_field,
                    'terms' => $exclude_terms,
                    'operator' => 'NOT IN'
                );
            }
        }

        // Apply taxonomy queries
        if (!empty($tax_queries)) {
            if (count($tax_queries) > 1) {
                $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_queries);
            } else {
                $args['tax_query'] = $tax_queries;
            }
        }

        // Ensure we have at least one condition if no specific filters
        if (empty($args['meta_query']) && empty($args['tax_query'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_video_id',
                'value' => '',
                'compare' => '!='
            );
        }

        $query = new WP_Query($args);
        return $query->found_posts;
    }
    /**
     * Load more videos via AJAX
     */
    public function load_more_videos()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eyss_nonce')) {
            wp_die('Security check failed');
        }

        $channel_id = sanitize_text_field($_POST['channel_id'] ?? '');
        $playlist_id = sanitize_text_field($_POST['playlist_id'] ?? '');
        $offset = intval($_POST['offset']);
        $count = intval($_POST['count']);

        // Build query args for WordPress posts
        $args = array(
            'post_type' => 'youtube_short',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array()
        );

        // Filter by channel if specified
        if (!empty($channel_id)) {
            $args['meta_query'][] = array(
                'key' => '_eyss_channel_id',
                'value' => $channel_id,
                'compare' => '='
            );
        }

        // Filter by playlist using taxonomy if specified
        if (!empty($playlist_id)) {
            // Support both playlist slug and playlist title
            $playlist_field = 'slug';

            // Check if it looks like a playlist ID (starts with PL)
            if (strpos($playlist_id, 'PL') === 0) {
                $playlist_field = 'name';
            }

            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $playlist_field,
                    'terms' => $playlist_id
                )
            );
        }

        // Default filter if no specific criteria
        if (empty($args['meta_query']) && empty($args['tax_query'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_video_id',
                'value' => '',
                'compare' => '!='
            );
        }

        $query = new WP_Query($args);
        $new_videos = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get playlist information for this video
                $playlists = wp_get_post_terms($post_id, 'youtube_playlist', array('fields' => 'names'));
                $playlist_names = is_array($playlists) ? $playlists : array();

                $new_videos[] = array(
                    'id' => get_post_meta($post_id, '_eyss_video_id', true),
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'thumbnail' => get_post_meta($post_id, '_eyss_thumbnail_url', true) ?: get_the_post_thumbnail_url($post_id, 'medium'),
                    'published_at' => get_post_meta($post_id, '_eyss_published_at', true) ?: get_the_date('Y-m-d H:i:s'),
                    'duration' => intval(get_post_meta($post_id, '_eyss_duration', true)),
                    'view_count' => intval(get_post_meta($post_id, '_eyss_view_count', true)),
                    'like_count' => intval(get_post_meta($post_id, '_eyss_like_count', true)),
                    'url' => get_post_meta($post_id, '_eyss_youtube_url', true),
                    'playlists' => $playlist_names,
                    'post_id' => $post_id
                );
            }
            wp_reset_postdata();
        }

        // Check if there are more videos
        $check_args = $args;
        $check_args['posts_per_page'] = 1;
        $check_args['offset'] = $offset + $count;
        $check_query = new WP_Query($check_args);
        $has_more = $check_query->have_posts();

        if (empty($new_videos)) {
            wp_send_json_error(__('No more videos available.', 'embed-youtube-shorts'));
        }

        // Generate HTML for new videos
        ob_start();
        foreach ($new_videos as $video) {
        ?>
            <div class="eyss-video-item"
                data-video-id="<?php echo esc_attr($video['id']); ?>"
                data-video-title="<?php echo esc_attr(strtolower($video['title'])); ?>">
                <div class="eyss-video-thumbnail">
                    <img src="<?php echo esc_url($video['thumbnail']); ?>"
                        alt="<?php echo esc_attr($video['title']); ?>"
                        loading="lazy">
                    <div class="eyss-play-button">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </div>
                    <div class="eyss-duration"><?php echo esc_html($this->format_duration($video['duration'])); ?></div>
                </div>

                <div class="eyss-video-info">
                    <h3 class="eyss-video-title"><?php echo esc_html($this->truncate_title($video['title'], 'grid')); ?></h3>
                    <div class="eyss-video-stats">
                        <span class="eyss-views"><?php echo esc_html($this->format_number($video['view_count'])); ?> views</span>
                        <span class="eyss-date"><?php echo esc_html(human_time_diff(strtotime($video['published_at']))); ?> ago</span>
                    </div>
                </div>

                <a href="<?php echo esc_url($video['url']); ?>"
                    class="eyss-video-link"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?php printf(__('Watch %s on YouTube', 'embed-youtube-shorts'), esc_attr($video['title'])); ?>">
                </a>
            </div>
        <?php
        }

        $html = ob_get_clean();

        // Get total count for this query (without pagination)
        $total_args = $args;
        $total_args['posts_per_page'] = -1;
        $total_args['fields'] = 'ids';
        unset($total_args['offset']);
        $total_query = new WP_Query($total_args);
        $total_count = $total_query->found_posts;

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $has_more,
            'total_count' => $total_count
        ));
    }

    /**
     * Search videos via AJAX
     */
    public function search_videos()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eyss_nonce')) {
            wp_die('Security check failed');
        }

        $search_term = sanitize_text_field($_POST['search'] ?? '');
        $channel_id = sanitize_text_field($_POST['channel_id'] ?? '');
        $playlist_id = sanitize_text_field($_POST['playlist_id'] ?? '');
        $playlists = sanitize_text_field($_POST['playlists'] ?? '');
        $exclude_playlist = sanitize_text_field($_POST['exclude_playlist'] ?? '');
        $layout = sanitize_text_field($_POST['layout'] ?? 'grid');
        $count = intval($_POST['count'] ?? 20);

        if (empty($search_term)) {
            wp_send_json_error(__('Search term is required.', 'embed-youtube-shorts'));
        }

        // Build search query
        $args = array(
            'post_type' => 'youtube_short',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            's' => $search_term,
            'orderby' => 'relevance',
            'order' => 'DESC',
            'meta_query' => array()
        );

        // Filter by channel if specified
        if (!empty($channel_id)) {
            $args['meta_query'][] = array(
                'key' => '_eyss_channel_id',
                'value' => $channel_id,
                'compare' => '='
            );
        }

        // Handle playlist filtering using taxonomy
        $tax_queries = array();

        // Single playlist filter
        if (!empty($playlist_id)) {
            $playlist_field = $this->determine_playlist_field($playlist_id);
            $tax_queries[] = array(
                'taxonomy' => 'youtube_playlist',
                'field' => $playlist_field,
                'terms' => $playlist_id
            );
        }

        // Multiple playlists filter (comma-separated)
        if (!empty($playlists)) {
            $playlist_list = array_map('trim', explode(',', $playlists));
            $playlist_terms = array();

            foreach ($playlist_list as $playlist) {
                if (!empty($playlist)) {
                    $playlist_terms[] = $playlist;
                }
            }

            if (!empty($playlist_terms)) {
                // For multiple playlists, we need to determine the field for each
                // For simplicity, try slug first, then name if slug fails
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => 'slug',
                    'terms' => $playlist_terms,
                    'operator' => 'IN'
                );
            }
        }

        // Exclude playlists filter
        if (!empty($exclude_playlist)) {
            $exclude_list = array_map('trim', explode(',', $exclude_playlist));
            $exclude_terms = array();

            foreach ($exclude_list as $playlist) {
                if (!empty($playlist)) {
                    $exclude_terms[] = $playlist;
                }
            }

            if (!empty($exclude_terms)) {
                $playlist_field = $this->determine_playlist_field($exclude_terms[0]);
                $tax_queries[] = array(
                    'taxonomy' => 'youtube_playlist',
                    'field' => $playlist_field,
                    'terms' => $exclude_terms,
                    'operator' => 'NOT IN'
                );
            }
        }

        // Add tax queries if any exist
        if (!empty($tax_queries)) {
            if (count($tax_queries) > 1) {
                $tax_queries['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_queries;
        }

        // Default filter to ensure we only get videos with video IDs
        if (empty($args['meta_query']) && empty($args['tax_query'])) {
            $args['meta_query'][] = array(
                'key' => '_eyss_video_id',
                'value' => '',
                'compare' => '!='
            );
        }

        $query = new WP_Query($args);
        $videos = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get playlist information for this video
                $playlists = wp_get_post_terms($post_id, 'youtube_playlist', array('fields' => 'names'));
                $playlist_names = is_array($playlists) ? $playlists : array();

                $video_data = array(
                    'id' => get_post_meta($post_id, '_eyss_video_id', true),
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'thumbnail' => get_post_meta($post_id, '_eyss_thumbnail_url', true) ?: get_the_post_thumbnail_url($post_id, 'medium'),
                    'published_at' => get_post_meta($post_id, '_eyss_published_at', true) ?: get_the_date('Y-m-d H:i:s'),
                    'duration' => intval(get_post_meta($post_id, '_eyss_duration', true)),
                    'view_count' => intval(get_post_meta($post_id, '_eyss_view_count', true)),
                    'like_count' => intval(get_post_meta($post_id, '_eyss_like_count', true)),
                    'url' => get_post_meta($post_id, '_eyss_youtube_url', true),
                    'playlists' => $playlist_names,
                    'post_id' => $post_id
                );

                $videos[] = $video_data;
            }
            wp_reset_postdata();
        }

        if (empty($videos)) {
            wp_send_json_success(array(
                'html' => '',
                'count' => 0,
                'message' => __('No videos found matching your search.', 'embed-youtube-shorts')
            ));
            return;
        }

        // Generate HTML for search results
        ob_start();
        foreach ($videos as $video) {
        ?>
            <div class="eyss-video-item"
                data-video-id="<?php echo esc_attr($video['id']); ?>"
                data-video-title="<?php echo esc_attr(strtolower($video['title'])); ?>">
                <div class="eyss-video-thumbnail">
                    <img src="<?php echo esc_url($video['thumbnail']); ?>"
                        alt="<?php echo esc_attr($video['title']); ?>"
                        loading="lazy">
                    <div class="eyss-play-button">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </div>
                    <div class="eyss-duration"><?php echo esc_html($this->format_duration($video['duration'])); ?></div>
                </div>

                <div class="eyss-video-info">
                    <h3 class="eyss-video-title"><?php echo esc_html($this->truncate_title($video['title'], $layout)); ?></h3>
                    <div class="eyss-video-stats">
                        <span class="eyss-views"><?php echo esc_html($this->format_number($video['view_count'])); ?> views</span>
                        <span class="eyss-date"><?php echo esc_html(human_time_diff(strtotime($video['published_at']))); ?> ago</span>
                    </div>
                </div>

                <a href="<?php echo esc_url($video['url']); ?>"
                    class="eyss-video-link"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="<?php printf(__('Watch %s on YouTube', 'embed-youtube-shorts'), esc_attr($video['title'])); ?>">
                </a>
            </div>
<?php
        }

        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'count' => count($videos)
        ));
    }
}
