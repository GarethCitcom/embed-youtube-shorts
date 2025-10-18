# Embed YouTube Shorts Plugin# Embed YouTube Shorts Plugin



A comprehensive WordPress plugin that imports and displays YouTube Shorts using a custom post type system with playlist organization. Features advanced search, filtering, and multiple display layouts powered by the YouTube Data API v3.A comprehensive WordPress plugin that imports and displays YouTube Shorts using a custom post type system with playlist organization. Features advanced search, filtering, and multiple display layouts powered by the YouTube Data API v3.



## Features## Features



### 🎥 Video Management### 🎥 Video Management

- **Custom Post Type**: YouTube Shorts are imported as WordPress posts (`youtube_short`)- **Custom Post Type**: YouTube Shorts are imported as WordPress posts (`youtube_short`)

- **Playlist Taxonomy**: Automatic playlist detection and organization using WordPress taxonomies- **Playlist Taxonomy**: Automatic playlist detection and organization using WordPress taxonomies

- **Smart Import System**: Detects and imports videos from channel playlists automatically- **Smart Import System**: Detects and imports videos from channel playlists automatically

- **Automatic Cron Imports**: Optional daily auto-import with WordPress cron integration- **Automatic Cron Imports**: Optional daily auto-import with WordPress cron integration

- **Comprehensive Video Data**: Stores titles, descriptions, thumbnails, durations, view counts, and more- **Comprehensive Video Data**: Stores titles, descriptions, thumbnails, durations, view counts, and more

- **Local Storage**: All video data cached locally for fast loading and reduced API calls- **Local Storage**: All video data cached locally for fast loading and reduced API calls



### 🎨 Display & Layouts### 🎨 Display & Layouts

- **Multiple Layouts**: Display videos in grid, carousel (loop with center focus), or list layouts- **Multiple Layouts**: Display videos in grid, carousel, or list layouts

- **Responsive Design**: Perfectly optimized for desktop, tablet, and mobile devices- **Responsive Design**: Perfectly optimized for desktop, tablet, and mobile devices

- **Professional Carousel**: Infinite loop carousel with center focus using Splide.js library- **Modal Viewer**: Full-screen video player with YouTube embed

- **Modal Viewer**: Full-screen video player with YouTube embed- **Touch/Swipe Support**: Carousel navigation with touch gestures on mobile

- **Touch/Swipe Support**: Carousel navigation with touch gestures on mobile

### 🔍 Advanced Search & Filtering

### 🔍 Advanced Search & Filtering- **Real-time Search**: Live search with client-side and AJAX functionality

- **Real-time Search**: Live search with client-side and AJAX functionality (disabled for carousel)- **Playlist Filtering**: Filter videos by specific playlists or multiple playlists

- **Playlist Filtering**: Filter videos by specific playlists or multiple playlists- **Search with Clear Button**: Easy search clearing and state management

- **Search with Clear Button**: Easy search clearing and state management- **Accurate Result Counts**: Shows total available videos, not just loaded count

- **Accurate Result Counts**: Shows total available videos, not just loaded count- **Load More Support**: Choose between "Load More Button" or "Infinite Scroll" in admin settings

- **Load More Support**: Choose between "Load More Button" or "Infinite Scroll" in admin settings (disabled for carousel)- **Infinite Scroll**: Automatic content loading when scrolling near bottom of videos

- **Infinite Scroll**: Automatic content loading when scrolling near bottom of videos

### ⚡ Performance & Caching

### ⚡ Performance & Caching- **WordPress Integration**: Leverages WordPress post system for optimal performance

- **WordPress Integration**: Leverages WordPress post system for optimal performance- **Smart Caching**: Reduced API calls through local post storage

- **Smart Caching**: Reduced API calls through local post storage- **Efficient Queries**: Uses WordPress query system for fast video retrieval

- **Efficient Queries**: Uses WordPress query system for fast video retrieval- **Load More Pagination**: Loads additional videos without page refresh

- **Load More Pagination**: Loads additional videos without page refresh (grid/list only)

### 🛠 Developer Features

### 🛠 Developer Features- **Shortcode System**: Powerful shortcode with extensive parameters

- **Shortcode System**: Powerful shortcode with extensive parameters- **Taxonomy Support**: Uses WordPress taxonomy system for playlists

- **Taxonomy Support**: Uses WordPress taxonomy system for playlists- **Admin Integration**: Full WordPress admin integration with post management

- **Admin Integration**: Full WordPress admin integration with post management- **Debug Tools**: Built-in playlist testing and debugging functionality

- **Professional Libraries**: Splide.js v4.1.4 for carousel functionality

## Installation & Setup

## Installation & Setup

### 1. Install the Plugin

### 1. Install the Plugin- Upload the `embed-youtube-shorts` folder to your `/wp-content/plugins/` directory

- Upload the `embed-youtube-shorts` folder to your `/wp-content/plugins/` directory- Or install directly through the WordPress admin dashboard

- Or install directly through the WordPress admin dashboard- Activate the plugin via Plugins → Installed Plugins

- Activate the plugin via Plugins → Installed Plugins

### 2. Configure API Settings

### 2. Configure API Settings- Go to **Settings → YouTube Shorts** in your WordPress admin

- Go to **Settings → YouTube Shorts** in your WordPress admin- Add your **YouTube Data API v3 key** (see API setup guide below)

- Add your **YouTube Data API v3 key** (see API setup guide below)- Add the **Channel ID** you want to import videos from

- Add the **Channel ID** you want to import videos from- Click **"Test Connection"** to verify your settings

- Click **"Test Connection"** to verify your settings

### 3. Import Videos

### 3. Import Videos- Once API is configured, click **"Import Videos"** in the settings page

- Once API is configured, click **"Import Videos"** in the settings page- The plugin will automatically:

