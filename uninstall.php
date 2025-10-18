<?php

/**
 * Plugin Uninstall Script
 *
 * This file is executed when the plugin is uninstalled (not just deactivated).
 * It cleans up all plugin data from the database.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up plugin data when uninstalled
 */
function eyss_uninstall_cleanup()
{
    global $wpdb;

    // Remove plugin options
    delete_option('eyss_settings');

    // Remove any transients
    delete_transient('eyss_api_test_result');

    // Drop custom cache table
    $table_name = $wpdb->prefix . 'eyss_cache';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Clear any scheduled events
    wp_clear_scheduled_hook('eyss_clear_cache');

    // Remove any cached data from wp_options that might have been created
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'eyss_%'");

    // Clean up any temporary files if they exist
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/eyss-temp/';

    if (is_dir($temp_dir)) {
        eyss_recursive_rmdir($temp_dir);
    }
}

/**
 * Recursively remove directory and its contents
 */
function eyss_recursive_rmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
                    eyss_recursive_rmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rmdir($dir);
    }
}

// Execute cleanup
eyss_uninstall_cleanup();
