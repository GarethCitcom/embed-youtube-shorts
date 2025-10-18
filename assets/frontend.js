/**
 * Embed YouTube Shorts - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Plugin namespace
    const EYSS = {

        /**
         * Initialize the plugin
         */
        init: function() {
            this.bindEvents();
            this.initializeCarousels();
            this.initializeSearch();
            this.initializeInfiniteScroll();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Video click handler
            $(document).on('click', '.eyss-video-item', this.handleVideoClick);

            // Modal close handlers
            $(document).on('click', '.eyss-modal-close', this.closeModal);
            $(document).on('click', '.eyss-modal', function(e) {
                if (e.target === this) {
                    EYSS.closeModal();
                }
            });

            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // Escape key
                    EYSS.closeModal();
                }
            });

            // Handle window resize (Splide handles this automatically)
            // Load more functionality (if needed in the future)
            $(document).on('click', '.eyss-load-more', this.handleLoadMore);
        },

        /**
         * Initialize carousel functionality with Splide.js
         */
        initializeCarousels: function() {
            console.log('EYSS: Initializing carousels');

            // Initialize all Splide carousels
            $('.eyss-carousel-splide').each(function() {
                const $carousel = $(this);
                console.log('EYSS: Found carousel element', $carousel);
                EYSS.setupSplideCarousel($carousel);
            });
        },

        /**
         * Setup individual Splide carousel
         */
        setupSplideCarousel: function($container) {
            // Check if Splide is available
            if (typeof Splide === 'undefined') {
                console.error('EYSS: Splide.js library not loaded');
                return;
            }

            // Get the splide element
            const splideElement = $container[0];

            if (!splideElement) {
                console.error('EYSS: Splide element not found');
                return;
            }

            console.log('EYSS: Setting up Splide carousel', splideElement);

            try {
                // Get configuration from data attribute or use defaults
                let config = {
                    type: 'loop',
                    focus: 'center',
                    perPage: 4,
                    perMove: 1,
                    gap: '20px',
                    padding: 0,
                    pagination: false,
                    arrows: true,
                    breakpoints: {
                        768: {
                            perPage: 2,
                        },
                        480: {
                            perPage: 1,
                        }
                    },
                    classes: {
                        arrows: 'splide__arrows eyss-splide-arrows',
                        arrow: 'splide__arrow eyss-splide-arrow',
                        prev: 'splide__arrow--prev eyss-splide-prev',
                        next: 'splide__arrow--next eyss-splide-next',
                    }
                };

                // Try to get config from data attribute
                const dataConfig = $container.data('splide');
                if (dataConfig && typeof dataConfig === 'object') {
                    config = Object.assign(config, dataConfig);
                    console.log('EYSS: Using data config for Splide', dataConfig);
                }

                // Create new Splide instance
                const splide = new Splide(splideElement, config);

                // Mount the splide
                splide.mount();
                console.log('EYSS: Splide carousel mounted successfully');

                // Store the splide instance for later reference
                $container.data('splide-instance', splide);
            } catch (error) {
                console.error('EYSS: Error setting up Splide carousel:', error);
            }
        },

        /**
         * Update carousel after search filtering (for Splide)
         */
        updateCarouselAfterSearch: function($container) {
            // Get the splide instance from the carousel element
            const $splideCarousel = $container.find('.eyss-carousel-splide');
            const splide = $splideCarousel.data('splide-instance');

            if (splide) {
                // Refresh the splide to recalculate slides
                splide.refresh();
                console.log('EYSS: Splide carousel refreshed after search');
            } else {
                // Re-initialize if instance not found
                console.log('EYSS: Re-initializing Splide carousel after search');
                this.setupSplideCarousel($splideCarousel);
            }
        },

        /**
         * Handle video click
         */
        handleVideoClick: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $video = $(this);
            const videoId = $video.data('video-id');
            const $container = $video.closest('.eyss-container');
            const autoplay = $container.data('autoplay') === 1;

            if (!videoId) return;

            EYSS.openModal(videoId, autoplay);
        },

        /**
         * Open video modal
         */
        openModal: function(videoId, autoplay = false) {
            const $modal = $('#eyss-modal');
            const $iframe = $('#eyss-modal-iframe');

            // Create YouTube embed URL
            let embedUrl = `https://www.youtube.com/embed/${videoId}?`;
            const params = new URLSearchParams({
                'rel': '0',
                'modestbranding': '1',
                'fs': '1',
                'cc_load_policy': '0',
                'iv_load_policy': '3',
                'autohide': '0',
                'color': 'white',
                'theme': 'dark'
            });

            if (autoplay) {
                params.append('autoplay', '1');
            }

            embedUrl += params.toString();

            $iframe.attr('src', embedUrl);
            $modal.fadeIn(300);

            // Prevent body scroll
            $('body').addClass('eyss-modal-open');

            // Focus trap
            $modal.find('.eyss-modal-close').focus();
        },

        /**
         * Close video modal
         */
        closeModal: function() {
            const $modal = $('#eyss-modal');
            const $iframe = $('#eyss-modal-iframe');

            $modal.fadeOut(300, function() {
                $iframe.attr('src', '');
            });

            // Restore body scroll
            $('body').removeClass('eyss-modal-open');
        },

        /**
         * Handle load more videos
         */
        handleLoadMore: function(e) {
            e.preventDefault();

            const $button = $(this);
            const channelId = $button.data('channel') || '';
            const playlistId = $button.data('playlist') || '';
            const offset = parseInt($button.data('offset')) || 0;
            const count = parseInt($button.data('count')) || 12;
            const targetId = $button.data('target');
            const $container = $('#' + targetId);

            if ($button.hasClass('loading')) return;

            const originalText = $button.text();
            $button.addClass('loading').text('Loading...');

            $.ajax({
                url: eyss_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_load_more',
                    nonce: eyss_ajax.nonce,
                    channel_id: channelId,
                    playlist_id: playlistId,
                    offset: offset,
                    count: count
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.eyss-videos').append(response.data.html);

                        // Update button offset for next load
                        $button.data('offset', offset + count);

                        // Update search count if search is active
                        const $searchInput = $container.find('.eyss-search-input');
                        if ($searchInput.length && $searchInput.val()) {
                            // Reapply search filter to new items
                            EYSS.handleSearch.call($searchInput[0]);
                        } else {
                            // Update total count from server response
                            if (response.data.total_count) {
                                $container.find('.eyss-total-count').text(response.data.total_count);
                            } else {
                                // Fallback: count DOM elements
                                const newTotal = $container.find('.eyss-video-item').length;
                                $container.find('.eyss-total-count').text(newTotal);
                            }
                        }

                        if (!response.data.has_more) {
                            $button.fadeOut();
                        }
                    } else {
                        alert('Error loading more videos: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error loading more videos. Please try again.');
                },
                complete: function() {
                    $button.removeClass('loading').text(originalText);
                }
            });
        },

        /**
         * Initialize infinite scroll functionality
         */
        initializeInfiniteScroll: function() {
            const self = this;

            // Find all containers with infinite scroll enabled
            const $containers = $('.eyss-container[data-scroll-type="infinite_scroll"]');

            $containers.each(function() {
                const $container = $(this);
                const $loadMoreBtn = $container.find('.eyss-load-more-btn');

                if ($loadMoreBtn.length) {
                    // Hide the load more button for infinite scroll
                    $loadMoreBtn.hide();

                    // Create scroll listener
                    self.setupInfiniteScrollListener($container, $loadMoreBtn);
                }
            });
        },

        /**
         * Setup infinite scroll listener for a container
         */
        setupInfiniteScrollListener: function($container, $loadMoreBtn) {
            const self = this;
            let isLoading = false;
            let throttleTimeout;

            // Throttled scroll handler
            function onScroll() {
                if (throttleTimeout) clearTimeout(throttleTimeout);

                throttleTimeout = setTimeout(function() {
                    if (isLoading) return;

                    const containerBottom = $container.offset().top + $container.outerHeight();
                    const viewportBottom = $(window).scrollTop() + $(window).height();
                    const threshold = 200; // Trigger 200px before reaching bottom

                    if (viewportBottom + threshold >= containerBottom) {
                        isLoading = true;

                        // Show loading indicator
                        self.showInfiniteScrollLoader($container);

                        // Trigger load more
                        self.loadMoreForInfiniteScroll($loadMoreBtn, function() {
                            isLoading = false;
                            self.hideInfiniteScrollLoader($container);
                        });
                    }
                }, 100);
            }

            $(window).on('scroll', onScroll);

            // Also check on resize
            $(window).on('resize', onScroll);
        },        /**
         * Load more videos for infinite scroll
         */
        loadMoreForInfiniteScroll: function($loadMoreBtn, callback) {
            const channelId = $loadMoreBtn.data('channel') || '';
            const playlistId = $loadMoreBtn.data('playlist') || '';
            const offset = parseInt($loadMoreBtn.data('offset')) || 0;
            const count = parseInt($loadMoreBtn.data('count')) || 12;
            const targetId = $loadMoreBtn.data('target');
            const $container = $('#' + targetId);            $.ajax({
                url: eyss_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_load_more',
                    nonce: eyss_ajax.nonce,
                    channel_id: channelId,
                    playlist_id: playlistId,
                    offset: offset,
                    count: count
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.eyss-videos').append(response.data.html);

                        // Update button offset for next load
                        $loadMoreBtn.data('offset', offset + count);

                        // Update search count if search is active
                        const $searchInput = $container.find('.eyss-search-input');
                        if ($searchInput.length && $searchInput.val()) {
                            // Reapply search filter to new items
                            EYSS.handleSearch.call($searchInput[0]);
                        } else if (response.data.total_count !== undefined) {
                            const $totalCount = $container.find('.eyss-total-count');
                            if ($totalCount.length) {
                                $totalCount.text(response.data.total_count);
                            }
                        }

                        // Hide load more button if no more videos
                        if (response.data.has_more === false) {
                            $loadMoreBtn.hide();
                            // Remove scroll listener when no more content
                            $(window).off('scroll');
                        }
                    }
                },
                complete: function() {
                    callback();
                }
            });
        },

        /**
         * Show infinite scroll loading indicator
         */
        showInfiniteScrollLoader: function($container) {
            if (!$container.find('.eyss-infinite-loader').length) {
                const loader = $('<div class="eyss-infinite-loader" style="text-align: center; padding: 20px; font-size: 14px; color: #666;"><span>Loading more videos...</span></div>');
                $container.append(loader);
            }
        },

        /**
         * Hide infinite scroll loading indicator
         */
        hideInfiniteScrollLoader: function($container) {
            $container.find('.eyss-infinite-loader').remove();
        },

        /**
         * Initialize search functionality
         */
        initializeSearch: function() {
            // Bind keyup event for search inputs
            $(document).on('keyup input', '.eyss-search-input', this.debounce(this.handleSearch, 300));

            // Show/hide clear button based on input content
            $(document).on('input', '.eyss-search-input', this.toggleClearButton);

            // Bind click event for clear button
            $(document).on('click', '.eyss-search-clear', this.clearSearch);

            // Bind click event for load more buttons
            $(document).on('click', '.eyss-load-more-btn', this.handleLoadMore);
        },

        /**
         * Handle search functionality
         */
        handleSearch: function() {
            const $input = $(this);
            const searchTerm = $input.val().toLowerCase().trim();
            const targetId = $input.data('target');
            const $container = $('#' + targetId);

            // Handle completely empty search - same as clear button
            if (searchTerm === '') {
                EYSS.handleEmptySearch($container);
                return;
            }

            // For better performance with large datasets, use both client-side and server-side search
            if (searchTerm.length >= 3) {
                // Use AJAX search for terms 3+ characters for better accuracy
                EYSS.performAjaxSearch($input, searchTerm, $container);
            } else {
                // Use client-side filtering for short terms
                EYSS.performClientSearch($input, searchTerm, $container);
            }
        },

        /**
         * Perform client-side search (for short terms or when no server search needed)
         */
        performClientSearch: function($input, searchTerm, $container) {
            // Store original count if not already stored
            if (!$container.data('original-count')) {
                $container.data('original-count', $container.find('.eyss-total-count').text());
            }

            const $videoItems = $container.find('.eyss-video-item');
            let visibleCount = 0;

            // Note: Empty search is handled by handleEmptySearch function
            // Filter videos by title
            $videoItems.each(function() {
                const $item = $(this);
                const videoTitle = $item.data('video-title') || '';

                if (videoTitle.includes(searchTerm)) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                $container.find('.eyss-no-results').show();
            } else {
                $container.find('.eyss-no-results').hide();
            }

            // Hide load more button during client-side search (filtered results don't support pagination)
            const $loadMoreContainer = $container.find('.eyss-load-more-container');
            if ($loadMoreContainer.length && !$container.data('original-load-more-state')) {
                $container.data('original-load-more-state', $loadMoreContainer.is(':visible'));
            }
            $loadMoreContainer.hide();

            // This is client-side filtering, not AJAX replacement
            $container.data('search-performed', false);

            // Update total count display
            $container.find('.eyss-total-count').text(visibleCount);

            // Update carousel if needed
            if ($container.hasClass('eyss-layout-carousel')) {
                EYSS.updateCarouselAfterSearch($container);
            }
        },

        /**
         * Perform AJAX-based search for more accurate results
         */
        performAjaxSearch: function($input, searchTerm, $container) {
            // Store original content if not already stored
            if (!$container.data('original-content')) {
                $container.data('original-content', $container.find('.eyss-videos').html());
                $container.data('original-count', $container.find('.eyss-total-count').text());
            }

            // Show loading state
            $container.find('.eyss-videos').addClass('eyss-loading');

            // Extract search parameters from container data or input data
            const channelId = $container.data('channel') || '';
            const playlistId = $container.data('playlist') || '';
            const playlists = $container.data('playlists') || '';
            const excludePlaylist = $container.data('exclude-playlist') || '';
            const layout = $container.data('layout') || 'grid';

            $.ajax({
                url: eyss_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_search_videos',
                    nonce: eyss_ajax.nonce,
                    search: searchTerm,
                    channel_id: channelId,
                    playlist_id: playlistId,
                    playlists: playlists,
                    exclude_playlist: excludePlaylist,
                    layout: layout,
                    count: 50 // Search more results
                },
                success: function(response) {
                    if (response.success) {
                        // Mark that search was performed
                        $container.data('search-performed', true);

                        // Replace video grid with search results
                        $container.find('.eyss-videos').html(response.data.html);
                        $container.find('.eyss-total-count').text(response.data.count);

                        // Hide load more button during search (search results are complete)
                        const $loadMoreContainer = $container.find('.eyss-load-more-container');
                        if ($loadMoreContainer.length && !$container.data('original-load-more-state')) {
                            $container.data('original-load-more-state', $loadMoreContainer.is(':visible'));
                        }
                        $loadMoreContainer.hide();

                        if (response.data.count === 0) {
                            $container.find('.eyss-no-results').show();
                        } else {
                            $container.find('.eyss-no-results').hide();
                        }

                        // Update carousel if needed
                        if ($container.hasClass('eyss-layout-carousel')) {
                            EYSS.updateCarouselAfterSearch($container);
                        }
                    } else {
                        // Fallback to client-side search
                        EYSS.performClientSearch($input, searchTerm, $container);
                    }
                },
                error: function() {
                    // Fallback to client-side search
                    EYSS.performClientSearch($input, searchTerm, $container);
                },
                complete: function() {
                    $container.find('.eyss-videos').removeClass('eyss-loading');
                }
            });
        },

        /**
         * Handle empty search (when user deletes all text)
         */
        handleEmptySearch: function($container) {
            // Check if we need to reload original content via AJAX
            if ($container.data('search-performed')) {
                // Reload original videos
                EYSS.reloadOriginalVideos($container);
            } else {
                // Just show all currently loaded videos
                $container.find('.eyss-video-item').show();
                $container.find('.eyss-no-results').hide();

                // Restore load more button state
                const originalLoadMoreState = $container.data('original-load-more-state');
                if (originalLoadMoreState !== undefined) {
                    const $loadMoreContainer = $container.find('.eyss-load-more-container');
                    if (originalLoadMoreState) {
                        $loadMoreContainer.show();
                    } else {
                        $loadMoreContainer.hide();
                    }
                }

                // Update counts - restore original count if available
                const originalCount = $container.data('original-count');
                if (originalCount) {
                    $container.find('.eyss-total-count').text(originalCount);
                } else {
                    // Fallback to current loaded items if original count not stored
                    const totalItems = $container.find('.eyss-video-item').length;
                    $container.find('.eyss-total-count').text(totalItems);
                }

                // Update carousel if needed
                if ($container.hasClass('eyss-layout-carousel')) {
                    EYSS.updateCarouselAfterSearch($container);
                }
            }
        },

        /**
         * Reload original videos (before search)
         */
        reloadOriginalVideos: function($container) {
            const originalContent = $container.data('original-content');
            const originalCount = $container.data('original-count');

            if (originalContent) {
                // Restore original content
                $container.find('.eyss-videos').html(originalContent);
                $container.find('.eyss-total-count').text(originalCount);
                $container.find('.eyss-no-results').hide();

                // Restore load more button state
                const originalLoadMoreState = $container.data('original-load-more-state');
                if (originalLoadMoreState !== undefined) {
                    const $loadMoreContainer = $container.find('.eyss-load-more-container');
                    if (originalLoadMoreState) {
                        $loadMoreContainer.show();
                    } else {
                        $loadMoreContainer.hide();
                    }
                }

                // Clear search performed flag
                $container.data('search-performed', false);

                // Update carousel if needed
                if ($container.hasClass('eyss-layout-carousel')) {
                    EYSS.setupSplideCarousel($container.find('.eyss-carousel-splide'));
                }
            } else {
                // Fallback: reload page content via AJAX
                location.reload();
            }
        },        /**
         * Toggle clear button visibility
         */
        toggleClearButton: function() {
            const $input = $(this);
            const $clearButton = $input.siblings('.eyss-search-clear');

            if ($input.val().trim()) {
                $clearButton.show();
            } else {
                $clearButton.hide();
            }
        },

        /**
         * Clear search input and reset results
         */
        clearSearch: function(e) {
            e.preventDefault();

            const $button = $(this);
            const $input = $button.siblings('.eyss-search-input');
            const targetId = $input.data('target');
            const $container = $('#' + targetId);

            // Clear input
            $input.val('');

            // Hide clear button
            $button.hide();

            // Check if we need to reload original content via AJAX
            if ($container.data('search-performed')) {
                // Reload original videos via AJAX
                EYSS.reloadOriginalVideos($container);
            } else {
                // Just show all currently loaded videos
                $container.find('.eyss-video-item').show();
                $container.find('.eyss-no-results').hide();

                // Restore load more button state
                const originalLoadMoreState = $container.data('original-load-more-state');
                if (originalLoadMoreState !== undefined) {
                    const $loadMoreContainer = $container.find('.eyss-load-more-container');
                    if (originalLoadMoreState) {
                        $loadMoreContainer.show();
                    } else {
                        $loadMoreContainer.hide();
                    }
                }

                // Update counts
                const totalItems = $container.find('.eyss-video-item').length;
                $container.find('.eyss-total-count').text(totalItems);

                // Update carousel if needed
                if ($container.hasClass('eyss-layout-carousel')) {
                    EYSS.updateCarouselAfterSearch($container);
                }
            }

            // Focus back to input
            $input.focus();
        },        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        EYSS.init();
    });

    // Add CSS class to body when modal is open (for scroll prevention)
    $(document).ready(function() {
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .eyss-modal-open {
                    overflow: hidden;
                }
            `)
            .appendTo('head');
    });

})(jQuery);