- The plugin will automatically:  - Fetch all videos from your channel

  - Fetch all videos from your channel  - Filter for Shorts (videos under 60 seconds)

  - Filter for Shorts (videos under 60 seconds)  - Detect playlist membership for each video

  - Detect playlist membership for each video  - Create WordPress posts for each Short

  - Create WordPress posts for each Short  - Organize videos using the playlist taxonomy

  - Organize videos using the playlist taxonomy- Progress is shown in real-time during import

- Progress is shown in real-time during import

### 3.1. Optional: Setup Automatic Imports

### 3.1. Optional: Setup Automatic ImportsFor hands-free video management:

For hands-free video management:- **Enable Auto-Import**: Check "Enable automatic daily video import" in settings

- **Enable Auto-Import**: Check "Enable automatic daily video import" in settings- **Set Import Time**: Choose when daily imports should run (server time)

- **Set Import Time**: Choose when daily imports should run (server time)- **Save Settings**: The plugin will automatically schedule daily imports

- **Save Settings**: The plugin will automatically schedule daily imports- **Benefits**: New videos are imported automatically without manual intervention

- **Benefits**: New videos are imported automatically without manual intervention

**Auto-Import Features:**

**Auto-Import Features:**- 🔄 **Daily Sync**: Checks for new videos every 24 hours

- 🔄 **Daily Sync**: Checks for new videos every 24 hours- ⚡ **Light Import**: Only imports new videos from the last day for efficiency

- ⚡ **Light Import**: Only imports new videos from the last day for efficiency- 📅 **Weekly Deep Sync**: Performs comprehensive import weekly to catch any missed videos

- 📅 **Weekly Deep Sync**: Performs comprehensive import weekly to catch any missed videos- 📝 **Automatic Logging**: All import activities logged for debugging

- 📝 **Automatic Logging**: All import activities logged for debugging- 🛑 **Easy Control**: Can be enabled/disabled anytime from settings

- 🛑 **Easy Control**: Can be enabled/disabled anytime from settings

### 4. Manage Content

### 4. Manage ContentAfter import, you can:

After import, you can:- **View Videos**: Go to **YouTube Shorts** in the admin menu

- **View Videos**: Go to **YouTube Shorts** in the admin menu- **Manage Playlists**: Access playlist taxonomy via **YouTube Shorts → Playlists**

- **Manage Playlists**: Access playlist taxonomy via **YouTube Shorts → Playlists**- **Test System**: Use the **"Test Playlists"** link on the settings page

- **Display Videos**: Use shortcodes on any page or post- **Display Videos**: Use shortcodes on any page or post



### 5. Display Videos### 5. Display Videos

Use the `[youtube_shorts]` shortcode anywhere in your content to display imported videos with full playlist filtering capabilities.Use the `[youtube_shorts]` shortcode anywhere in your content to display imported videos with full playlist filtering capabilities.



## Getting Your YouTube API Key## Getting Your YouTube API Key



