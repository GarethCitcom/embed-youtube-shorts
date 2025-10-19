# Changelog

All notable changes to the Embed YouTube Shorts plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.1] - 2025-10-19

### Fixed
- **Video Import Progress Bar**: Fixed progress bar animation that wasn't displaying real-time import progress
- **AJAX Handler Conflicts**: Resolved duplicate AJAX handlers that prevented proper background import with progress tracking
- **Background Import Reliability**: Fixed import process to properly use background processing with visual progress updates

### Enhanced
- **Progress Polling System**: Improved import progress polling with better error handling and retry logic
- **Cron Fallback**: Added fallback import method for when WordPress cron is disabled or fails to schedule
- **UI Animations**: Progress bar now shows smooth CSS transitions during video import process
- **Error Handling**: Enhanced error messages and timeout management for import monitoring

## [2.3.0] - 2025-10-18

### Enhanced
- **WordPress.org Compliance**: Achieved 100% WordPress plugin directory readiness with comprehensive compliance updates
- **Security Hardening**: Implemented comprehensive input validation, nonce verification, and data sanitization across all functions
- **Internationalization**: Complete i18n support with proper translator comments and text domain consistency
- **Code Quality**: Full WordPress coding standards compliance with proper escaping, validation, and formatting

### Performance
- **Database Optimization**: All database queries optimized with justified meta_query and tax_query usage documentation
- **Resource Localization**: All external assets localized - included Splide.js v4.1.4 files directly in plugin package
- **Query Efficiency**: Comprehensive documentation of all database operations with performance justifications

### Production Ready
- **Clean Codebase**: Removed all debug code, test files, and development dependencies for production deployment
- **Documentation**: Enhanced code comments and inline documentation for maintainability
- **Standards Compliance**: Eliminated all PHPCS violations and coding standard issues

## [2.2.4] - 2025-10-18

### Added
- **Professional Splide.js Carousel**: Upgraded to industry-standard Splide.js v4.1.4 library for smooth, infinite loop carousel
- **Center Focus Design**: Carousel now centers active video with elegant loop navigation
- **Optimized Carousel Experience**: Disabled search and load more functions for carousel layout to provide cleaner UX
- **Smart Count Defaults**: Carousel layout automatically defaults to 8 videos when count not specified in shortcode
- **Touch & Swipe Support**: Full mobile touch navigation with smooth gesture controls

### Enhanced
- **Responsive Breakpoints**: Professional responsive design with 4 desktop, 2 tablet, 1 mobile video display
- **Error Handling**: Improved carousel initialization with comprehensive error handling and debugging
- **Code Organization**: Cleaned up development files and consolidated documentation for production release
- **Performance**: Optimized carousel loading with CDN delivery of Splide.js library

### Fixed
- **Carousel Layout Issues**: Resolved previous carousel implementation problems with professional library upgrade
- **Mobile Experience**: Enhanced touch navigation and responsive behavior across all devices
- **Loading States**: Improved carousel initialization and fallback handling

### Removed
- **Development Files**: Cleaned up all test files, debug utilities, and development artifacts for clean production release
- **Legacy Carousel Code**: Replaced custom carousel implementation with professional Splide.js solution

## [2.2.3] - 2025-10-18

### Fixed
- **Update System Formatting**: Fixed formatting in update notifications to display proper line breaks instead of literal \n characters
- **Professional Display**: Improved presentation of changelog and instructions in WordPress admin update interface

## [2.2.2] - 2025-10-18

### Added
- **Playlist-Scoped Search**: Search functionality now properly filters within selected playlist instead of searching all videos
- **Enhanced Multi-Playlist Support**: Search works correctly with single, multiple, and excluded playlist filters
- **Improved Data Attributes**: Proper playlist information passed to search functionality for accurate scoping

### Fixed
- **Count Restoration**: Total count correctly restores to original number after clearing search
- **Search Behavior**: Search now matches user expectations with accurate playlist-based filtering
- **Data Consistency**: Enhanced search data handling for reliable results across all playlist configurations

## [2.2.1] - 2025-10-18

### Added
- **Enhanced Error Handling**: Improved debugging capabilities and error reporting
- **Better Code Organization**: Streamlined file structure and improved maintainability

### Fixed
- **Minor Bug Fixes**: Various small improvements and stability enhancements

## [2.2.0] - 2025-10-18

### Added
- **Infinite Scroll Feature**: New setting to choose between Load More Button and Infinite Scroll
- **Automatic Content Loading**: Videos load automatically when scrolling near the bottom
- **Enhanced Admin Settings**: New Load More Type setting with intuitive radio button options
- **Seamless Integration**: Infinite scroll works with all existing features including search and filtering

### Improved
- **User Experience**: More engaging content discovery with smooth infinite scrolling
- **Performance**: Optimized loading system for better performance during scroll operations
- **Admin Interface**: Enhanced settings panel with clear option descriptions

## [2.1.0] - 2025-10-17

### Added
- **Real-time Search**: Live search functionality with instant filtering of video results
- **Advanced Filtering**: Filter videos by playlist with multiple selection options
- **Load More Functionality**: Paginated loading system for better performance with large video collections
- **Count Management**: Configurable number of videos to display with shortcode parameter support

### Enhanced
- **AJAX Integration**: Smooth user experience with AJAX-powered search and loading
- **Responsive Design**: Mobile-optimized interface with touch-friendly controls
- **Performance**: Optimized database queries and caching system

## [2.0.0] - 2025-10-17

### Added - Major Architecture Overhaul
- **Custom Post Type System**: Videos now imported as WordPress posts with full admin integration
- **Playlist Taxonomy**: Automatic playlist detection and WordPress taxonomy organization
- **Admin Dashboard**: Complete administrative interface for video management
- **Bulk Import System**: Efficient bulk video import from YouTube playlists
- **Shortcode System**: Flexible shortcode with multiple layout and filtering options

### Enhanced
- **WordPress Integration**: Full integration with WordPress post system and admin interface
- **Database Optimization**: Efficient storage and retrieval of video data using WordPress standards
- **Extensibility**: Plugin architecture designed for future enhancements and customizations

## [1.1.0] - 2025-10-16

### Added
- **Multiple Layout Options**: Grid, list, and carousel layout support
- **Responsive Grid System**: Configurable column layouts for different screen sizes
- **Video Metadata**: Display video titles, descriptions, and publication dates
- **Thumbnail Optimization**: High-quality thumbnail display with proper sizing

### Improved
- **Loading Performance**: Optimized video loading and thumbnail generation
- **Mobile Experience**: Enhanced responsive design for mobile devices

## [1.0.0] - 2025-10-15

### Added - Initial Release
- **YouTube API Integration**: Connect with YouTube Data API v3 for video import
- **Basic Video Display**: Display YouTube Shorts in grid layout
- **Channel Integration**: Import videos from specific YouTube channels
- **Shortcode Support**: Basic shortcode implementation for embedding videos
- **Admin Settings**: Configuration panel for API keys and basic settings

### Features
- WordPress 5.0+ compatibility
- PHP 7.4+ support
- Responsive design foundation
- Security implementation with proper sanitization
- Basic caching system for improved performance

### Security
- Input sanitization and validation
- Nonce verification for all operations
- Capability checks for admin functions
- Prepared statements for database queries