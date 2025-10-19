/**
 * Embed YouTube Shorts - Admin JavaScript
 */

(function($) {
    'use strict';

    const EYSSAdmin = {

        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Test API connection button
            $('#eyss-test-api').on('click', this.testApiConnection);

            // Debug videos button
            $('#eyss-debug-videos').on('click', this.debugVideos);

            // Form validation
            $('#eyss-settings-form form').on('submit', this.validateForm);

            // Real-time validation
            $('#api_key, #channel_id').on('blur', this.validateField);

            // Channel ID format helper
            $('#channel_id').on('input', this.formatChannelId);

            // Copy shortcode functionality
            $('.eyss-copy-shortcode').on('click', this.copyShortcode);

            // Toggle advanced settings
            $('.eyss-toggle-advanced').on('click', this.toggleAdvanced);

            // Video import functionality
            $('#eyss-import-btn').on('click', this.startImport);
            $('#eyss-force-import-btn').on('click', this.startForceImport);
        },

        /**
         * Test API connection
         */
        testApiConnection: function(e) {
            e.preventDefault();

            const $button = $(this);
            const $result = $('#eyss-api-test-result');
            const apiKey = $('#api_key').val().trim();
            const channelId = $('#channel_id').val().trim();

            if (!apiKey) {
                EYSSAdmin.showTestResult('error', 'Please enter an API key first.');
                return;
            }

            // Show loading state
            $button.prop('disabled', true).addClass('loading');
            const originalText = $button.text();
            $button.text('Testing...');
            EYSSAdmin.showTestResult('loading', 'Testing API connection...');

            // Make AJAX request
            $.ajax({
                url: eyss_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_test_api',
                    nonce: eyss_admin.nonce,
                    api_key: apiKey,
                    channel_id: channelId
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    console.log('API Test Response:', response);
                    if (response.success) {
                        EYSSAdmin.showTestResult('success', response.data);
                    } else {
                        EYSSAdmin.showTestResult('error', response.data || 'Unknown error occurred.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('API Test Error:', {xhr: xhr, status: status, error: error});
                    let errorMessage = 'Connection failed. ';

                    if (status === 'timeout') {
                        errorMessage += 'Request timed out.';
                    } else if (xhr.status === 0) {
                        errorMessage += 'Network error.';
                    } else if (xhr.status === 403) {
                        errorMessage += 'Access denied. Check your API key permissions.';
                    } else if (xhr.status === 400) {
                        errorMessage += 'Bad request. Check your API key and Channel ID format.';
                    } else {
                        errorMessage += `HTTP ${xhr.status}: ${error}`;
                        if (xhr.responseText) {
                            console.log('Response Text:', xhr.responseText);
                        }
                    }

                    EYSSAdmin.showTestResult('error', errorMessage);
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).removeClass('loading');
                    $button.text(originalText || 'Test Connection');
                }
            });
        },

        /**
         * Debug videos
         */
        debugVideos: function(e) {
            e.preventDefault();

            const $button = $(this);
            const $result = $('#eyss-debug-result');
            const apiKey = $('#api_key').val().trim();
            const channelId = $('#channel_id').val().trim();

            if (!apiKey) {
                EYSSAdmin.showDebugResult('error', 'Please enter an API key first.');
                return;
            }

            if (!channelId) {
                EYSSAdmin.showDebugResult('error', 'Please enter a Channel ID first.');
                return;
            }

            // Show loading state
            $button.prop('disabled', true).addClass('loading');
            const originalText = $button.text();
            $button.text('Debugging...');
            EYSSAdmin.showDebugResult('loading', 'Analyzing channel videos...');

            // Make AJAX request
            $.ajax({
                url: eyss_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_debug_videos',
                    nonce: eyss_admin.nonce,
                    api_key: apiKey,
                    channel_id: channelId
                },
                timeout: 60000, // 60 seconds timeout for debugging
                success: function(response) {
                    console.log('Debug Videos Response:', response);
                    if (response.success) {
                        EYSSAdmin.showDebugResult('success', EYSSAdmin.formatDebugResult(response.data));
                    } else {
                        EYSSAdmin.showDebugResult('error', response.data || 'Unknown error occurred during debugging.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Debug Videos Error:', {xhr: xhr, status: status, error: error});
                    let errorMessage = 'Debug request failed. ';

                    if (status === 'timeout') {
                        errorMessage += 'Request timed out.';
                    } else if (xhr.status === 0) {
                        errorMessage += 'Network error.';
                    } else {
                        errorMessage += `HTTP ${xhr.status}: ${error}`;
                    }

                    EYSSAdmin.showDebugResult('error', errorMessage);
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).removeClass('loading');
                    $button.text(originalText);
                }
            });
        },

        /**
         * Show API test result
         */
        showTestResult: function(type, message) {
            const $result = $('#eyss-api-test-result');

            $result
                .removeClass('success error loading')
                .addClass(type)
                .html(message)
                .fadeIn();

            // Auto-hide success/error messages after 5 seconds
            if (type === 'success' || type === 'error') {
                setTimeout(() => {
                    $result.fadeOut();
                }, 5000);
            }
        },

        /**
         * Show debug result
         */
        showDebugResult: function(type, message) {
            const $result = $('#eyss-debug-result');

            $result
                .removeClass('success error loading')
                .addClass(type)
                .html(message)
                .fadeIn();

            // Auto-hide loading messages but keep success/error visible
            if (type === 'loading') {
                setTimeout(() => {
                    if ($result.hasClass('loading')) {
                        $result.fadeOut();
                    }
                }, 30000);
            }
        },

        /**
         * Format debug result for display
         */
        formatDebugResult: function(data) {
            let html = '<div style="background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 10px;">';

            html += '<h4 style="margin-top: 0;">Debug Results:</h4>';
            html += '<p><strong>Channel ID:</strong> ' + data.channel_id + '</p>';
            html += '<p><strong>Uploads Playlist:</strong> ' + data.uploads_playlist + '</p>';
            html += '<p><strong>Total Videos Checked:</strong> ' + data.total_videos_checked + '</p>';
            html += '<p><strong>Shorts Found:</strong> ' + data.shorts_found + '</p>';

            if (data.videos && data.videos.length > 0) {
                html += '<h5>Recent Videos Analysis:</h5>';
                html += '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background: white;">';

                data.videos.forEach(function(video) {
                    const shortLabel = video.is_short ?
                        '<span style="color: green; font-weight: bold;">✓ SHORT</span>' :
                        '<span style="color: red;">✗ NOT SHORT</span>';

                    html += '<div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">';
                    html += '<div><strong>' + video.title + '</strong></div>';
                    html += '<div>Duration: ' + video.duration_string + ' (' + video.duration_seconds + ' seconds) ' + shortLabel + '</div>';
                    html += '<div style="font-size: 11px; color: #666;">Published: ' + video.published_at + ' | ID: ' + video.id + '</div>';
                    html += '</div>';
                });

                html += '</div>';
            }

            html += '</div>';

            return html;
        },

        /**
         * Validate form before submission
         */
        validateForm: function(e) {
            const apiKey = $('#api_key').val().trim();
            const channelId = $('#channel_id').val().trim();
            let isValid = true;

            // Clear previous errors
            $('.eyss-field-error').remove();

            // Validate API key
            if (!apiKey) {
                EYSSAdmin.showFieldError('#api_key', 'API key is required.');
                isValid = false;
            } else if (apiKey.length < 10) {
                EYSSAdmin.showFieldError('#api_key', 'API key appears to be invalid.');
                isValid = false;
            }

            // Validate Channel ID
            if (!channelId) {
                EYSSAdmin.showFieldError('#channel_id', 'Channel ID is required.');
                isValid = false;
            } else if (!EYSSAdmin.isValidChannelId(channelId)) {
                EYSSAdmin.showFieldError('#channel_id', 'Channel ID format is invalid. Should start with UC and be 24 characters long.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();

                // Scroll to first error
                const $firstError = $('.eyss-field-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
            }

            return isValid;
        },

        /**
         * Validate individual field
         */
        validateField: function() {
            const $field = $(this);
            const fieldId = $field.attr('id');
            const value = $field.val().trim();

            // Remove existing error for this field
            $field.siblings('.eyss-field-error').remove();
            $field.removeClass('error');

            if (fieldId === 'api_key' && value && value.length < 10) {
                EYSSAdmin.showFieldError($field, 'API key appears to be invalid.');
            } else if (fieldId === 'channel_id' && value && !EYSSAdmin.isValidChannelId(value)) {
                EYSSAdmin.showFieldError($field, 'Invalid Channel ID format.');
            }
        },

        /**
         * Show field error
         */
        showFieldError: function(field, message) {
            const $field = $(field);
            const $error = $('<div class="eyss-field-error" style="color: #d63638; font-size: 12px; margin-top: 5px;">' + message + '</div>');

            $field.addClass('error').after($error);
        },

        /**
         * Format Channel ID as user types
         */
        formatChannelId: function() {
            const $field = $(this);
            let value = $field.val().replace(/[^a-zA-Z0-9_-]/g, '');

            // Ensure it starts with UC if user is typing a channel ID
            if (value.length > 0 && !value.startsWith('UC') && !value.startsWith('@')) {
                // Only auto-prepend UC if it looks like they're entering a channel ID
                if (value.match(/^[a-zA-Z0-9_-]{10,}$/)) {
                    value = 'UC' + value;
                }
            }

            // Limit length for channel IDs (UC + 22 characters = 24 total)
            if (value.startsWith('UC') && value.length > 24) {
                value = value.substring(0, 24);
            }

            $field.val(value);
        },

        /**
         * Check if Channel ID is valid format
         */
        isValidChannelId: function(channelId) {
            // YouTube Channel ID format: UC + 22 characters
            // Or handle format: @username
            return /^UC[a-zA-Z0-9_-]{22}$/.test(channelId) || /^@[a-zA-Z0-9._-]+$/.test(channelId);
        },

        /**
         * Copy shortcode to clipboard
         */
        copyShortcode: function(e) {
            e.preventDefault();

            const shortcode = $(this).data('shortcode') || '[youtube_shorts]';

            // Create temporary textarea
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(shortcode).select();

            try {
                document.execCommand('copy');
                $(this).text('Copied!').addClass('copied');

                setTimeout(() => {
                    $(this).text('Copy').removeClass('copied');
                }, 2000);

            } catch (err) {
                console.error('Failed to copy shortcode:', err);
                alert('Failed to copy shortcode. Please copy manually: ' + shortcode);
            }

            $temp.remove();
        },

        /**
         * Toggle advanced settings
         */
        toggleAdvanced: function(e) {
            e.preventDefault();

            const $toggle = $(this);
            const $advanced = $('.eyss-advanced-settings');

            $advanced.slideToggle();

            if ($toggle.text().includes('Show')) {
                $toggle.text('Hide Advanced Settings');
            } else {
                $toggle.text('Show Advanced Settings');
            }
        },

        /**
         * Get WordPress nonce
         */
        getNonce: function() {
            // Get nonce from localized script
            if (typeof eyss_admin !== 'undefined' && eyss_admin.nonce) {
                return eyss_admin.nonce;
            }

            // Fallback: look for nonce in form
            const $nonceField = $('input[name="_wpnonce"]');
            if ($nonceField.length) {
                return $nonceField.val();
            }

            return '';
        },        /**
         * Show notification
         */
        showNotification: function(message, type = 'info') {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');

            $('.wrap > h1').after($notice);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut();
            }, 5000);
        },

        /**
         * Initialize tooltips (if needed)
         */
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltip = $element.data('tooltip');

                $element.attr('title', tooltip);
            });
        },

        /**
         * Start video import
         */
        startImport: function(e) {
            e.preventDefault();
            EYSSAdmin.doImport(false);
        },

        /**
         * Start force import (re-import existing videos)
         */
        startForceImport: function(e) {
            e.preventDefault();

            if (!confirm('This will re-import all videos, including existing ones. Continue?')) {
                return;
            }

            EYSSAdmin.doImport(true);
        },

        /**
         * Perform the import
         */
        doImport: function(forceRefresh = false) {
            const $importBtn = $('#eyss-import-btn');
            const $forceBtn = $('#eyss-force-import-btn');
            const $progress = $('#eyss-import-progress');

            // Disable buttons
            $importBtn.prop('disabled', true).text('Importing...');
            $forceBtn.prop('disabled', true);

            // Show progress container and reset progress bar
            $progress.show();
            $('#eyss-progress-status').text('Starting import process...');
            $('#eyss-progress-details').text('This may take a few minutes...');
            $('#eyss-progress-bar').css('width', '0%');

            // Start background import
            $.ajax({
                url: eyss_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_import_videos',
                    nonce: EYSSAdmin.getNonce(),
                    force_refresh: forceRefresh
                },
                timeout: 30000, // 30 seconds for starting the process
                success: function(response) {
                    if (response.success && response.data.progress_key) {
                        // Import started successfully, begin polling for progress
                        $('#eyss-progress-status').text('Import started successfully. Monitoring progress...');
                        EYSSAdmin.pollImportProgress(response.data.progress_key);
                    } else {
                        $('#eyss-progress-status').text('Import failed to start');
                        EYSSAdmin.showNotification('Import failed to start: ' + (response.data || 'Unknown error'), 'error');
                        EYSSAdmin.resetImportUI();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Failed to start import';
                    if (status === 'timeout') {
                        errorMsg = 'Request timed out while starting import';
                    }

                    $('#eyss-progress-status').text('Import failed to start');
                    EYSSAdmin.showNotification(errorMsg, 'error');
                    EYSSAdmin.resetImportUI();
                }
            });
        },        /**
         * Start chunked import process
         */
        startChunkedImport: function(channelId, forceRefresh = false) {
            const $status = $('#eyss-progress-status');
            const $details = $('#eyss-progress-details');

            $status.text('Initializing import...');
            $details.text('');

            // Process first chunk
            EYSSAdmin.processNextChunk(channelId, forceRefresh);
        },

        /**
         * Process next chunk of videos
         */
        processNextChunk: function(channelId, forceRefresh = false) {
            $.ajax({
                url: eyss_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'eyss_import_videos',
                    nonce: EYSSAdmin.getNonce(),
                    channel_id: channelId,
                    force_refresh: forceRefresh,
                    chunk_mode: true
                },
                timeout: 60000, // 1 minute timeout per chunk
                success: function(response) {
                    if (response.success) {
                        const result = response.data;

                        // Update progress UI
                        EYSSAdmin.updateChunkProgress(result);

                        if (result.has_more && result.status !== 'error') {
                            // Process next chunk after short delay
                            setTimeout(function() {
                                EYSSAdmin.processNextChunk(channelId, forceRefresh);
                            }, 1000); // 1 second delay between chunks
                        } else {
                            // Import completed
                            EYSSAdmin.completeChunkedImport(result);
                        }
                    } else {
                        EYSSAdmin.showNotification('Import chunk failed: ' + (response.data || 'Unknown error'), 'error');
                        EYSSAdmin.resetImportUI();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Import chunk failed';
                    if (status === 'timeout') {
                        errorMsg += ' (timeout)';
                    }
                    EYSSAdmin.showNotification(errorMsg + '. Please try again.', 'error');
                    EYSSAdmin.resetImportUI();
                }
            });
        },

        /**
         * Update chunked import progress
         */
        updateChunkProgress: function(result) {
            const $status = $('#eyss-progress-status');
            const $details = $('#eyss-progress-details');

            if (result.status === 'error') {
                $status.text('Error: ' + result.message);
                return;
            }

            $status.text(result.message || 'Processing videos...');
            $details.text(`Processed: ${result.processed || 0} | Imported: ${result.imported || 0}`);

            // Update progress bar if we have total count
            if (result.total && result.processed) {
                const percentage = Math.min((result.processed / result.total) * 100, 100);
                $('#eyss-progress-bar').css('width', percentage + '%');
            }
        },

        /**
         * Complete chunked import
         */
        completeChunkedImport: function(result) {
            const $status = $('#eyss-progress-status');
            const $results = $('#eyss-progress-results');
            const $stats = $('#eyss-import-stats');

            if (result.status === 'error') {
                $status.text('Import failed: ' + result.message);
                EYSSAdmin.showNotification('Import failed. Please check your settings and try again.', 'error');
            } else {
                $status.text('Import completed successfully!');
                $('#eyss-progress-bar').css('width', '100%');

                // Show results
                $stats.empty();
                $stats.append(`<li>Videos processed: ${result.processed || 0}</li>`);
                $stats.append(`<li>Shorts imported: ${result.imported || 0}</li>`);

                $results.show();

                EYSSAdmin.showNotification('Video import completed successfully!', 'success');

                // Reload page after delay to show updated count
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }

            EYSSAdmin.resetImportUI();
        },

        /**
         * Poll import progress
         */
        pollImportProgress: function(progressKey) {
            let pollCount = 0;
            const maxPolls = 150; // 5 minutes at 2-second intervals

            const pollInterval = setInterval(function() {
                pollCount++;

                // Stop polling after maximum attempts
                if (pollCount > maxPolls) {
                    clearInterval(pollInterval);
                    $('#eyss-progress-status').text('Import monitoring timed out');
                    EYSSAdmin.showNotification('Import monitoring timed out. The import may still be running in the background.', 'warning');
                    EYSSAdmin.resetImportUI();
                    return;
                }

                $.ajax({
                    url: eyss_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'eyss_get_import_progress',
                        nonce: EYSSAdmin.getNonce(),
                        progress_key: progressKey
                    },
                    success: function(response) {
                        if (response.success) {
                            const progress = response.data;
                            EYSSAdmin.updateImportProgress(progress);

                            if (progress.status === 'completed' || progress.status === 'failed' || progress.status === 'error') {
                                clearInterval(pollInterval);
                                EYSSAdmin.completeImport(progress);
                            }
                        } else {
                            // If no progress found, maybe import hasn't started yet or finished
                            if (pollCount < 10) {
                                // Keep trying for first 20 seconds
                                return;
                            }

                            clearInterval(pollInterval);
                            EYSSAdmin.showNotification('Failed to get import progress: ' + (response.data || 'Unknown error'), 'error');
                            EYSSAdmin.resetImportUI();
                        }
                    },
                    error: function(xhr, status, error) {
                        if (pollCount < 5) {
                            // Retry first few times in case of temporary network issues
                            return;
                        }

                        clearInterval(pollInterval);
                        EYSSAdmin.showNotification('Progress polling failed: ' + error, 'error');
                        EYSSAdmin.resetImportUI();
                    }
                });
            }, 2000); // Poll every 2 seconds
        },

        /**
         * Update import progress UI
         */
        updateImportProgress: function(progress) {
            const $status = $('#eyss-progress-status');
            const $details = $('#eyss-progress-details');
            const $bar = $('#eyss-progress-bar');

            // Update status
            let statusText = 'Unknown status';
            switch (progress.status) {
                case 'starting':
                    statusText = 'Starting import...';
                    break;
                case 'fetching_playlist':
                    statusText = 'Getting video list...';
                    break;
                case 'fetching_videos':
                    statusText = 'Fetching videos from YouTube...';
                    break;
                case 'processing_videos':
                    statusText = 'Processing videos...';
                    break;
                default:
                    if (progress.status.startsWith('fetching_videos_page_')) {
                        const pageNum = progress.status.split('_').pop();
                        statusText = `Fetching videos (page ${pageNum})...`;
                    } else {
                        statusText = progress.status;
                    }
            }

            $status.text(statusText);

            // Update progress bar
            if (progress.total_videos > 0) {
                const percentage = (progress.processed_videos / progress.total_videos) * 100;
                $bar.css('width', percentage + '%');
            }

            // Update details
            let detailsText = '';
            if (progress.total_videos > 0) {
                detailsText += `Total videos: ${progress.total_videos} | `;
            }
            if (progress.processed_videos > 0) {
                detailsText += `Processed: ${progress.processed_videos} | `;
            }
            if (progress.fetched_videos > 0) {
                detailsText += `Fetched: ${progress.fetched_videos} | `;
            }
            if (progress.imported_videos > 0) {
                detailsText += `Imported: ${progress.imported_videos} | `;
            }
            if (progress.updated_videos > 0) {
                detailsText += `Updated: ${progress.updated_videos} | `;
            }
            if (progress.skipped_videos > 0) {
                detailsText += `Skipped: ${progress.skipped_videos}`;
            }

            $details.text(detailsText);
        },

        /**
         * Complete the import process
         */
        completeImport: function(progress) {
            const $results = $('#eyss-progress-results');
            const $stats = $('#eyss-import-stats');

            if (progress.status === 'completed') {
                $('#eyss-progress-status').text('Import completed successfully!');
                $('#eyss-progress-bar').css('width', '100%');

                // Show results
                $stats.empty();
                $stats.append(`<li>Total videos processed: ${progress.processed_videos}</li>`);
                $stats.append(`<li>New videos imported: ${progress.imported_videos}</li>`);
                $stats.append(`<li>Videos updated: ${progress.updated_videos}</li>`);
                $stats.append(`<li>Videos skipped: ${progress.skipped_videos}</li>`);

                if (progress.errors && progress.errors.length > 0) {
                    $stats.append(`<li style="color: #d63638;">Errors: ${progress.errors.length}</li>`);
                }

                $results.show();

                EYSSAdmin.showNotification('Video import completed successfully!', 'success');

                // Reload page after a delay to show updated count
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else {
                $('#eyss-progress-status').text('Import failed: ' + (progress.error_message || 'Unknown error'));
                EYSSAdmin.showNotification('Import failed. Please check your API settings and try again.', 'error');
            }

            EYSSAdmin.resetImportUI();
        },

        /**
         * Reset import UI to initial state
         */
        resetImportUI: function() {
            const $importBtn = $('#eyss-import-btn');
            const $forceBtn = $('#eyss-force-import-btn');

            $importBtn.prop('disabled', false).text('Import All Videos');
            $forceBtn.prop('disabled', false);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Check if localized variables are available
        if (typeof eyss_admin === 'undefined') {
            console.error('EYSS Admin: eyss_admin object not found. AJAX functionality may not work.');
        } else {
            console.log('EYSS Admin: Initialized with AJAX URL:', eyss_admin.ajax_url);
        }

        EYSSAdmin.init();
    });

    // Add some helpful CSS classes dynamically
    $(document).ready(function() {
        // Add loading button styles
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .button.loading {
                    position: relative;
                    pointer-events: none;
                }

                .eyss-field-error {
                    animation: eyss-shake 0.5s ease-in-out;
                }

                @keyframes eyss-shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }

                input.error,
                select.error,
                textarea.error {
                    border-color: #d63638 !important;
                    box-shadow: 0 0 0 1px #d63638 !important;
                }

                .eyss-copy-shortcode.copied {
                    background: #00a32a !important;
                    color: white !important;
                }

                #eyss-progress-bar {
                    transition: width 0.3s ease-in-out;
                    background: linear-gradient(to right, #00a32a, #108a00);
                    height: 20px;
                    border-radius: 10px;
                }

                .eyss-import-progress {
                    margin-top: 20px;
                }

                .eyss-import-progress .progress-container {
                    background: #f0f0f0;
                    border-radius: 10px;
                    overflow: hidden;
                    margin: 10px 0;
                }
            `)
            .appendTo('head');
    });

})(jQuery);