1. **Visit Google Cloud Console**:1. **Visit Google Cloud Console**:

   - Go to [Google Cloud Console](https://console.cloud.google.com/)   - Go to [Google Cloud Console](https://console.cloud.google.com/)

   - Sign in with your Google account   - Sign in with your Google account



2. **Create or Select a Project**:2. **Create or Select a Project**:

   - Create a new project or select an existing one   - Create a new project or select an existing one

   - Make sure billing is enabled (required for API access)   - Make sure billing is enabled (required for API access)



3. **Enable YouTube Data API v3**:3. **Enable YouTube Data API v3**:

   - In the Cloud Console, go to APIs & Services → Library   - In the Cloud Console, go to APIs & Services → Library

   - Search for "YouTube Data API v3"   - Search for "YouTube Data API v3"

   - Click on it and press "Enable"   - Click on it and press "Enable"



4. **Create Credentials**:4. **Create Credentials**:

   - Go to APIs & Services → Credentials   - Go to APIs & Services → Credentials

   - Click "Create Credentials" → "API Key"   - Click "Create Credentials" → "API Key"

   - Copy the generated API key   - Copy the generated API key

   - (Optional) Restrict the key to YouTube Data API v3 for security   - (Optional) Restrict the key to YouTube Data API v3 for security



5. **Add API Key to Plugin**:5. **Add API Key to Plugin**:

   - In WordPress admin, go to Settings → YouTube Shorts   - In WordPress admin, go to Settings → YouTube Shorts

   - Paste your API key in the "YouTube API Key" field   - Paste your API key in the "YouTube API Key" field

   - Click "Test Connection" to verify it works   - Click "Test Connection" to verify it works



## Finding Your Channel ID## Finding Your Channel ID



### Method 1: From Channel URL### Method 1: From Channel URL

If your channel URL looks like: `https://www.youtube.com/channel/UCxxxxxxxxxxxxxxxxxxxxx`If your channel URL looks like: `https://www.youtube.com/channel/UCxxxxxxxxxxxxxxxxxxxxx`

- The part after `/channel/` is your Channel ID (starts with UC)- The part after `/channel/` is your Channel ID (starts with UC)



### Method 2: From Custom URL### Method 2: From Custom URL

If your channel has a custom URL like: `https://www.youtube.com/@YourChannelName`If your channel has a custom URL like: `https://www.youtube.com/@YourChannelName`

- You can use `@YourChannelName` as the Channel ID- You can use `@YourChannelName` as the Channel ID

- Or use online tools to convert it to the UC format- Or use online tools to convert it to the UC format



### Method 3: Using Browser Developer Tools### Method 3: Using Browser Developer Tools

1. Go to your YouTube channel page1. Go to your YouTube channel page

2. Right-click and select "View Page Source"2. Right-click and select "View Page Source"

3. Search for "channelId" or "externalId"3. Search for "channelId" or "externalId"

4. Copy the value (should start with UC and be 24 characters long)4. Copy the value (should start with UC and be 24 characters long)



## Finding Your Playlist ID## Finding Your Playlist ID



### Method 1: From Playlist URL### Method 1: From Playlist URL

If your playlist URL looks like: `https://www.youtube.com/playlist?list=PLxxxxxxxxxxxxxxxxxxxxx`If your playlist URL looks like: `https://www.youtube.com/playlist?list=PLxxxxxxxxxxxxxxxxxxxxx`

- The part after `list=` is your Playlist ID (starts with PL)- The part after `list=` is your Playlist ID (starts with PL)



### Method 2: From YouTube Studio### Method 2: From YouTube Studio

1. Go to YouTube Studio1. Go to YouTube Studio

2. Navigate to "Content" → "Playlists"2. Navigate to "Content" → "Playlists"

3. Click on your playlist3. Click on your playlist

4. The Playlist ID will be in the URL bar4. The Playlist ID will be in the URL bar



### Method 3: From Any Playlist Page### Method 3: From Any Playlist Page

1. Go to any YouTube playlist page1. Go to any YouTube playlist page

2. Look at the URL in your browser2. Look at the URL in your browser

3. Copy everything after `list=` (should start with PL and be about 34 characters long)3. Copy everything after `list=` (should start with PL and be about 34 characters long)



## Usage## Usage



### Basic Shortcode### Basic Shortcode

``````

[youtube_shorts][youtube_shorts]

``````

Displays all imported YouTube Shorts using default settings.Displays all imported YouTube Shorts using default settings.



### How It Works### How It Works



The plugin uses a **two-phase approach**:The plugin uses a **two-phase approach**:



1. **Import Phase**: Videos are imported as WordPress posts with playlist taxonomy1. **Import Phase**: Videos are imported as WordPress posts with playlist taxonomy

2. **Display Phase**: Shortcodes query the local WordPress database for fast display2. **Display Phase**: Shortcodes query the local WordPress database for fast display



**Benefits of This Approach:****Benefits of This Approach:**

- ⚡ **Lightning Fast**: No API calls during page load- ⚡ **Lightning Fast**: No API calls during page load

- 🎯 **Advanced Filtering**: Use WordPress taxonomy system for playlist filtering- 🎯 **Advanced Filtering**: Use WordPress taxonomy system for playlist filtering

- 🔍 **Powerful Search**: Real-time search through imported video data- 🔍 **Powerful Search**: Real-time search through imported video data

- 📊 **Scalable**: Handles large video collections efficiently- 📊 **Scalable**: Handles large video collections efficiently

- 🛠 **Manageable**: Full WordPress admin integration- 🛠 **Manageable**: Full WordPress admin integration



## Playlist Integration### Playlist Filtering



### Automatic Playlist DetectionThe plugin automatically detects playlist membership during import:



When you import videos from a YouTube channel, the plugin automatically:```

[youtube_shorts playlist="my-cooking-videos"]

1. **Detects Playlists**: Finds all playlists associated with the channel```

2. **Assigns Videos**: Determines which videos belong to which playlists

3. **Creates Taxonomy Terms**: Creates WordPress taxonomy terms for each playlist**Supported Playlist Identifiers:**

4. **Links Videos**: Associates imported videos with their respective playlists- **YouTube Playlist ID**: `PLxxxxxxxxxxxxxxxxx` (from YouTube URLs)

- **Playlist Slug**: `my-cooking-videos` (WordPress-friendly version)

### Playlist Filtering- **Playlist Name**: `Cooking Basics` (exact playlist title)



The plugin automatically detects playlist membership during import:### Advanced Shortcode Examples

```

```<!-- Show videos from specific playlist -->

[youtube_shorts playlist="my-cooking-videos"][youtube_shorts playlist="cooking-basics" layout="grid" count="12"]

```

<!-- Multiple playlists -->

**Supported Playlist Identifiers:**[youtube_shorts playlists="beginner,intermediate,advanced"]

- **YouTube Playlist ID**: `PLxxxxxxxxxxxxxxxxx` (from YouTube URLs)

- **Playlist Slug**: `my-cooking-videos` (WordPress-friendly version)<!-- Exclude certain playlists -->

- **Playlist Name**: `Cooking Basics` (exact playlist title)[youtube_shorts exclude_playlist="private,drafts"]



### Basic Playlist Filtering<!-- Show playlist names on videos -->

[youtube_shorts show_playlists="true"]

```php

// Show videos from a specific playlist (using playlist ID)<!-- Search-enabled grid -->

[youtube_shorts playlist="PLxxx123456789"][youtube_shorts show_search="true" layout="grid"]

```

// Show videos from a specific playlist (using playlist slug)

[youtube_shorts playlist="my-cooking-videos"]### Available Parameters



// Show videos from multiple playlists| Parameter | Options | Default | Description |

[youtube_shorts playlists="cooking-basics,advanced-recipes,quick-meals"]|-----------|---------|---------|-------------|

| **Display Settings** |

// Exclude videos from specific playlists| `layout` | `grid`, `carousel`, `list` | From settings | Display layout |

[youtube_shorts exclude_playlist="private-videos,test-videos"]| `count` | 1-50 | From settings | Number of videos to show initially |

```| `autoplay` | `true`, `false` | `false` | Auto-play videos in modal |

| **Playlist Filtering** |

### Advanced Combinations| `playlist` | Playlist identifier | None | Show videos from specific playlist |

| `playlists` | Comma-separated list | None | Show videos from multiple playlists |

```php| `exclude_playlist` | Playlist identifier | None | Exclude specific playlist |

// Show videos from specific channel but exclude certain playlists| `exclude_playlists` | Comma-separated list | None | Exclude multiple playlists |

[youtube_shorts channel="UCxxxxx" exclude_playlist="drafts,unlisted"]| `show_playlists` | `true`, `false` | `false` | Display playlist names on videos |

| **Display Options** |

// Show videos from multiple playlists with custom layout| `show_title` | `true`, `false` | `true` | Show video titles |

[youtube_shorts playlists="tutorials,reviews" layout="carousel" count="8"]| `show_duration` | `true`, `false` | `true` | Show video duration |

| `show_views` | `true`, `false` | `true` | Show view counts |

// Display playlist information on each video| `show_date` | `true`, `false` | `true` | Show upload date |

[youtube_shorts show_playlists="true" show_title="true"]| **Search & Interaction** |

```| `show_search` | `true`, `false` | `false` | Enable search functionality |

| `search_placeholder` | Text string | `Search videos...` | Search input placeholder text |

### Advanced Shortcode Examples| `show_load_more` | `true`, `false` | `true` | Show load more button (behavior controlled by admin settings) |

```| **Legacy Support** |

<!-- Show videos from specific playlist -->| `channel` | Channel ID | From settings | Filters by channel (for backwards compatibility) |

[youtube_shorts playlist="cooking-basics" layout="grid" count="12"]

### Example Shortcodes

<!-- Multiple playlists -->

[youtube_shorts playlists="beginner,intermediate,advanced"]#### Basic Usage

```

<!-- Exclude certain playlists --><!-- All imported videos -->

[youtube_shorts exclude_playlist="private,drafts"][youtube_shorts]



<!-- Show playlist names on videos --><!-- Grid with search -->

[youtube_shorts show_playlists="true"][youtube_shorts layout="grid" show_search="true"]



<!-- Search-enabled grid (not available for carousel) --><!-- Carousel with autoplay -->

[youtube_shorts show_search="true" layout="grid"][youtube_shorts layout="carousel" autoplay="true"]

```

<!-- Carousel with 8 videos (carousel default count) -->

[youtube_shorts layout="carousel"]#### Playlist Filtering

``````

<!-- Single playlist by slug -->

### Available Parameters[youtube_shorts playlist="cooking-tutorials"]



| Parameter | Options | Default | Description |<!-- Single playlist by YouTube ID -->

|-----------|---------|---------|-------------|[youtube_shorts playlist="PLxxxxxxxxxxxxxxxxx"]

| **Display Settings** |

| `layout` | `grid`, `carousel`, `list` | From settings | Display layout |<!-- Multiple playlists -->

| `count` | 1-50 | From settings (8 for carousel) | Number of videos to show initially |[youtube_shorts playlists="beginner,intermediate,advanced"]

| `autoplay` | `true`, `false` | `false` | Auto-play videos in modal |

| **Playlist Filtering** |<!-- Exclude specific playlists -->

| `playlist` | Playlist identifier | None | Show videos from specific playlist |[youtube_shorts exclude_playlist="private,unlisted"]

| `playlists` | Comma-separated list | None | Show videos from multiple playlists |

| `exclude_playlist` | Playlist identifier | None | Exclude specific playlist |<!-- Show playlist names on videos -->

| `exclude_playlists` | Comma-separated list | None | Exclude multiple playlists |[youtube_shorts playlist="featured" show_playlists="true"]

| `show_playlists` | `true`, `false` | `false` | Display playlist names on videos |```

| **Display Options** |

| `show_title` | `true`, `false` | `true` | Show video titles |#### Advanced Examples

| `show_duration` | `true`, `false` | `true` | Show video duration |```

| `show_views` | `true`, `false` | `true` | Show view counts |<!-- Complete featured section -->

| `show_date` | `true`, `false` | `true` | Show upload date |[youtube_shorts

| **Search & Interaction** |    playlist="featured"

| `show_search` | `true`, `false` | `false` | Enable search functionality (disabled for carousel) |    layout="carousel"

| `search_placeholder` | Text string | `Search videos...` | Search input placeholder text |    count="8"

| **Legacy Support** |    show_playlists="true"

| `channel` | Channel ID | From settings | Filters by channel (for backwards compatibility) |    autoplay="true"]



### Example Shortcodes<!-- Searchable video library -->

[youtube_shorts

#### Basic Usage    layout="grid"

```    show_search="true"

<!-- All imported videos -->    search_placeholder="Search our video library..."

[youtube_shorts]    count="20"]



