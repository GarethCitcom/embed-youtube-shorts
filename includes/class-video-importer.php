<?php

/**
 * Video Import System for YouTube Shorts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EYSS_Video_Importer
{

    /**
     * YouTube API instance
     */
    private $youtube_api;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->youtube_api = new EYSS_YouTube_API();

        // Remove duplicate AJAX handlers - handled by admin settings class
        // add_action('wp_ajax_eyss_import_videos', array($this, 'ajax_import_videos'));
        // add_action('wp_ajax_eyss_get_import_progress', array($this, 'ajax_get_import_progress'));
        add_action('eyss_sync_videos_cron', array($this, 'scheduled_video_sync'));

        // Background import hook
        add_action('eyss_import_videos_bg', array($this, 'background_import_handler'), 10, 2);
    }

    /**
     * Import all videos from a channel
     */
    public function import_channel_videos($channel_id, $force_refresh = false)
    {
        if (empty($channel_id)) {
            return new WP_Error('missing_channel', __('Channel ID is required.', 'embed-youtube-shorts'));
        }

        // Set up progress tracking
        $progress_key = 'eyss_import_progress_' . $channel_id;
        update_option($progress_key, array(
            'status' => 'starting',
            'total_videos' => 0,
            'processed_videos' => 0,
            'imported_videos' => 0,
            'updated_videos' => 0,
            'skipped_videos' => 0,
            'errors' => array(),
            'start_time' => current_time('mysql'),
            'last_update' => current_time('mysql')
        ));

        try {
            // Step 1: Get uploads playlist ID
            update_option($progress_key, array_merge(get_option($progress_key), array(
                'status' => 'fetching_playlist',
                'last_update' => current_time('mysql')
            )));

            $uploads_playlist = $this->youtube_api->get_uploads_playlist_id($channel_id);
            if (is_wp_error($uploads_playlist)) {
                return $uploads_playlist;
            }

            // Step 2: Get all videos from the playlist
            update_option($progress_key, array_merge(get_option($progress_key), array(
                'status' => 'fetching_videos',
                'last_update' => current_time('mysql')
            )));

            $all_videos = $this->fetch_all_playlist_videos($uploads_playlist, $progress_key);
            if (is_wp_error($all_videos)) {
                return $all_videos;
            }

            // Update progress with total count
            $progress = get_option($progress_key);
            $progress['total_videos'] = count($all_videos);
            $progress['status'] = 'processing_videos';
            update_option($progress_key, $progress);

            // Step 3: Process videos in batches
            $batch_size = 50; // Process 50 videos at a time
            $video_chunks = array_chunk($all_videos, $batch_size);

            foreach ($video_chunks as $chunk_index => $video_chunk) {
                // Get video IDs from this chunk
                $video_ids = array();
                foreach ($video_chunk as $video) {
                    $video_ids[] = $video['contentDetails']['videoId'];
                }

                // Get detailed video information
                $video_details = $this->youtube_api->get_video_details($video_ids);
                if (is_wp_error($video_details)) {
                    continue; // Skip this batch if there's an error
                }

                // Process each video in the batch
                foreach ($video_details as $video_data) {
                    $result = $this->import_single_video($video_data, $channel_id, $force_refresh);

                    // Update progress
                    $progress = get_option($progress_key);
                    $progress['processed_videos']++;

                    if ($result === 'imported') {
                        $progress['imported_videos']++;
                    } elseif ($result === 'updated') {
                        $progress['updated_videos']++;
                    } elseif ($result === 'skipped') {
                        $progress['skipped_videos']++;
                    } elseif (is_wp_error($result)) {
                        $progress['errors'][] = $result->get_error_message();
                    }

                    $progress['last_update'] = current_time('mysql');
                    update_option($progress_key, $progress);
                }

                // Small delay between batches to prevent overwhelming the server
                usleep(100000); // 0.1 second delay
            }

            // Mark as completed
            $final_progress = get_option($progress_key);
            $final_progress['status'] = 'completed';
            $final_progress['end_time'] = current_time('mysql');
            update_option($progress_key, $final_progress);

            return $final_progress;
        } catch (Exception $e) {
            // Mark as failed
            $error_progress = get_option($progress_key);
            $error_progress['status'] = 'failed';
            $error_progress['error_message'] = $e->getMessage();
            $error_progress['end_time'] = current_time('mysql');
            update_option($progress_key, $error_progress);

            return new WP_Error('import_failed', $e->getMessage());
        }
    }

    /**
     * Fetch all videos from a playlist using pagination
     */
    private function fetch_all_playlist_videos($playlist_id, $progress_key)
    {
        $url = 'https://www.googleapis.com/youtube/v3/playlistItems';
        $all_videos = array();
        $next_page_token = '';
        $page_count = 0;
        $max_pages = 500; // Safety limit

        do {
            $params = array(
                'part' => 'contentDetails',
                'playlistId' => $playlist_id,
                'maxResults' => 50,
                'key' => $this->youtube_api->get_api_key(),
            );

            if ($next_page_token) {
                $params['pageToken'] = $next_page_token;
            }

            $response = wp_remote_get(add_query_arg($params, $url));

            if (is_wp_error($response)) {
                return $response;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['error'])) {
                return new WP_Error('api_error', $data['error']['message']);
            }

            if (!empty($data['items'])) {
                $all_videos = array_merge($all_videos, $data['items']);
            }

            $next_page_token = $data['nextPageToken'] ?? '';
            $page_count++;

            // Update progress
            $progress = get_option($progress_key);
            $progress['status'] = 'fetching_videos_page_' . $page_count;
            $progress['fetched_videos'] = count($all_videos);
            $progress['last_update'] = current_time('mysql');
            update_option($progress_key, $progress);
        } while ($next_page_token && $page_count < $max_pages);

        return $all_videos;
    }

    /**
     * Import or update a single video
     */
    public function import_single_video($video_data, $channel_id, $force_refresh = false)
    {
        $video_id = $video_data['id'];

        // Parse duration to check if it's a short
        $duration_string = $video_data['contentDetails']['duration'] ?? '';
        $duration = $this->youtube_api->parse_duration($duration_string);

        // Only import shorts (videos <= 180 seconds)
        if ($duration > $this->youtube_api->get_max_short_duration()) {
            return 'skipped'; // Not a short
        }

        // Check if video already exists
        $existing_posts = get_posts(array(
            'post_type' => 'youtube_short',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to check for existing videos by YouTube ID to prevent duplicates during import, essential for video import functionality
            'meta_query' => array(
                array(
                    'key' => '_eyss_video_id',
                    'value' => $video_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1,
            'post_status' => array('publish', 'draft', 'private')
        ));

        $post_id = null;
        $is_update = false;

        if (!empty($existing_posts) && !$force_refresh) {
            return 'skipped'; // Already exists and not forcing refresh
        } elseif (!empty($existing_posts)) {
            $post_id = $existing_posts[0]->ID;
            $is_update = true;
        }

        // Prepare post data
        $post_data = array(
            'post_type' => 'youtube_short',
            'post_title' => sanitize_text_field($video_data['snippet']['title']),
            'post_content' => wp_kses_post($video_data['snippet']['description']),
            'post_excerpt' => wp_trim_words($video_data['snippet']['description'], 55),
            'post_status' => 'publish',
            'post_date' => gmdate('Y-m-d H:i:s', strtotime($video_data['snippet']['publishedAt'])),
        );

        if ($is_update) {
            $post_data['ID'] = $post_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save video metadata
        $meta_data = array(
            '_eyss_video_id' => $video_id,
            '_eyss_youtube_url' => 'https://www.youtube.com/watch?v=' . $video_id,
            '_eyss_duration' => $duration,
            '_eyss_channel_id' => $channel_id,
            '_eyss_channel_title' => $video_data['snippet']['channelTitle'] ?? '',
            '_eyss_published_at' => gmdate('Y-m-d\TH:i', strtotime($video_data['snippet']['publishedAt'])),
            '_eyss_thumbnail_url' => $video_data['snippet']['thumbnails']['high']['url'] ?? $video_data['snippet']['thumbnails']['default']['url'] ?? '',
            '_eyss_view_count' => $video_data['statistics']['viewCount'] ?? 0,
            '_eyss_like_count' => $video_data['statistics']['likeCount'] ?? 0,
            '_eyss_comment_count' => $video_data['statistics']['commentCount'] ?? 0,
            '_eyss_last_updated' => current_time('mysql')
        );

        foreach ($meta_data as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value);
        }

        // Set featured image from thumbnail
        $this->set_featured_image_from_url($post_id, $meta_data['_eyss_thumbnail_url']);

        // Detect and assign playlists
        $this->assign_video_playlists($post_id, $video_id, $channel_id);

        return $is_update ? 'updated' : 'imported';
    }

    /**
     * Set featured image from URL
     */
    private function set_featured_image_from_url($post_id, $image_url)
    {
        if (empty($image_url)) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $image_id = media_sideload_image($image_url, $post_id, '', 'id');

        if (!is_wp_error($image_id)) {
            set_post_thumbnail($post_id, $image_id);
            return true;
        }

        return false;
    }

    /**
     * Assign video to YouTube playlists (taxonomy terms)
     */
    private function assign_video_playlists($post_id, $video_id, $channel_id)
    {
        try {
            // Get playlist membership for this video
            $video_playlists = $this->youtube_api->get_video_playlist_membership($video_id, $channel_id);

            if (is_wp_error($video_playlists)) {
                return;
            }

            $term_ids = array();

            foreach ($video_playlists as $playlist_data) {
                $playlist_id = $playlist_data['id'];
                $playlist_title = $playlist_data['title'];

                // Check if term already exists
                $existing_term = get_term_by('slug', $playlist_id, 'youtube_playlist');

                if ($existing_term) {
                    $term_ids[] = $existing_term->term_id;
                } else {
                    // Create new term
                    $term_result = wp_insert_term(
                        $playlist_title,
                        'youtube_playlist',
                        array(
                            'slug' => $playlist_id,
                            'description' => $playlist_data['description'] ?? ''
                        )
                    );

                    if (!is_wp_error($term_result)) {
                        $term_ids[] = $term_result['term_id'];

                        // Store additional playlist metadata
                        update_term_meta($term_result['term_id'], '_eyss_playlist_id', $playlist_id);
                        update_term_meta($term_result['term_id'], '_eyss_playlist_thumbnail', $playlist_data['thumbnail'] ?? '');
                    }
                }
            }

            // Assign terms to post
            if (!empty($term_ids)) {
                wp_set_post_terms($post_id, $term_ids, 'youtube_playlist');
            }
        } catch (Exception $e) {
            // Error occurred during playlist assignment
        }
    }

    /**
     * Import all playlists from channel as taxonomy terms
     */
    private function import_channel_playlists($channel_id)
    {
        $playlists = $this->youtube_api->get_all_channel_playlists($channel_id);

        if (is_wp_error($playlists)) {
            return 0;
        }

        $imported_count = 0;

        foreach ($playlists as $playlist) {
            $playlist_id = $playlist['id'];
            $playlist_title = $playlist['snippet']['title'];

            // Skip uploads playlist (we handle that separately)
            if (strpos($playlist_id, 'UU') === 0) {
                continue;
            }

            // Check if term already exists
            $existing_term = get_term_by('slug', $playlist_id, 'youtube_playlist');

            if (!$existing_term) {
                // Create new term
                $term_result = wp_insert_term(
                    $playlist_title,
                    'youtube_playlist',
                    array(
                        'slug' => $playlist_id,
                        'description' => $playlist['snippet']['description'] ?? ''
                    )
                );

                if (!is_wp_error($term_result)) {
                    // Store additional playlist metadata
                    update_term_meta($term_result['term_id'], '_eyss_playlist_id', $playlist_id);
                    update_term_meta($term_result['term_id'], '_eyss_playlist_thumbnail', $playlist['snippet']['thumbnails']['default']['url'] ?? '');
                    update_term_meta($term_result['term_id'], '_eyss_video_count', $playlist['contentDetails']['itemCount'] ?? 0);

                    $imported_count++;
                }
            }
        }

        return $imported_count;
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
        $channel_id = $settings['channel_id'] ?? '';
        $force_refresh = isset($_POST['force_refresh']) && $_POST['force_refresh'] === 'true';

        if (empty($channel_id)) {
            wp_send_json_error(__('Channel ID not configured. Please set it in settings first.', 'embed-youtube-shorts'));
        }

        // Simple, direct import - no chunking, just process what we can
        try {
            $result = $this->simple_import_process($channel_id, $force_refresh);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            } else {
                wp_send_json_success($result);
            }
        } catch (Exception $e) {
            wp_send_json_error('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Simple, direct import process
     */
    public function simple_import_process($channel_id, $force_refresh = false)
    {

        // Step 1: Import all playlists first
        $playlists_imported = $this->import_channel_playlists($channel_id);

        // Step 2: Get uploads playlist
        $uploads_playlist = $this->youtube_api->get_uploads_playlist_id($channel_id);
        if (is_wp_error($uploads_playlist)) {
            return $uploads_playlist;
        }

        // Step 2: Get recent videos (limit to 200 to avoid timeout)
        $videos = $this->youtube_api->get_playlist_videos($uploads_playlist, 200);
        if (is_wp_error($videos)) {
            return $videos;
        }

        // Step 3: Extract video IDs
        $video_ids = array();
        foreach ($videos as $video) {
            $video_ids[] = $video['contentDetails']['videoId'];
        }

        if (empty($video_ids)) {
            return array(
                'message' => 'No videos found in channel',
                'imported' => 0,
                'processed' => 0,
                'skipped' => 0
            );
        }

        // Step 4: Get video details (process in chunks of 50 - API limit)
        $all_video_details = array();
        $video_chunks = array_chunk($video_ids, 50);

        foreach ($video_chunks as $chunk) {
            $details = $this->youtube_api->get_video_details($chunk);
            if (!is_wp_error($details)) {
                $all_video_details = array_merge($all_video_details, $details);
            }
        }

        // Step 5: Process videos and import shorts
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;

        foreach ($all_video_details as $video_data) {
            $processed++;
            $result = $this->import_single_video($video_data, $channel_id, $force_refresh);

            if ($result === 'imported') {
                $imported++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }

            // Log progress every 10 videos
        }

        $message = sprintf(
            'Import completed! Processed %d videos, imported %d new shorts, updated %d, skipped %d.',
            $processed,
            $imported,
            $updated,
            $skipped
        );

        return array(
            'message' => $message,
            'processed' => $processed,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped
        );
    }

    /**
     * Background import handler
     */
    public function background_import_handler($channel_id, $force_refresh = false)
    {
        // Call the main import function
        $result = $this->import_channel_videos($channel_id, $force_refresh);
    }

    /**
     * Process a single chunk of videos for chunked imports
     */
    public function process_single_chunk($channel_id, $force_refresh = false)
    {
        $progress_key = 'eyss_import_progress_' . $channel_id;
        $progress = get_option($progress_key, array());

        // Initialize progress if not exists
        if (empty($progress)) {
            $progress = array(
                'status' => 'initializing',
                'total_videos' => 0,
                'processed_videos' => 0,
                'imported_videos' => 0,
                'updated_videos' => 0,
                'skipped_videos' => 0,
                'errors' => array(),
                'current_page' => 1,
                'videos_per_chunk' => 20,
                'start_time' => current_time('mysql'),
                'last_update' => current_time('mysql')
            );
            update_option($progress_key, $progress);
        }

        try {
            // Get uploads playlist ID if not cached
            if (!isset($progress['uploads_playlist'])) {
                $uploads_playlist = $this->youtube_api->get_uploads_playlist_id($channel_id);
                if (is_wp_error($uploads_playlist)) {
                    throw new Exception($uploads_playlist->get_error_message());
                }
                $progress['uploads_playlist'] = $uploads_playlist;
                $progress['status'] = 'fetching_videos';
                update_option($progress_key, $progress);
            }

            // Fetch a chunk of videos (20 per chunk to avoid timeout)
            $chunk_size = 20;
            $page_token = $progress['next_page_token'] ?? '';

            $chunk_videos = $this->fetch_video_chunk($progress['uploads_playlist'], $chunk_size, $page_token);

            if (is_wp_error($chunk_videos)) {
                throw new Exception($chunk_videos->get_error_message());
            }

            $video_ids = array();
            foreach ($chunk_videos['videos'] as $video) {
                $video_ids[] = $video['contentDetails']['videoId'];
            }

            if (!empty($video_ids)) {
                // Get video details
                $video_details = $this->youtube_api->get_video_details($video_ids);
                if (!is_wp_error($video_details)) {
                    // Process each video
                    foreach ($video_details as $video_data) {
                        $result = $this->import_single_video($video_data, $channel_id, $force_refresh);

                        $progress['processed_videos']++;
                        if ($result === 'imported') {
                            $progress['imported_videos']++;
                        } elseif ($result === 'updated') {
                            $progress['updated_videos']++;
                        } elseif ($result === 'skipped') {
                            $progress['skipped_videos']++;
                        } elseif (is_wp_error($result)) {
                            $progress['errors'][] = $result->get_error_message();
                        }
                    }
                }
            }

            // Update progress
            $progress['next_page_token'] = $chunk_videos['next_page_token'] ?? '';
            $progress['last_update'] = current_time('mysql');

            if (empty($progress['next_page_token'])) {
                $progress['status'] = 'completed';
                $progress['end_time'] = current_time('mysql');
            } else {
                $progress['status'] = 'processing_chunk_' . $progress['current_page'];
                $progress['current_page']++;
            }

            update_option($progress_key, $progress);

            return array(
                'status' => $progress['status'],
                'processed' => $progress['processed_videos'],
                'imported' => $progress['imported_videos'],
                'has_more' => !empty($progress['next_page_token']),
                'message' => sprintf('Processed %d videos, imported %d shorts', $progress['processed_videos'], $progress['imported_videos'])
            );
        } catch (Exception $e) {
            $progress['status'] = 'error';
            $progress['error_message'] = $e->getMessage();
            $progress['end_time'] = current_time('mysql');
            update_option($progress_key, $progress);

            return array(
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Fetch a chunk of videos from playlist
     */
    private function fetch_video_chunk($playlist_id, $max_results = 20, $page_token = '')
    {
        $url = 'https://www.googleapis.com/youtube/v3/playlistItems';
        $params = array(
            'part' => 'contentDetails',
            'playlistId' => $playlist_id,
            'maxResults' => min($max_results, 50), // API limit is 50
            'key' => $this->youtube_api->get_api_key(),
        );

        if ($page_token) {
            $params['pageToken'] = $page_token;
        }

        $response = wp_remote_get(add_query_arg($params, $url));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }

        return array(
            'videos' => $data['items'] ?? array(),
            'next_page_token' => $data['nextPageToken'] ?? ''
        );
    }

    /**
     * AJAX handler for getting import progress
     */
    public function ajax_get_import_progress()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'embed-youtube-shorts'));
        }

        // Verify nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'eyss_nonce')) {
            wp_send_json_error(__('Security check failed.', 'embed-youtube-shorts'));
        }

        $progress_key = isset($_GET['progress_key']) ? sanitize_text_field(wp_unslash($_GET['progress_key'])) : '';

        if (empty($progress_key)) {
            wp_send_json_error(__('Progress key is required.', 'embed-youtube-shorts'));
        }

        if (empty($progress_key)) {
            wp_send_json_error(__('Progress key is required.', 'embed-youtube-shorts'));
        }

        $progress = get_option($progress_key, array());

        if (empty($progress)) {
            wp_send_json_error(__('No import progress found.', 'embed-youtube-shorts'));
        }

        wp_send_json_success($progress);
    }

    /**
     * Scheduled video sync (for keeping videos up to date)
     */
    public function scheduled_video_sync()
    {
        $settings = get_option('eyss_settings', array());
        $channel_id = $settings['channel_id'] ?? '';

        if (!empty($channel_id)) {
            // Only sync videos published in the last 30 days
            $this->sync_recent_videos($channel_id, 30);
        }
    }

    /**
     * Sync recent videos (lighter sync for scheduled updates)
     */
    public function sync_recent_videos($channel_id, $days_back = 30)
    {
        // This is a lighter version that only checks recent videos
        // Implementation would be similar but with date filtering
        // Left as a stub for now
    }
}
