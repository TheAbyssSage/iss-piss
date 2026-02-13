<?php
/**
 * ISS Pee Telemetry Configuration
 * 
 * SECURITY NOTE: This file contains configuration constants.
 * Never expose sensitive credentials in client-side HTML or public repositories.
 */

// Remote telemetry endpoint URL
// Using local mock for testing - replace with real URL when available
// Real URL: https://gigazine.net/gsc_news/en/20241225-pissstream/
define('TELEMETRY_API_URL', 'http://localhost:9000');

// Refresh interval in seconds (how often the page auto-refreshes)
define('REFRESH_INTERVAL', 2);

// Threshold percentage for "almost full" warning (90%)
define('CRITICAL_THRESHOLD', 90);

// Cache file path for storing last successful telemetry result
// RELIABILITY: This allows the app to show stale data when API is unavailable
define('CACHE_FILE_PATH', __DIR__ . '/storage/last_tank_status.json');

// Timezone for displaying timestamps
define('DISPLAY_TIMEZONE', 'UTC');

// Cookie name for storing user's theme preference (astronaut mode)
define('THEME_COOKIE_NAME', 'iss_pee_theme');

// Session/cookie lifetime (7 days)
define('THEME_COOKIE_LIFETIME', 60 * 60 * 24 * 7);