<!-- Grid with search --><!-- Minimal video list -->

[youtube_shorts layout="grid" show_search="true"][youtube_shorts

    layout="list"

<!-- Carousel with autoplay (infinite loop, center focus) -->    exclude_playlists="private,drafts"

[youtube_shorts layout="carousel" autoplay="true"]    show_views="false"

```    show_date="false"]



#### Playlist Filtering<!-- Mobile-optimized carousel -->

```[youtube_shorts

<!-- Single playlist by slug -->    layout="carousel"

[youtube_shorts playlist="cooking-tutorials"]    count="6"

    show_title="true"

<!-- Single playlist by YouTube ID -->    show_duration="true"]

[youtube_shorts playlist="PLxxxxxxxxxxxxxxxxx"]```



<!-- Multiple playlists -->## WordPress Integration

[youtube_shorts playlists="beginner,intermediate,advanced"]

### Custom Post Type: `youtube_short`

<!-- Exclude specific playlists -->Each imported video becomes a WordPress post with:

[youtube_shorts exclude_playlist="private,unlisted"]- **Post Title**: Video title from YouTube

- **Post Content**: Video description

<!-- Show playlist names on videos -->- **Featured Image**: Video thumbnail

[youtube_shorts playlist="featured" show_playlists="true"]- **Custom Fields**: Duration, view count, like count, YouTube URL, etc.

```- **Taxonomy**: Playlist assignments



#### Advanced Examples### Admin Management

```- **YouTube Shorts Menu**: Dedicated admin section for managing imported videos

<!-- Complete featured carousel section (8 videos default) -->- **Playlist Taxonomy**: Organized playlist management with WordPress taxonomy UI

[youtube_shorts- **Bulk Operations**: Edit, delete, or organize multiple videos at once

    playlist="featured"- **Standard WordPress Features**: Search, filtering, sorting in admin

    layout="carousel"

    show_playlists="true"### Playlist Taxonomy: `youtube_playlist`

    autoplay="true"]- **Automatic Detection**: Playlists discovered during import

- **Hierarchical Support**: Nested playlist organization if needed

<!-- Searchable video library (grid/list only) -->- **WordPress Integration**: Uses standard taxonomy management

[youtube_shorts- **Shortcode Filtering**: Filter videos by any playlist combination

    layout="grid"

    show_search="true"## Display Layouts

    search_placeholder="Search our video library..."

    count="20"]### Grid Layout

- Responsive grid system (1-4 columns based on screen size)

<!-- Minimal video list -->- Perfect for showcasing multiple videos

[youtube_shorts- Hover effects and smooth transitions

    layout="list"- Search integration with real-time filtering

    exclude_playlists="private,drafts"

    show_views="false"### Carousel Layout

    show_date="false"]- Horizontal scrolling with navigation arrows

- Touch/swipe support for mobile devices

<!-- Mobile-optimized carousel -->- Automatic responsive adjustment

[youtube_shorts- Great for featured content sections

    layout="carousel"

    count="6"### List Layout

    show_title="true"- Vertical layout with larger thumbnails

    show_duration="true"]- Extended video information display

```- Ideal for blog-style content

- Better for detailed video browsing

## WordPress Integration

## Customization

### Custom Post Type: `youtube_short`

Each imported video becomes a WordPress post with:### CSS Customization

- **Post Title**: Video title from YouTubeYou can override the plugin's styles by adding custom CSS to your theme:

- **Post Content**: Video description

- **Featured Image**: Video thumbnail```css

- **Custom Fields**: Duration, view count, like count, YouTube URL, etc./* Customize grid spacing */

- **Taxonomy**: Playlist assignments.eyss-videos-grid {

    gap: 30px;

### Admin Management}

- **YouTube Shorts Menu**: Dedicated admin section for managing imported videos

- **Playlist Taxonomy**: Organized playlist management with WordPress taxonomy UI/* Custom video item hover effect */

- **Bulk Operations**: Edit, delete, or organize multiple videos at once.eyss-video-item:hover {

- **Standard WordPress Features**: Search, filtering, sorting in admin    transform: scale(1.05);

}

### Playlist Taxonomy: `youtube_playlist`

- **Automatic Detection**: Playlists discovered during import/* Custom modal styling */

- **Hierarchical Support**: Nested playlist organization if needed.eyss-modal-content {

- **WordPress Integration**: Uses standard taxonomy management    border-radius: 20px;

- **Shortcode Filtering**: Filter videos by any playlist combination}

```

### Playlist Management

### Theme Integration

1. **View Playlists**: Go to YouTube Shorts > Playlists in the admin menuThe plugin uses CSS classes that follow BEM methodology:

2. **Edit Playlists**: Click on any playlist to edit its name, slug, or description- `.eyss-container` - Main container

3. **Assign Videos**: Edit any YouTube Short post to assign/remove playlist associations- `.eyss-video-item` - Individual video items

- `.eyss-videos-grid` - Grid layout container

### Post List Columns- `.eyss-videos-carousel` - Carousel layout container

- `.eyss-videos-list` - List layout container

The YouTube Shorts post list now includes a "Playlists" column showing which playlists each video belongs to.

## Performance & Caching

## Display Layouts

The plugin includes built-in caching to improve performance:

### Grid Layout- **API Response Caching**: YouTube API responses are cached to reduce API calls

- Responsive grid system (1-4 columns based on screen size)- **Configurable Cache Duration**: Set cache duration in plugin settings

- Perfect for showcasing multiple videos- **Automatic Cache Cleanup**: Expired cache entries are automatically removed

- Hover effects and smooth transitions- **Minimal API Usage**: Only fetches Shorts (videos under 60 seconds)

- Search integration with real-time filtering

- Load more button for pagination### Automatic Import System

The plugin uses WordPress's built-in cron system for automatic imports:

### Carousel Layout- **WordPress Cron Integration**: Uses `wp_schedule_event()` for reliable scheduling

- **Professional Splide.js Library**: Uses Splide.js v4.1.4 for smooth performance- **Smart Scheduling**: Automatically reschedules when settings change

- **Infinite Loop**: Seamless continuous scrolling in both directions- **Fail-safe Cleanup**: Removes scheduled tasks on plugin deactivation

- **Center Focus**: Active slide always centered for better visual hierarchy- **Logging System**: All auto-import activities are logged to WordPress error log

- **Responsive Configuration**: 4 slides desktop, 2 tablet, 1 mobile- **Dual Import Strategy**:

- **Touch/Swipe Support**: Full mobile gesture support  - Daily light sync (last 24 hours) for efficiency

- **No Search/Load More**: Optimized experience without distracting UI elements  - Weekly comprehensive sync to ensure no videos are missed

- **Default Count**: 8 videos for optimal carousel performance

## Troubleshooting

### List Layout

- Vertical layout with larger thumbnails### Setup Issues

- Extended video information display

- Ideal for blog-style content**"API key and Channel ID are required"**

- Better for detailed video browsing- Ensure both API key and Channel ID are entered in Settings → YouTube Shorts

- Load more button for pagination- Click "Test Connection" to verify API connectivity

- Check that your API key has YouTube Data API v3 enabled

## Customization

**Import fails or no videos imported**

### CSS Customization- Verify the channel has videos under 60 seconds (Shorts)

You can override the plugin's styles by adding custom CSS to your theme:- Check that the Channel ID is correct (should start with UC)

- Ensure API key has sufficient quota remaining

```css- Look for error messages in the import progress display

/* Customize grid spacing */

.eyss-videos-grid {**"No videos found" after successful import**

    gap: 30px;- Check **YouTube Shorts** admin menu to see if posts were created

}- Verify shortcode is used correctly: `[youtube_shorts]`

- Check if videos were filtered out by playlist parameters

/* Custom video item hover effect */

.eyss-video-item:hover {### Display Issues

    transform: scale(1.05);

}**Shortcode shows no content**

- Confirm videos were imported successfully (check admin menu)

/* Custom modal styling */- Verify shortcode syntax is correct

.eyss-modal-content {- Check if playlist filtering is too restrictive

    border-radius: 20px;- Use `[youtube_shorts]` without parameters to test

}

**Playlist filtering not working**

/* Carousel-specific styling */- Go to **YouTube Shorts → Playlists** to see available playlists

.eyss-layout-carousel .splide__arrow {- Use **Settings → Test Playlists** to verify playlist functionality

    background: #0073aa;- Check playlist identifier spelling (slug, name, or YouTube ID)

    border-radius: 50%;- Ensure playlists were detected during import

}

```**Search functionality not working**

- Check browser console for JavaScript errors

### Theme Integration- Verify jQuery is loaded by your theme

The plugin uses CSS classes that follow BEM methodology:- Clear browser cache and try again

- `.eyss-container` - Main container- Test with `show_search="true"` parameter

- `.eyss-video-item` - Individual video items

- `.eyss-videos-grid` - Grid layout container**Modal not opening**

- `.eyss-carousel-splide` - Splide carousel container- Check browser console for JavaScript errors

- `.eyss-videos-list` - List layout container- Ensure no conflicting modal plugins

- Verify theme compatibility with plugin JavaScript

## Performance & Caching

### Performance Issues

The plugin includes built-in caching to improve performance:

- **API Response Caching**: YouTube API responses are cached to reduce API calls**Slow page loading**

- **Configurable Cache Duration**: Set cache duration in plugin settings- Videos are loaded from local WordPress database (should be fast)

- **Automatic Cache Cleanup**: Expired cache entries are automatically removed- Check if other plugins are causing conflicts

- **Minimal API Usage**: Only fetches Shorts (videos under 60 seconds)- Consider reducing `count` parameter in shortcodes

- Optimize images if using custom thumbnails

### Automatic Import System

The plugin uses WordPress's built-in cron system for automatic imports:**"Load More" button not working**

- **WordPress Cron Integration**: Uses `wp_schedule_event()` for reliable scheduling- Check browser console for AJAX errors

- **Smart Scheduling**: Automatically reschedules when settings change- Verify WordPress AJAX is functioning properly

- **Fail-safe Cleanup**: Removes scheduled tasks on plugin deactivation- Test with different `count` values

- **Logging System**: All auto-import activities are logged to WordPress error log- Clear any caching plugins that might interfere

- **Dual Import Strategy**:

  - Daily light sync (last 24 hours) for efficiency### Management Issues

  - Weekly comprehensive sync to ensure no videos are missed

**Cannot edit imported videos**

## API Integration Details- Videos can be edited like regular WordPress posts

- Go to **YouTube Shorts** in admin menu

### YouTube API Methods- Use standard WordPress bulk edit features

The plugin includes comprehensive YouTube API integration:- Custom fields can be modified in post edit screen



#### Playlist Detection Methods**Auto-import not working**

- `get_all_channel_playlists()` - Fetch all playlists for a channel with pagination- Check that "Enable automatic daily video import" is checked in settings

- `get_video_playlist_membership()` - Check which playlists contain a specific video- Verify WordPress cron is functioning (some hosts disable it)

- `is_video_in_playlist()` - Boolean check for video-playlist membership- Look for "EYSS Daily Import" entries in error logs

- `get_playlist_videos()` - Get all videos from a specific playlist- Test by temporarily setting import time to a few minutes from now

- Ensure server time zone is configured correctly

#### Performance Optimizations- Consider using a plugin like "WP Crontrol" to monitor scheduled tasks

- **Caching System**: API responses cached to reduce quota usage

- **Batch Processing**: Videos processed in batches to prevent timeouts**Auto-import running at wrong time**

- **Pagination Support**: Handles large playlists with proper pagination- Check your server's time zone settings

- The import time setting uses server time, not your local time

### Database Schema- You can verify current server time on the settings page

```sql- Adjust the import time setting accordingly

-- Custom Post Type: youtube_short

-- Custom Taxonomy: youtube_playlist (hierarchical)### Getting Help

-- Meta Fields:

--   _eyss_video_id: YouTube video IDIf you encounter issues:

--   _eyss_channel_id: YouTube channel ID1. Check the WordPress debug log for errors

--   _eyss_thumbnail_url: Video thumbnail URL2. Test with a default WordPress theme

--   _eyss_published_at: Publication date3. Disable other plugins to check for conflicts

--   _eyss_duration: Video duration in seconds4. Verify your API key has the correct permissions

--   _eyss_view_count: View count5. Make sure your website can make outbound HTTPS requests

--   _eyss_like_count: Like count

--   _eyss_youtube_url: Full YouTube URL## Requirements

```

- **WordPress**: 5.0 or higher

## Example Use Cases- **PHP**: 7.4 or higher

- **YouTube Data API v3 Key**: Required for fetching videos

### 1. Recipe Website- **Internet Connection**: Required for API calls

```php- **HTTPS**: Recommended for security

// Show only breakfast recipes in carousel

[youtube_shorts playlist="breakfast-recipes" layout="carousel"]## Changelog



// Show all recipes except private ones with search### Version 2.2.3 - Minor Formatting Fix

[youtube_shorts exclude_playlist="private-recipes,drafts" show_playlists="true" show_search="true" layout="grid"]**🎨 Polish & Presentation**

```- Fixed formatting in update notifications to display proper line breaks instead of literal \n characters

- Improved presentation of changelog and instructions in WordPress admin update interface

### 2. Tutorial Channel

```php### Version 2.2.2 - Critical Search Fixes

// Beginner tutorials carousel (8 videos default)**🐛 Critical Bug Fixes**

[youtube_shorts playlist="beginner-tutorials" layout="carousel" show_title="true"]- Fixed search to properly filter within selected playlists instead of searching all videos

- Fixed total count restoration after clearing search (now shows original count, not loaded count)

// All tutorials with playlist labels- Enhanced data attribute passing for accurate playlist filtering in search

[youtube_shorts playlists="beginner,intermediate,advanced" show_playlists="true"]- Added support for all playlist filtering options (single, multiple, exclude) in search

```- Improved search behavior to match user expectations with proper scoping



### 3. Mixed Content Channel**🔧 Technical Improvements**

```php- Added missing playlist data attributes to shortcode container

// Everything except behind-the-scenes content- Enhanced JavaScript AJAX search parameters

[youtube_shorts exclude_playlist="behind-scenes,bloopers" count="20"]- Updated PHP search method with comprehensive playlist filtering

```- Fixed client-side search to store and restore original count properly



## Troubleshooting### Version 2.2.0 - Infinite Scroll Feature

**🚀 New Infinite Scroll Option**

### Setup Issues- Added setting to choose between "Load More Button" and "Infinite Scroll"

- Automatic content loading when scrolling near the bottom

**"API key and Channel ID are required"**- Smooth scroll behavior with loading indicators

- Ensure both API key and Channel ID are entered in Settings → YouTube Shorts- Throttled scroll detection for optimal performance

- Click "Test Connection" to verify API connectivity- Seamless integration with existing load more functionality

- Check that your API key has YouTube Data API v3 enabled

**🎛 Enhanced Admin Settings**

**Import fails or no videos imported**- New "Load More Type" setting with radio button options

- Verify the channel has videos under 60 seconds (Shorts)- Dynamic behavior based on user selection

- Check that the Channel ID is correct (should start with UC)- Clean UI integration in WordPress admin settings

- Ensure API key has sufficient quota remaining

- Look for error messages in the import progress display**🔧 Technical Improvements**

- Proper data attribute handling for scroll type configuration

**"No videos found" after successful import**- JavaScript scroll listener optimization

- Check **YouTube Shorts** admin menu to see if posts were created- CSS animations for loading states

- Verify shortcode is used correctly: `[youtube_shorts]`- Production-ready code with debugging support

- Check if videos were filtered out by playlist parameters

### Version 2.0.0 - Major Architecture Overhaul

### Display Issues**🎯 Complete Custom Post Type Implementation**

- Videos now imported as WordPress posts (`youtube_short` custom post type)

**Shortcode shows no content**- Local storage eliminates API calls during page display

- Confirm videos were imported successfully (check admin menu)- Full WordPress admin integration for video management

- Verify shortcode syntax is correct

- Check if playlist filtering is too restrictive**📁 Playlist Taxonomy System**

- Use `[youtube_shorts]` without parameters to test- Automatic playlist detection during import

- WordPress taxonomy system for playlist organization (`youtube_playlist`)

**Playlist filtering not working**- Advanced playlist filtering in shortcodes

- Go to **YouTube Shorts → Playlists** to see available playlists- Support for multiple playlist inclusion/exclusion

- Check playlist identifier spelling (slug, name, or YouTube ID)

- Ensure playlists were detected during import**🔍 Enhanced Search & Filtering**

- Real-time search with client-side and AJAX functionality

**Carousel not working**- Search with clear button and proper state management

- Check browser console for JavaScript errors- Accurate result counts (total vs. loaded)

- Verify Splide.js library is loading from CDN- Load more pagination with search integration

- Ensure no conflicting carousel plugins

- Test with different carousel count values**⚡ Performance Improvements**

- Lightning-fast display (no API calls during page load)

**Search functionality not working**- WordPress query optimization for large video collections

- Check browser console for JavaScript errors- Smart caching through local post storage

- Verify jQuery is loaded by your theme- Reduced API quota usage (import-once, display-many)

- Clear browser cache and try again

- Test with `show_search="true"` parameter**🛠 Developer Enhancements**

- Note: Search is disabled for carousel layout- Comprehensive shortcode parameters for playlist filtering

- WordPress taxonomy integration for advanced queries

**Modal not opening**- Debug and testing tools integrated into settings page

- Check browser console for JavaScript errors- Backwards compatibility maintained

- Ensure no conflicting modal plugins

- Verify theme compatibility with plugin JavaScript**🎨 UI/UX Improvements**

- Load more button with proper visibility management during search

### Performance Issues- Enhanced modal video player

- Improved responsive design

**Slow page loading**- Better touch/swipe support for mobile

- Videos are loaded from local WordPress database (should be fast)

- Check if other plugins are causing conflicts### Version 1.0.0 - Initial Release

- Consider reducing `count` parameter in shortcodes- Basic grid, carousel, and list layouts

- Optimize images if using custom thumbnails- Direct YouTube API integration

- Simple caching system

**"Load More" button not working**- Modal video player

- Check browser console for AJAX errors- Basic shortcode functionality

- Verify WordPress AJAX is functioning properly- Admin settings page

- Test with different `count` values

- Clear any caching plugins that might interfere## Support

- Note: Load More is disabled for carousel layout

For support and questions:

### Management Issues- Check the plugin documentation

- Review common troubleshooting steps

**Cannot edit imported videos**- Test with minimal configuration

- Videos can be edited like regular WordPress posts

- Go to **YouTube Shorts** in admin menu## License

- Use standard WordPress bulk edit features

- Custom fields can be modified in post edit screenThis plugin is licensed under the GPL v2 or later.



**Auto-import not working**---

- Check that "Enable automatic daily video import" is checked in settings

- Verify WordPress cron is functioning (some hosts disable it)**Note**: This plugin requires a YouTube Data API v3 key from Google Cloud Console. API usage may be subject to quotas and billing depending on your usage and Google Cloud account settings.
- Look for "EYSS Daily Import" entries in error logs
- Test by temporarily setting import time to a few minutes from now
- Ensure server time zone is configured correctly
- Consider using a plugin like "WP Crontrol" to monitor scheduled tasks

**Auto-import running at wrong time**
- Check your server's time zone settings
- The import time setting uses server time, not your local time
- You can verify current server time on the settings page
- Adjust the import time setting accordingly

**No Playlists Detected**
- Ensure your YouTube API key has proper permissions
- Check that the channel actually has public playlists
- Verify the videos are actually in playlists (not just channel uploads)

**Playlist Filter Not Working**
- Check the playlist slug/ID is correct
- Ensure videos have been imported with playlist detection enabled
- Verify playlist taxonomy terms exist in WordPress admin

### Getting Help

If you encounter issues:
1. Check the WordPress debug log for errors
2. Test with a default WordPress theme
3. Disable other plugins to check for conflicts
4. Verify your API key has the correct permissions
5. Make sure your website can make outbound HTTPS requests

## Technical Notes

- Playlists are stored as WordPress custom taxonomy terms
- The taxonomy is called `youtube_playlist`
- Playlist detection happens automatically during video import
- Videos can belong to multiple playlists
- Playlist filtering works with search, pagination, and AJAX loading
- Carousel layout uses Splide.js v4.1.4 for optimal performance

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **YouTube Data API v3 Key**: Required for fetching videos
- **Internet Connection**: Required for API calls and CDN assets (Splide.js)
- **HTTPS**: Recommended for security

## Changelog

### Version 2.2.3 - Carousel Enhancement & Cleanup
**🎨 Carousel Improvements**
- Upgraded carousel to use professional Splide.js v4.1.4 library
- Implemented infinite loop carousel with center focus for better UX
- Default count set to 8 videos for carousel layout (optimal performance)
- Disabled search and load more functionality for carousel (cleaner experience)
- Added responsive breakpoints (4 slides desktop, 2 tablet, 1 mobile)

**🧹 Code Cleanup**
- Removed all test and debug files from production build
- Consolidated documentation into single README.md
- Enhanced error handling and debugging capabilities
- Improved code organization and removed unused functionality

### Version 2.2.2 - Critical Search Fixes
**🐛 Critical Bug Fixes**
- Fixed search to properly filter within selected playlists instead of searching all videos
- Fixed total count restoration after clearing search (now shows original count, not loaded count)
- Enhanced data attribute passing for accurate playlist filtering in search
- Added support for all playlist filtering options (single, multiple, exclude) in search
- Improved search behavior to match user expectations with proper scoping

**🔧 Technical Improvements**
- Added missing playlist data attributes to shortcode container
- Enhanced JavaScript AJAX search parameters
- Updated PHP search method with comprehensive playlist filtering
- Fixed client-side search to store and restore original count properly

### Version 2.2.0 - Infinite Scroll Feature
**🚀 New Infinite Scroll Option**
- Added setting to choose between "Load More Button" and "Infinite Scroll"
- Automatic content loading when scrolling near the bottom
- Smooth scroll behavior with loading indicators
- Throttled scroll detection for optimal performance
- Seamless integration with existing load more functionality

**🎛 Enhanced Admin Settings**
- New "Load More Type" setting with radio button options
- Dynamic behavior based on user selection
- Clean UI integration in WordPress admin settings

**🔧 Technical Improvements**
- Proper data attribute handling for scroll type configuration
- JavaScript scroll listener optimization
- CSS animations for loading states
- Production-ready code with debugging support

### Version 2.0.0 - Major Architecture Overhaul
**🎯 Complete Custom Post Type Implementation**
- Videos now imported as WordPress posts (`youtube_short` custom post type)
- Local storage eliminates API calls during page display
- Full WordPress admin integration for video management

**📁 Playlist Taxonomy System**
- Automatic playlist detection during import
- WordPress taxonomy system for playlist organization (`youtube_playlist`)
- Advanced playlist filtering in shortcodes
- Support for multiple playlist inclusion/exclusion

**🔍 Enhanced Search & Filtering**
- Real-time search with client-side and AJAX functionality
- Search with clear button and proper state management
- Accurate result counts (total vs. loaded)
- Load more pagination with search integration

**⚡ Performance Improvements**
- Lightning-fast display (no API calls during page load)
- WordPress query optimization for large video collections
- Smart caching through local post storage
- Reduced API quota usage (import-once, display-many)

**🛠 Developer Enhancements**
- Comprehensive shortcode parameters for playlist filtering
- WordPress taxonomy integration for advanced queries
- Backwards compatibility maintained

**🎨 UI/UX Improvements**
- Load more button with proper visibility management during search
- Enhanced modal video player
- Improved responsive design
- Better touch/swipe support for mobile

### Version 1.0.0 - Initial Release
- Basic grid, carousel, and list layouts
- Direct YouTube API integration
- Simple caching system
- Modal video player
- Basic shortcode functionality
- Admin settings page

## Support

For support and questions:
- Check the plugin documentation
- Review common troubleshooting steps
- Test with minimal configuration

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: This plugin requires a YouTube Data API v3 key from Google Cloud Console. API usage may be subject to quotas and billing depending on your usage and Google Cloud account settings.