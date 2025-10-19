<?php

/**
 * Custom Post Type for YouTube Shorts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EYSS_Post_Type
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register post type and taxonomies early in init
        add_action('init', array($this, 'register_post_type'), 0);
        add_action('init', array($this, 'register_playlist_taxonomy'), 5);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('manage_youtube_short_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_youtube_short_posts_custom_column', array($this, 'custom_column_content'), 10, 2);

        // Add debug hook to verify registration
        add_action('init', array($this, 'debug_post_type'), 999);
    }
    /**
     * Register YouTube Shorts custom post type
     */
    public function register_post_type()
    {
        // Check if already registered
        if (post_type_exists('youtube_short')) {
            return;
        }

        $args = array(
            'label'              => 'YouTube Shorts',
            'labels'             => array(
                'name'                  => 'YouTube Shorts',
                'singular_name'         => 'YouTube Short',
                'menu_name'             => 'YouTube Shorts',
                'all_items'             => 'All YouTube Shorts',
                'add_new'               => 'Add New',
                'add_new_item'          => 'Add New YouTube Short',
                'edit_item'             => 'Edit YouTube Short',
                'view_item'             => 'View YouTube Short',
                'search_items'          => 'Search YouTube Shorts',
                'not_found'             => 'No YouTube Shorts found',
                'not_found_in_trash'    => 'No YouTube Shorts found in Trash'
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'youtube-shorts'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-video-alt3',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true
        );

        $result = register_post_type('youtube_short', $args);

        if (is_wp_error($result)) {
            return false;
        }

        return true;
    }

    /**
     * Register YouTube Playlists custom taxonomy
     */
    public function register_playlist_taxonomy()
    {
        // Check if already registered
        if (taxonomy_exists('youtube_playlist')) {
            return;
        }

        $labels = array(
            'name'                       => 'YouTube Playlists',
            'singular_name'              => 'YouTube Playlist',
            'menu_name'                  => 'YouTube Playlists',
            'all_items'                  => 'All Playlists',
            'parent_item'                => 'Parent Playlist',
            'parent_item_colon'          => 'Parent Playlist:',
            'new_item_name'              => 'New Playlist Name',
            'add_new_item'               => 'Add New Playlist',
            'edit_item'                  => 'Edit Playlist',
            'update_item'                => 'Update Playlist',
            'view_item'                  => 'View Playlist',
            'separate_items_with_commas' => 'Separate playlists with commas',
            'add_or_remove_items'        => 'Add or remove playlists',
            'choose_from_most_used'      => 'Choose from the most used',
            'popular_items'              => 'Popular Playlists',
            'search_items'               => 'Search Playlists',
            'not_found'                  => 'Not Found',
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'youtube-playlists',
            'query_var'                  => 'youtube_playlist',
            'rewrite'                    => array('slug' => 'youtube-playlist'),
        );

        $result = register_taxonomy('youtube_playlist', array('youtube_short'), $args);

        if (is_wp_error($result)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Debug method to verify post type registration
     */
    public function debug_post_type()
    {
        if (post_type_exists('youtube_short')) {
            // Post type exists and is registered
        } else {
            // Try manual registration as fallback
            $this->manual_register_fallback();
        }

        // Debug taxonomy registration
        if (taxonomy_exists('youtube_playlist')) {
            $tax_obj = get_taxonomy('youtube_playlist');
        } else {
            // Try to register taxonomy now
            $this->register_playlist_taxonomy();
        }

        // Check registered post types
        $post_types = get_post_types(array(), 'names');
        if (!in_array('youtube_short', $post_types)) {
            // Post type not found in list
        }
    }

    /**
     * Manual fallback registration
     */
    public function manual_register_fallback()
    {
        register_post_type('youtube_short', array(
            'labels' => array(
                'name' => 'YouTube Shorts',
                'singular_name' => 'YouTube Short'
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-video-alt3',
            'supports' => array('title', 'editor', 'thumbnail')
        ));
    }

    /**
     * Flush rewrite rules to ensure post type URLs work
     */
    public function flush_rewrite_rules()
    {
        $this->register_post_type();
        flush_rewrite_rules();
    }

    /**
     * Add meta boxes for YouTube Short data
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'eyss_video_details',
            __('Video Details', 'embed-youtube-shorts'),
            array($this, 'video_details_meta_box'),
            'youtube_short',
            'normal',
            'high'
        );

        add_meta_box(
            'eyss_video_stats',
            __('Video Statistics', 'embed-youtube-shorts'),
            array($this, 'video_stats_meta_box'),
            'youtube_short',
            'side',
            'default'
        );
    }

    /**
     * Video details meta box content
     */
    public function video_details_meta_box($post)
    {
        wp_nonce_field('eyss_save_meta_box', 'eyss_meta_box_nonce');

        $video_id = get_post_meta($post->ID, '_eyss_video_id', true);
        $youtube_url = get_post_meta($post->ID, '_eyss_youtube_url', true);
        $duration = get_post_meta($post->ID, '_eyss_duration', true);
        $channel_id = get_post_meta($post->ID, '_eyss_channel_id', true);
        $channel_title = get_post_meta($post->ID, '_eyss_channel_title', true);
        $published_at = get_post_meta($post->ID, '_eyss_published_at', true);
        $thumbnail_url = get_post_meta($post->ID, '_eyss_thumbnail_url', true);
?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="eyss_video_id"><?php esc_html_e('YouTube Video ID', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="text" id="eyss_video_id" name="eyss_video_id" value="<?php echo esc_attr($video_id); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('The unique YouTube video identifier.', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_youtube_url"><?php esc_html_e('YouTube URL', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="url" id="eyss_youtube_url" name="eyss_youtube_url" value="<?php echo esc_url($youtube_url); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Full YouTube video URL.', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_duration"><?php esc_html_e('Duration (seconds)', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="number" id="eyss_duration" name="eyss_duration" value="<?php echo esc_attr($duration); ?>" min="0" max="180" />
                    <p class="description"><?php esc_html_e('Video duration in seconds (max 180 for Shorts).', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_channel_id"><?php esc_html_e('Channel ID', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="text" id="eyss_channel_id" name="eyss_channel_id" value="<?php echo esc_attr($channel_id); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('YouTube channel identifier.', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_channel_title"><?php esc_html_e('Channel Title', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="text" id="eyss_channel_title" name="eyss_channel_title" value="<?php echo esc_attr($channel_title); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('YouTube channel name.', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_published_at"><?php esc_html_e('Published Date', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="eyss_published_at" name="eyss_published_at" value="<?php echo esc_attr($published_at); ?>" />
                    <p class="description"><?php esc_html_e('When the video was published on YouTube.', 'embed-youtube-shorts'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eyss_thumbnail_url"><?php esc_html_e('Thumbnail URL', 'embed-youtube-shorts'); ?></label>
                </th>
                <td>
                    <input type="url" id="eyss_thumbnail_url" name="eyss_thumbnail_url" value="<?php echo esc_url($thumbnail_url); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('URL of the video thumbnail image.', 'embed-youtube-shorts'); ?></p>
                    <?php if ($thumbnail_url): ?>
                        <br><img src="<?php echo esc_url($thumbnail_url); ?>" style="max-width: 200px; margin-top: 10px;" alt="<?php esc_attr_e('Video thumbnail', 'embed-youtube-shorts'); ?>">
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php
    }

    /**
     * Video statistics meta box content
     */
    public function video_stats_meta_box($post)
    {
        $view_count = get_post_meta($post->ID, '_eyss_view_count', true);
        $like_count = get_post_meta($post->ID, '_eyss_like_count', true);
        $comment_count = get_post_meta($post->ID, '_eyss_comment_count', true);
        $last_updated = get_post_meta($post->ID, '_eyss_last_updated', true);
    ?>
        <p>
            <strong><?php esc_html_e('View Count:', 'embed-youtube-shorts'); ?></strong><br>
            <input type="number" name="eyss_view_count" value="<?php echo esc_attr($view_count); ?>" min="0" style="width: 100%;" />
        </p>
        <p>
            <strong><?php esc_html_e('Like Count:', 'embed-youtube-shorts'); ?></strong><br>
            <input type="number" name="eyss_like_count" value="<?php echo esc_attr($like_count); ?>" min="0" style="width: 100%;" />
        </p>
        <p>
            <strong><?php esc_html_e('Comment Count:', 'embed-youtube-shorts'); ?></strong><br>
            <input type="number" name="eyss_comment_count" value="<?php echo esc_attr($comment_count); ?>" min="0" style="width: 100%;" />
        </p>
        <p>
            <strong><?php esc_html_e('Last Updated:', 'embed-youtube-shorts'); ?></strong><br>
            <em><?php echo $last_updated ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))) : esc_html__('Never', 'embed-youtube-shorts'); ?></em>
        </p>
<?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id)
    {
        if (!isset($_POST['eyss_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eyss_meta_box_nonce'])), 'eyss_save_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'youtube_short') {
            return;
        }

        // Save video details
        $fields = array(
            'eyss_video_id',
            'eyss_youtube_url',
            'eyss_duration',
            'eyss_channel_id',
            'eyss_channel_title',
            'eyss_published_at',
            'eyss_thumbnail_url',
            'eyss_view_count',
            'eyss_like_count',
            'eyss_comment_count'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field(wp_unslash($_POST[$field])));
            }
        }

        // Update last modified timestamp
        update_post_meta($post_id, '_eyss_last_updated', current_time('mysql'));
    }

    /**
     * Add custom columns to admin list
     */
    public function add_custom_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['eyss_thumbnail'] = __('Thumbnail', 'embed-youtube-shorts');
                $new_columns['eyss_video_id'] = __('Video ID', 'embed-youtube-shorts');
                $new_columns['eyss_duration'] = __('Duration', 'embed-youtube-shorts');
                $new_columns['eyss_playlists'] = __('Playlists', 'embed-youtube-shorts');
                $new_columns['eyss_stats'] = __('Stats', 'embed-youtube-shorts');
            }
        }
        return $new_columns;
    }

    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id)
    {
        switch ($column) {
            case 'eyss_thumbnail':
                $thumbnail_url = get_post_meta($post_id, '_eyss_thumbnail_url', true);
                if ($thumbnail_url) {
                    echo '<img src="' . esc_url($thumbnail_url) . '" style="width: 60px; height: auto;" alt="' . esc_attr__('Video thumbnail', 'embed-youtube-shorts') . '">';
                } else {
                    echo '—';
                }
                break;

            case 'eyss_video_id':
                $video_id = get_post_meta($post_id, '_eyss_video_id', true);
                if ($video_id) {
                    echo '<code>' . esc_html($video_id) . '</code><br>';
                    echo '<a href="https://www.youtube.com/watch?v=' . esc_attr($video_id) . '" target="_blank">' . esc_html__('View on YouTube', 'embed-youtube-shorts') . '</a>';
                } else {
                    echo '—';
                }
                break;

            case 'eyss_duration':
                $duration = get_post_meta($post_id, '_eyss_duration', true);
                if ($duration) {
                    $minutes = floor($duration / 60);
                    $seconds = $duration % 60;
                    echo sprintf('%d:%02d', esc_html($minutes), esc_html($seconds));
                } else {
                    echo '—';
                }
                break;

            case 'eyss_playlists':
                $playlists = wp_get_post_terms($post_id, 'youtube_playlist');
                if (!empty($playlists) && !is_wp_error($playlists)) {
                    $playlist_names = array();
                    foreach ($playlists as $playlist) {
                        $playlist_names[] = '<span style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 11px;">' . esc_html($playlist->name) . '</span>';
                    }
                    echo wp_kses(implode(' ', $playlist_names), array(
                        'span' => array(
                            'style' => true
                        )
                    ));
                } else {
                    echo '<span style="color: #666; font-style: italic;">' . esc_html__('No playlists', 'embed-youtube-shorts') . '</span>';
                }
                break;

            case 'eyss_stats':
                $views = get_post_meta($post_id, '_eyss_view_count', true);
                $likes = get_post_meta($post_id, '_eyss_like_count', true);
                if ($views) {
                    echo '<strong>' . esc_html(number_format($views)) . '</strong> ' . esc_html__('views', 'embed-youtube-shorts') . '<br>';
                }
                if ($likes) {
                    echo '<strong>' . esc_html(number_format($likes)) . '</strong> ' . esc_html__('likes', 'embed-youtube-shorts');
                }
                if (!$views && !$likes) {
                    echo '—';
                }
                break;
        }
    }
}
