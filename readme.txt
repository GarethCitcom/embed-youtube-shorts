=== Embed YouTube Shorts ===
Contributors: citcom
Tags: youtube, shorts, embed, playlist, carousel
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display YouTube Shorts from channel playlists with professional carousel, grid, and list layouts using the YouTube Data API.

== Description ==

**Embed YouTube Shorts** is a comprehensive WordPress plugin that seamlessly imports and displays YouTube Shorts from your channel playlists. Transform your YouTube content into engaging, responsive displays on your WordPress website.

= 🎬 Key Features =

* **Professional Carousel Layout** - Smooth infinite loop carousel with center focus using Splide.js v4.1.4
* **Multiple Layout Options** - Choose from carousel, grid, or list layouts to match your design
* **Responsive Design** - Optimized breakpoints (4 desktop, 2 tablet, 1 mobile) for all devices
* **Real-time Search** - Live search functionality with playlist-scoped filtering
* **Playlist Integration** - Automatic playlist detection and WordPress taxonomy organization
* **Touch & Swipe Support** - Mobile-friendly carousel navigation with gesture controls
* **Custom Post Type System** - Videos imported as WordPress posts with full admin integration

= 🚀 Layout Options =

**Carousel Layout**
* Professional Splide.js carousel with infinite loop
* Center focus design with smooth transitions
* Touch/swipe navigation for mobile devices
* Defaults to 8 videos for optimal performance
* Auto-disables search and load more for clean UX

**Grid Layout**
* Responsive column system (1-6 columns)
* Masonry-style arrangement
* Hover effects and smooth animations
* Perfect for showcasing video collections

**List Layout**
* Detailed video information display
* Thumbnail with title and description
* Ideal for video directories and catalogs

= 🔧 Easy Setup =

1. Get your free YouTube Data API v3 key from Google Cloud Console
2. Install and activate the plugin
3. Configure your API settings in Settings > YouTube Shorts
4. Import videos from your channel playlists
5. Use the `[youtube_shorts]` shortcode anywhere on your site

= 📱 Responsive & Mobile Optimized =

The plugin is built mobile-first with professional responsive breakpoints:
* **Desktop**: Up to 4 videos per row
* **Tablet**: 2 videos per row
* **Mobile**: 1 video per row with touch navigation

= 🎯 Shortcode Examples =

Basic usage:
`[youtube_shorts]`

Carousel with specific count:
`[youtube_shorts layout="carousel" count="8"]`

Grid layout with playlist filter:
`[youtube_shorts layout="grid" columns="3" playlist="my-playlist"]`

Custom count with search enabled:
`[youtube_shorts count="12" show_search="true"]`

= 🛠️ Advanced Features =

* **AJAX-Powered Interface** - Smooth loading without page refreshes
* **Infinite Scroll Option** - Choose between load more button or infinite scroll
* **Playlist Filtering** - Filter by single or multiple playlists
* **Caching System** - Optimized performance with WordPress transients
* **Admin Dashboard** - Complete video management interface
* **Bulk Operations** - Efficient bulk import and management tools

= 🔒 Security & Performance =

* Secure API integration with proper authentication
* Sanitized inputs and prepared database statements
* Optimized queries with smart caching
* WordPress coding standards compliance
* Regular security updates and maintenance

= 🌟 Perfect For =

* Content creators showcasing YouTube Shorts
* Businesses displaying product demos
* Educational sites with video tutorials
* Entertainment websites with video content
* Any WordPress site wanting to integrate YouTube Shorts

= 📖 Documentation =

Comprehensive documentation is included with the plugin, covering:
* Step-by-step setup instructions
* YouTube API key generation guide
* Shortcode parameters and examples
* Layout customization options
* Troubleshooting and FAQ

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "Embed YouTube Shorts"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Upload to your `/wp-content/plugins/` directory
3. Extract the files
4. Activate the plugin through the 'Plugins' menu in WordPress

= Configuration =

