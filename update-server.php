<?php

/**
 * Simple update server for Embed YouTube Shorts Plugin
 * Place this file on your server at https://plugins.citcom.support/eyss/
 */

// Prevent direct access
if (!defined('ABSPATH') && !isset($_SERVER['REQUEST_URI'])) {
    // This is being accessed directly, allow it
}

/**
 * Plugin information
 */
$plugin_info = array(
    'name' => 'Embed YouTube Shorts',
    'slug' => 'embed-youtube-shorts',
    'plugin' => 'embed-youtube-shorts/embed-youtube-shorts.php',
    'new_version' => '2.3.1',
    'requires' => '5.0',
    'tested' => '6.8.3',
    'requires_php' => '7.4',
    'download_link' => 'https://plugins.citcom.support/eyss/embed-youtube-shorts.zip',
    'details_url' => 'https://plugins.citcom.support/eyss/',
    'author' => 'Gareth Hale, CitCom.',
    'homepage' => 'https://plugins.citcom.support/eyss/',
    'description' => 'A comprehensive WordPress plugin that imports and displays YouTube Shorts using a custom post type system with playlist organization.',
    'last_updated' => date('Y-m-d H:i:s'),
    'upgrade_notice' => 'Bug fix release! Fixed video import progress bar animation and improved background import reliability with better error handling.',
    'compatibility' => '6.8.3'
);

/**
 * Handle different request types
 */
$request_uri = $_SERVER['REQUEST_URI'];

if (strpos($request_uri, 'update-check.json') !== false) {
    // Simple update check
    header('Content-Type: application/json');
    echo json_encode($plugin_info);
    exit;
}

if (strpos($request_uri, 'update-info.json') !== false) {
    // Detailed plugin information
    $detailed_info = array(
        'name' => $plugin_info['name'],
        'slug' => $plugin_info['slug'],
        'version' => $plugin_info['new_version'],
        'download_url' => $plugin_info['download_link'],
        'homepage' => $plugin_info['homepage'],
        'requires' => $plugin_info['requires'],
        'tested' => $plugin_info['tested'],
        'requires_php' => $plugin_info['requires_php'],
        'author' => $plugin_info['author'],
        'author_profile' => 'https://citcom.co.uk',
        'last_updated' => $plugin_info['last_updated'],
        'sections' => array(
            'description' => $plugin_info['description'],
            'installation' => '1. Upload the plugin to your /wp-content/plugins/ directory<br>2. Activate the plugin through the WordPress admin<br>3. Go to Settings > YouTube Shorts to configure your API settings<br>4. Add your YouTube Data API v3 key and Channel ID<br>5. Click \'Import Videos\' to start importing your YouTube Shorts',
            'changelog' => '<h4>2.3.0 - WordPress.org Compliance Release</h4><br><ul><br><li><strong>WordPress.org Ready:</strong> Achieved 100% plugin directory compliance with comprehensive security and code quality improvements</li><br><li><strong>Security Enhancement:</strong> Complete input validation, nonce verification, and data sanitization throughout all plugin files</li><br><li><strong>Internationalization:</strong> Full i18n support with proper translator comments and text domain usage</li><br><li><strong>Performance Optimization:</strong> Database queries optimized with justified meta_query and tax_query operations</li><br><li><strong>Local Assets:</strong> All external dependencies (Splide.js v4.1.4) now included locally for WordPress.org compliance</li><br><li><strong>Code Standards:</strong> WordPress coding standards compliance with proper escaping, validation, and documentation</li><br><li><strong>Production Ready:</strong> Removed all debug code, test files, and development dependencies for clean release</li><br></ul><br><h4>2.2.4 - Carousel Enhancement & Code Cleanup</h4><br><ul><br><li><strong>Professional Carousel:</strong> Upgraded to Splide.js v4.1.4 library for smooth, infinite loop carousel with center focus</li><br><li><strong>Optimized Experience:</strong> Carousel defaults to 8 videos with disabled search/load-more for cleaner UX</li><br><li><strong>Responsive Design:</strong> Professional breakpoints (4 desktop, 2 tablet, 1 mobile) with touch/swipe support</li><br><li><strong>Code Cleanup:</strong> Removed all test/debug files and consolidated documentation into single README.md</li><br><li><strong>Enhanced Error Handling:</strong> Improved debugging capabilities and code organization</li><br></ul><br><h4>2.2.3 - Minor Formatting Fix</h4><br><ul><br><li><strong>Update System Polish:</strong> Fixed formatting in update notifications to display proper line breaks instead of literal \\n characters</li><br><li><strong>Professional Display:</strong> Improved presentation of changelog and instructions in WordPress admin update interface</li><br></ul><br><h4>2.2.2 - Critical Search Fixes</h4><br><ul><br><li><strong>Playlist-Scoped Search:</strong> Search now properly filters within selected playlist instead of searching all videos</li><br><li><strong>Count Restoration Fix:</strong> Total count correctly restores to original number after clearing search</li><br><li><strong>Enhanced Data Attributes:</strong> Proper playlist information passed to search functionality</li><br><li><strong>Multi-Playlist Support:</strong> Search works correctly with single, multiple, and excluded playlist filters</li><br><li><strong>Improved UX:</strong> Search behavior now matches user expectations with accurate scoping</li><br></ul><br><h4>2.2.0 - Infinite Scroll Feature</h4><br><ul><br><li><strong>New Infinite Scroll Option:</strong> Added setting to choose between Load More Button and Infinite Scroll</li><br><li><strong>Automatic Content Loading:</strong> Videos load automatically when scrolling near the bottom</li><br><li><strong>Enhanced Admin Settings:</strong> New Load More Type setting with radio button options</li><br><li><strong>Seamless Integration:</strong> Works with all existing features including search and filtering</li><br></ul><br><h4>2.0.0 - Major Architecture Overhaul</h4><br><ul><br><li><strong>Custom Post Type Implementation:</strong> Videos now imported as WordPress posts with full admin integration</li><br><li><strong>Playlist Taxonomy System:</strong> Automatic playlist detection and WordPress taxonomy organization</li><br><li><strong>Enhanced Search & Filtering:</strong> Real-time search with clear button and accurate counts</li><br></ul>',
            'faq' => '<h4>Do I need a YouTube API key?</h4><br><p>Yes, you need a YouTube Data API v3 key from Google Cloud Console. The plugin provides step-by-step instructions for obtaining one.</p>'
        ),
        'banners' => array(
            'low' => 'https://plugins.citcom.support/eyss/assets/banner-772x250.jpg',
            'high' => 'https://plugins.citcom.support/eyss/assets/banner-1544x500.jpg'
        ),
        'icons' => array(
            '1x' => 'https://plugins.citcom.support/eyss/assets/icon-128x128.jpg',
            '2x' => 'https://plugins.citcom.support/eyss/assets/icon-256x256.jpg'
        ),
        'tags' => array('youtube', 'shorts', 'video', 'embed', 'playlist', 'responsive'),
        'upgrade_notice' => array(
            '2.2.3' => $plugin_info['upgrade_notice']
        )
    );

    header('Content-Type: application/json');
    echo json_encode($detailed_info);
    exit;
}

