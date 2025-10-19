<?php

/**
 * YouTube API Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EYSS_YouTube_API
{

    /**
     * YouTube API base URL
     */
    private $api_base_url = 'https://www.googleapis.com/youtube/v3/';

    /**
     * Plugin settings
     */
    private $settings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->settings = get_option('eyss_settings', array());
    }

    /**
     * Test API connection
     */
    public function test_connection($api_key = null, $channel_id = null)
    {
        $api_key = $api_key ?: $this->get_api_key();
        $channel_id = $channel_id ?: $this->get_channel_id();

        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is required.', 'embed-youtube-shorts')
            );
        }

        $url = $this->api_base_url . 'channels';
        $params = array(
            'part' => 'id,snippet',
            'key' => $api_key,
        );

        if (!empty($channel_id)) {
            $params['id'] = $channel_id;
        } else {
            $params['mine'] = 'true';
        }

        $response = $this->make_request($url, $params);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                // translators: %s is the error message from the API
                'message' => sprintf(__('API Error: %s', 'embed-youtube-shorts'), $response->get_error_message())
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return array(
                'success' => false,
                // translators: %s is the error message from YouTube API
                'message' => sprintf(__('YouTube API Error: %s', 'embed-youtube-shorts'), $data['error']['message'])
            );
        }

        if (empty($data['items'])) {
            return array(
                'success' => false,
                'message' => __('Channel not found or invalid Channel ID.', 'embed-youtube-shorts')
            );
        }

        $channel = $data['items'][0];
        return array(
            'success' => true,
            // translators: %s is the YouTube channel title/name
            'message' => sprintf(__('Connection successful! Channel: %s', 'embed-youtube-shorts'), $channel['snippet']['title'])
        );
    }

    /**
     * Get channel playlists
     */
    public function get_channel_playlists($channel_id = null)
    {
        $channel_id = $channel_id ?: $this->get_channel_id();
        $api_key = $this->get_api_key();

        if (empty($api_key) || empty($channel_id)) {
            return new WP_Error('missing_credentials', __('API key and Channel ID are required.', 'embed-youtube-shorts'));
        }

        // Check cache first
        $cache_key = 'eyss_playlists_' . md5($channel_id);
        $cached_data = $this->get_cached_data($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $url = $this->api_base_url . 'playlists';
        $params = array(
            'part' => 'id,snippet,contentDetails',
            'channelId' => $channel_id,
            'maxResults' => 50,
            'key' => $api_key,
        );

        $response = $this->make_request($url, $params);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }

        // Cache the result
        $this->set_cached_data($cache_key, $data['items'], $this->get_cache_duration());

        return $data['items'];
    }

    /**
     * Debug method to get detailed information about channel videos
     */
    public function debug_channel_videos($channel_id = null, $max_results = 10)
    {
        $channel_id = $channel_id ?: $this->get_channel_id();
        $api_key = $this->get_api_key();

        if (empty($api_key) || empty($channel_id)) {
            return array('error' => 'API key and Channel ID are required.');
        }

        // Get the uploads playlist ID
        $uploads_playlist = $this->get_uploads_playlist_id($channel_id);
        if (is_wp_error($uploads_playlist)) {
            return array('error' => 'Failed to get uploads playlist: ' . $uploads_playlist->get_error_message());
        }

        // Get videos from uploads playlist
        $playlist_videos = $this->get_playlist_videos($uploads_playlist, $max_results);
        if (is_wp_error($playlist_videos)) {
            return array('error' => 'Failed to get playlist videos: ' . $playlist_videos->get_error_message());
        }

        // Get video details
        $video_ids = array();
        foreach ($playlist_videos as $video) {
            $video_ids[] = $video['contentDetails']['videoId'];
        }

        if (empty($video_ids)) {
            return array('error' => 'No videos found in playlist');
        }

        $video_details = $this->get_video_details($video_ids);
        if (is_wp_error($video_details)) {
            return array('error' => 'Failed to get video details: ' . $video_details->get_error_message());
        }

        // Analyze videos
        $analysis = array();
        $shorts_count = 0;

        foreach ($video_details as $video) {
            $duration_string = $video['contentDetails']['duration'] ?? '';
            $duration = $this->parse_duration($duration_string);
            $is_short = $duration <= $this->get_max_short_duration();

            if ($is_short) {
                $shorts_count++;
            }

            $analysis[] = array(
                'id' => $video['id'],
                'title' => $video['snippet']['title'] ?? 'No title',
                'duration_string' => $duration_string,
                'duration_seconds' => $duration,
                'is_short' => $is_short,
                'published_at' => $video['snippet']['publishedAt'] ?? 'Unknown'
            );
        }

        return array(
            'channel_id' => $channel_id,
            'uploads_playlist' => $uploads_playlist,
            'total_videos_checked' => count($video_details),
            'shorts_found' => $shorts_count,
            'videos' => $analysis
        );
    }

    /**
     * Get videos from channel (including Shorts)
     */
    public function get_channel_shorts($channel_id = null, $max_results = 50)
    {
        $channel_id = $channel_id ?: $this->get_channel_id();
        $api_key = $this->get_api_key();

        if (empty($api_key) || empty($channel_id)) {
            return new WP_Error('missing_credentials', __('API key and Channel ID are required.', 'embed-youtube-shorts'));
        }

        // Check cache first
        $cache_key = 'eyss_shorts_' . md5($channel_id . '_' . $max_results);
        $cached_data = $this->get_cached_data($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        // Step 1: Get the uploads playlist ID
        $uploads_playlist = $this->get_uploads_playlist_id($channel_id);
        if (is_wp_error($uploads_playlist)) {
            return $uploads_playlist;
        }

        // Step 2: Get videos from uploads playlist (fetch more to account for filtering)
        $fetch_count = max($max_results * 5, 200); // Fetch 5x more or 200, whichever is higher
        $playlist_videos = $this->get_playlist_videos($uploads_playlist, $fetch_count);
        if (is_wp_error($playlist_videos)) {
            return $playlist_videos;
        }

        // Step 3: Get video details to identify Shorts
        $video_ids = array();
        foreach ($playlist_videos as $video) {
            $video_ids[] = $video['contentDetails']['videoId'];
        }

        if (empty($video_ids)) {
            return array();
        }

        $video_details = $this->get_video_details($video_ids);
        if (is_wp_error($video_details)) {
            return $video_details;
        }

        // Step 4: Filter for Shorts (videos up to configured max duration)
        $shorts = array();
        $debug_info = array(); // For debugging

        foreach ($video_details as $video) {
            try {
                $duration_string = $video['contentDetails']['duration'] ?? '';
                $duration = $this->parse_duration($duration_string);

                // Debug info
                $debug_info[] = array(
                    'title' => $video['snippet']['title'] ?? 'No title',
                    'duration_string' => $duration_string,
                    'duration_seconds' => $duration,
                    'is_short' => $duration <= $this->get_max_short_duration()
                );

                // YouTube Shorts can be up to configured max duration
                if ($duration <= $this->get_max_short_duration()) {
                    $shorts[] = array(
                        'id' => $video['id'],
                        'title' => $video['snippet']['title'],
                        'description' => $video['snippet']['description'],
                        'thumbnail' => $video['snippet']['thumbnails']['high']['url'] ?? $video['snippet']['thumbnails']['default']['url'],
                        'published_at' => $video['snippet']['publishedAt'],
                        'duration' => $duration,
                        'view_count' => $video['statistics']['viewCount'] ?? 0,
                        'like_count' => $video['statistics']['likeCount'] ?? 0,
                        'url' => 'https://www.youtube.com/watch?v=' . $video['id']
                    );

                    if (count($shorts) >= $max_results) {
                        break;
                    }
                }
            } catch (Exception $e) {
                // Parsing error occurred
            }
        }

        // Cache the result
        $this->set_cached_data($cache_key, $shorts, $this->get_cache_duration());

        return $shorts;
    }

    /**
     * Get videos from a specific playlist (filtering for Shorts)
     */
    public function get_playlist_shorts($playlist_id, $max_results = 50)
    {
        $api_key = $this->get_api_key();

        if (empty($api_key) || empty($playlist_id)) {
            return new WP_Error('missing_credentials', __('API key and Playlist ID are required.', 'embed-youtube-shorts'));
        }

        // Check cache first
        $cache_key = 'eyss_playlist_shorts_' . md5($playlist_id . '_' . $max_results);
        $cached_data = $this->get_cached_data($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        // Step 1: Get videos from the specified playlist (fetch more to account for filtering)
        $fetch_count = max($max_results * 5, 200); // Fetch 5x more or 200, whichever is higher
        $playlist_videos = $this->get_playlist_videos($playlist_id, $fetch_count);
        if (is_wp_error($playlist_videos)) {
            return $playlist_videos;
        }

        // Step 2: Get video details to identify Shorts
        $video_ids = array();
        foreach ($playlist_videos as $video) {
            $video_ids[] = $video['contentDetails']['videoId'];
        }

        if (empty($video_ids)) {
            return array();
        }

        $video_details = $this->get_video_details($video_ids);
        if (is_wp_error($video_details)) {
            return $video_details;
        }

        // Step 3: Filter for Shorts (videos up to configured max duration)
        $shorts = array();
        foreach ($video_details as $video) {
            $duration = $this->parse_duration($video['contentDetails']['duration']);

            // YouTube Shorts can be up to configured max duration
            if ($duration <= $this->get_max_short_duration()) {
                $shorts[] = array(
                    'id' => $video['id'],
                    'title' => $video['snippet']['title'],
                    'description' => $video['snippet']['description'],
                    'thumbnail' => $video['snippet']['thumbnails']['high']['url'] ?? $video['snippet']['thumbnails']['default']['url'],
                    'published_at' => $video['snippet']['publishedAt'],
                    'duration' => $duration,
                    'view_count' => $video['statistics']['viewCount'] ?? 0,
                    'like_count' => $video['statistics']['likeCount'] ?? 0,
                    'url' => 'https://www.youtube.com/watch?v=' . $video['id']
                );

                if (count($shorts) >= $max_results) {
                    break;
                }
            }
        }

        // Cache the result
        $this->set_cached_data($cache_key, $shorts, $this->get_cache_duration());

        return $shorts;
    }

    /**
     * Get uploads playlist ID for a channel
     */
    public function get_uploads_playlist_id($channel_id)
    {
        $url = $this->api_base_url . 'channels';
        $params = array(
            'part' => 'contentDetails',
            'id' => $channel_id,
            'key' => $this->get_api_key(),
        );

        $response = $this->make_request($url, $params);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }

        if (empty($data['items'])) {
            return new WP_Error('no_channel', __('Channel not found.', 'embed-youtube-shorts'));
        }

        return $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
    }

    /**
     * Get videos from a playlist
     */
    public function get_playlist_videos($playlist_id, $max_results = 50)
    {
        $url = $this->api_base_url . 'playlistItems';
        $params = array(
            'part' => 'contentDetails',
            'playlistId' => $playlist_id,
            'maxResults' => 50, // Always fetch max per page
            'key' => $this->get_api_key(),
        );

        $all_videos = array();
        $next_page_token = '';
        $pages_fetched = 0;
        $max_pages = ceil($max_results / 50) + 2; // Fetch extra pages to account for filtering

        do {
            if ($next_page_token) {
                $params['pageToken'] = $next_page_token;
            }

            $response = $this->make_request($url, $params);

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
            $pages_fetched++;

            // Continue until we have enough videos or hit limits
        } while ($next_page_token && $pages_fetched < $max_pages && count($all_videos) < ($max_results * 3));

        return $all_videos; // Return all fetched videos for filtering
    }

    /**
     * Get detailed video information
     */
    public function get_video_details($video_ids)
    {
        if (empty($video_ids)) {
            return array();
        }

        // YouTube API allows up to 50 video IDs per request
        $chunks = array_chunk($video_ids, 50);
        $all_videos = array();

        foreach ($chunks as $chunk) {
            $url = $this->api_base_url . 'videos';
            $params = array(
                'part' => 'snippet,contentDetails,statistics',
                'id' => implode(',', $chunk),
                'key' => $this->get_api_key(),
            );

            $response = $this->make_request($url, $params);

            if (is_wp_error($response)) {
                return $response;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['error'])) {
                return new WP_Error('api_error', $data['error']['message']);
            }

            $all_videos = array_merge($all_videos, $data['items']);
        }

        return $all_videos;
    }

    /**
     * Parse ISO 8601 duration to seconds
     */
    public function parse_duration($duration)
    {
        if (empty($duration)) {
            return 0;
        }

        try {
            // YouTube returns duration in ISO 8601 format like PT1M30S or PT45S
            $interval = new DateInterval($duration);
            return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (Exception $e) {
            // Fallback parsing for malformed durations

            // Try manual parsing as fallback
            if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches)) {
                $hours = isset($matches[1]) ? intval($matches[1]) : 0;
                $minutes = isset($matches[2]) ? intval($matches[2]) : 0;
                $seconds = isset($matches[3]) ? intval($matches[3]) : 0;
                return ($hours * 3600) + ($minutes * 60) + $seconds;
            }

            return 0; // Default to 0 if we can't parse
        }
    }

    /**
     * Make HTTP request to YouTube API
     */
    private function make_request($url, $params)
    {
        $query_url = add_query_arg($params, $url);

        $response = wp_remote_get($query_url, array(
            'timeout' => 30,
            'user-agent' => 'WordPress/EmbedYouTubeShorts',
        ));

        return $response;
    }

    /**
     * Get all playlists for a channel (for full playlist import)
     */
    public function get_all_channel_playlists($channel_id = null)
    {
        $channel_id = $channel_id ?: $this->get_channel_id();
        $api_key = $this->get_api_key();

        if (empty($api_key) || empty($channel_id)) {
            return new WP_Error('missing_credentials', __('API key and Channel ID are required.', 'embed-youtube-shorts'));
        }

        // Check cache first
        $cache_key = 'eyss_all_playlists_' . md5($channel_id);
        $cached_data = $this->get_cached_data($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $url = $this->api_base_url . 'playlists';
        $all_playlists = array();
        $next_page_token = '';

        do {
            $params = array(
                'part' => 'id,snippet,contentDetails',
                'channelId' => $channel_id,
                'maxResults' => 50,
                'key' => $api_key,
            );

            if ($next_page_token) {
                $params['pageToken'] = $next_page_token;
            }

            $response = $this->make_request($url, $params);

            if (is_wp_error($response)) {
                return $response;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['error'])) {
                return new WP_Error('api_error', $data['error']['message']);
            }

            if (!empty($data['items'])) {
                $all_playlists = array_merge($all_playlists, $data['items']);
            }

            $next_page_token = $data['nextPageToken'] ?? '';
        } while ($next_page_token);

        // Cache the result for 24 hours
        $this->set_cached_data($cache_key, $all_playlists, 86400);

        return $all_playlists;
    }

    /**
     * Check which playlists contain a specific video
     */
    public function get_video_playlist_membership($video_id, $channel_id = null)
    {
        $channel_id = $channel_id ?: $this->get_channel_id();

        // Get all playlists first
        $playlists = $this->get_all_channel_playlists($channel_id);

        if (is_wp_error($playlists)) {
            return $playlists;
        }

        $video_playlists = array();

        foreach ($playlists as $playlist) {
            $playlist_id = $playlist['id'];

            // Skip the uploads playlist (we already handle that separately)
            if (strpos($playlist_id, 'UU') === 0) {
                continue;
            }

            // Check if video is in this playlist
            if ($this->is_video_in_playlist($video_id, $playlist_id)) {
                $video_playlists[] = array(
                    'id' => $playlist_id,
                    'title' => $playlist['snippet']['title'],
                    'description' => $playlist['snippet']['description'] ?? '',
                    'thumbnail' => $playlist['snippet']['thumbnails']['default']['url'] ?? ''
                );
            }
        }

        return $video_playlists;
    }

    /**
     * Check if a specific video is in a specific playlist
     */
    public function is_video_in_playlist($video_id, $playlist_id)
    {
        $url = $this->api_base_url . 'playlistItems';
        $params = array(
            'part' => 'contentDetails',
            'playlistId' => $playlist_id,
            'videoId' => $video_id,
            'maxResults' => 1,
            'key' => $this->get_api_key(),
        );

        $response = $this->make_request($url, $params);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return false;
        }

        return !empty($data['items']);
    }

    /**
     * Get videos from multiple playlists (batch processing)
     */
    public function get_videos_from_playlists($playlist_ids, $max_per_playlist = 50)
    {
        $all_playlist_videos = array();

        foreach ($playlist_ids as $playlist_data) {
            $playlist_id = is_array($playlist_data) ? $playlist_data['id'] : $playlist_data;

            $videos = $this->get_playlist_videos($playlist_id, $max_per_playlist);

            if (!is_wp_error($videos) && !empty($videos)) {
                $playlist_title = is_array($playlist_data) ? $playlist_data['title'] : 'Unknown Playlist';

                $all_playlist_videos[$playlist_id] = array(
                    'playlist_title' => $playlist_title,
                    'videos' => $videos
                );
            }
        }

        return $all_playlist_videos;
    }

    /**
     * Get cached data
     */
    private function get_cached_data($cache_key)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'eyss_cache';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom cache table query, cannot use wp_cache_* functions as this IS the caching system
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT cache_data FROM %i WHERE cache_key = %s AND expiry_time > NOW()",
            $table_name,
            $cache_key
        ));

        if ($result) {
            return maybe_unserialize($result->cache_data);
        }

        return false;
    }

    /**
     * Set cached data
     */
    private function set_cached_data($cache_key, $data, $expiry_seconds)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'eyss_cache';
        $expiry_time = gmdate('Y-m-d H:i:s', time() + $expiry_seconds);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom cache table insert/update, cannot use wp_cache_* functions as this IS the caching system
        $wpdb->replace($table_name, array(
            'cache_key' => $cache_key,
            'cache_data' => maybe_serialize($data),
            'expiry_time' => $expiry_time,
        ));
    }

    /**
     * Clear expired cache entries
     */
    public function clear_expired_cache()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'eyss_cache';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom cache table cleanup, cannot use wp_cache_* functions as this IS the caching system
        $wpdb->query($wpdb->prepare("DELETE FROM %i WHERE expiry_time < NOW()", $table_name));
    }

    /**
     * Get API key from settings
     */
    public function get_api_key()
    {
        return isset($this->settings['api_key']) ? $this->settings['api_key'] : '';
    }

    /**
     * Get Channel ID from settings
     */
    private function get_channel_id()
    {
        return isset($this->settings['channel_id']) ? $this->settings['channel_id'] : '';
    }

    /**
     * Get max short duration from settings
     */
    public function get_max_short_duration()
    {
        return isset($this->settings['max_short_duration']) ? $this->settings['max_short_duration'] : 180;
    }

    /**
     * Get cache duration from settings
     */
    private function get_cache_duration()
    {
        return isset($this->settings['cache_duration']) ? $this->settings['cache_duration'] : 3600;
    }
}