1. Go to Settings > YouTube Shorts in your WordPress admin
2. Get your YouTube Data API v3 key from [Google Cloud Console](https://console.cloud.google.com/)
3. Enter your YouTube Channel ID
4. Click "Import Videos" to start importing your YouTube Shorts
5. Use the `[youtube_shorts]` shortcode to display videos on any page or post

== Frequently Asked Questions ==

= Do I need a YouTube API key? =

Yes, you need a free YouTube Data API v3 key from Google Cloud Console. The plugin provides step-by-step instructions for obtaining one.

= Can I display videos from multiple channels? =

Currently, the plugin supports one channel per installation. You can configure different playlists from that channel.

= Does the carousel work on mobile devices? =

Yes! The carousel is fully responsive with touch and swipe support optimized for mobile devices.

= Can I customize the video layouts? =

Yes, the plugin offers carousel, grid, and list layouts with customizable options like column counts, video counts, and responsive breakpoints.

= Does this work with YouTube Shorts only? =

The plugin is optimized for YouTube Shorts but works with any YouTube video from your channel playlists.

= Is there a limit to how many videos I can import? =

The limit depends on your YouTube API quota. The free tier typically allows for thousands of API calls per day.

= Can I filter videos by playlist? =

Yes, you can filter videos by single or multiple playlists using the shortcode parameters or the built-in search functionality.

= Does the plugin affect site performance? =

No, the plugin is optimized for performance with smart caching, efficient queries, and CDN delivery of assets.

== Screenshots ==

1. **Professional Carousel Layout** - Smooth infinite loop carousel with center focus and touch navigation
2. **Responsive Grid Layout** - Clean grid display with hover effects and responsive columns
3. **Admin Settings Panel** - Easy-to-use configuration interface with step-by-step guidance
4. **Video Management Dashboard** - Complete admin interface for managing imported videos
5. **Mobile Responsive Design** - Optimized display across all devices and screen sizes
6. **Playlist Integration** - Automatic playlist detection and filtering options

== Changelog ==

= 2.3.1 - 2025-10-19 =
* **Fixed**: Video import progress bar animation now properly displays real-time import progress
* **Fixed**: Resolved AJAX handler conflicts preventing background import with progress tracking
* **Enhanced**: Improved import progress polling with better error handling and retry logic
* **Enhanced**: Added fallback import method when WordPress cron is disabled
* **UI**: Progress bar now shows smooth CSS animations during video import process

= 2.3.0 - 2025-10-18 =
* **Enhanced**: Complete WordPress.org compliance - achieved 100% plugin directory readiness
* **Security**: Implemented comprehensive input validation, nonce verification, and data sanitization
* **Internationalization**: Full i18n support with proper translator comments and text domain usage
* **Performance**: Optimized database queries with justified meta_query and tax_query usage
* **Resources**: Localized all external assets - included Splide.js v4.1.4 files in plugin package
* **Code Quality**: WordPress coding standards compliance with proper escaping and validation
* **Database**: Documented all direct database operations with performance justifications
* **Production Ready**: Removed all debug code, test files, and development dependencies

= 2.2.4 - 2025-10-18 =
* **Added**: Professional Splide.js carousel with infinite loop and center focus
* **Added**: Touch and swipe support for mobile carousel navigation
* **Enhanced**: Carousel defaults to 8 videos with disabled search/load more for cleaner UX
* **Enhanced**: Responsive breakpoints (4 desktop, 2 tablet, 1 mobile)
* **Fixed**: Carousel layout issues with professional library upgrade
* **Removed**: Development files and legacy carousel code for clean production release

= 2.2.3 - 2025-10-18 =
* **Fixed**: Update system formatting for proper display in WordPress admin
* **Improved**: Professional presentation of changelog and update notifications

= 2.2.2 - 2025-10-18 =
* **Added**: Playlist-scoped search functionality
* **Added**: Enhanced multi-playlist support
* **Fixed**: Count restoration after clearing search
* **Fixed**: Search behavior for accurate playlist-based filtering

= 2.2.0 - 2025-10-18 =
* **Added**: Infinite scroll feature with automatic content loading
* **Added**: Enhanced admin settings with load more type options
* **Improved**: User experience with seamless infinite scrolling
* **Enhanced**: Integration with existing search and filtering features

= 2.0.0 - 2025-10-17 =
* **Added**: Custom post type system for WordPress integration
* **Added**: Playlist taxonomy organization
* **Added**: Complete admin dashboard for video management
* **Added**: Bulk import system and shortcode functionality
* **Enhanced**: Database optimization and WordPress standards compliance

[View complete changelog](https://github.com/GarethCitcom/embed-youtube-shorts/blob/main/CHANGELOG.md)

== Upgrade Notice ==

= 2.3.0 =
WordPress.org compliance release! Complete security, performance, and code quality improvements. All external dependencies now included locally. Ready for WordPress plugin directory submission.

= 2.2.4 =
Major carousel enhancement! Upgraded to professional Splide.js library with infinite loop, center focus, and mobile touch support. Comprehensive code cleanup for production release.

= 2.2.0 =
New infinite scroll feature added! Choose between load more button and automatic infinite scrolling for better user experience.

= 2.0.0 =
Major architecture upgrade! Custom post type system, playlist taxonomy, and complete admin dashboard. Backup recommended before upgrading.

== Support ==

For support, documentation, and updates:

* **Plugin Homepage**: [https://plugins.citcom.support/eyss](https://plugins.citcom.support/eyss)
* **GitHub Repository**: [https://github.com/GarethCitcom/embed-youtube-shorts](https://github.com/GarethCitcom/embed-youtube-shorts)
* **Developer Website**: [https://citcom.co.uk](https://citcom.co.uk)

== Privacy ==

This plugin connects to the YouTube Data API v3 to import video information. No personal data is collected or transmitted beyond what's necessary for the YouTube API integration. All data is stored locally in your WordPress database.

== Credits ==

* **Splide.js** - Professional carousel library for smooth video navigation
* **YouTube Data API v3** - Google's official API for YouTube integration
* **WordPress** - Amazing platform that makes this plugin possible