// Default response - plugin information page
?>
<!DOCTYPE html>
<html>

<head>
    <title>Embed YouTube Shorts Plugin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #0073aa;
            color: white;
            padding: 20px;
            border-radius: 5px;
        }

        .version {
            background: #f1f1f1;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .download {
            background: #00a32a;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }

        .changelog {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><?php echo $plugin_info['name']; ?></h1>
        <p><?php echo $plugin_info['description']; ?></p>
    </div>

    <div class="version">
        <h2>Current Version: <?php echo $plugin_info['new_version']; ?></h2>
        <p><strong>Last Updated:</strong> <?php echo $plugin_info['last_updated']; ?></p>
        <p><strong>Requires WordPress:</strong> <?php echo $plugin_info['requires']; ?>+</p>
        <p><strong>Requires PHP:</strong> <?php echo $plugin_info['requires_php']; ?>+</p>
        <p><strong>Tested up to:</strong> WordPress <?php echo $plugin_info['tested']; ?></p>
    </div>

    <a href="<?php echo $plugin_info['download_link']; ?>" class="download">Download Plugin</a>

    <div class="changelog">
        <h3>What's New in Version 2.2.3</h3>
        <ul>
            <li><strong>Critical Search Fixes:</strong> Search now properly filters within selected playlist instead of all videos</li>
            <li><strong>Count Restoration:</strong> Total count correctly restores after clearing search</li>
            <li><strong>Enhanced Playlist Support:</strong> Full support for single, multiple, and excluded playlist filters</li>
            <li><strong>Improved Data Handling:</strong> Proper playlist information passed to search functionality</li>
            <li><strong>Better User Experience:</strong> Search behavior now matches expectations with accurate scoping</li>
        </ul>
    </div>

    <h3>Installation</h3>
    <ol>
        <li>Download the plugin zip file</li>
        <li>Upload to your /wp-content/plugins/ directory</li>
        <li>Activate through WordPress admin</li>
        <li>Configure your YouTube API settings</li>
        <li>Import your videos and start displaying them!</li>
    </ol>

    <p><strong>Support:</strong> For questions and support, visit our <a href="<?php echo $plugin_info['homepage']; ?>">plugin homepage</a>.</p>
</body>

</html>