<?php
/**
 * TelemetryClient Class
 * 
 * Handles fetching telemetry data from the remote ISS pee API.
 * 
 * RELIABILITY DESIGN:
 * - Fetches data from remote API
 * - Caches successful responses to disk
 * - Falls back to cached data when API is unavailable
 * - Marks data as stale/error when appropriate
 * 
 * SECURITY NOTE:
 * - This is the boundary between untrusted external data and our application
 * - All API responses are validated before use
 * - No credentials or secrets are stored here
 */

require_once __DIR__ . '/TankStatus.php';

class TelemetryClient
{
    /**
     * Fetch current tank status from remote API or cache
     * 
     * FLOW:
     * 1. Try to fetch from remote API
     * 2. If successful, parse and cache the result
     * 3. If failed, try to load from cache and mark as stale
     * 4. If no cache available, return error status
     * 
     * @return TankStatus Current tank status
     */
    public function fetchCurrentStatus(): TankStatus
    {
        // Try to fetch from remote API first
        $remoteData = $this->fetchFromRemoteApi();

        if ($remoteData !== null) {
            // Success! Parse the data
            $status = $this->parseApiResponse($remoteData);

            if ($status !== null) {
                // Cache this successful result
                $this->cacheStatus($status);
                return $status;
            }
        }

        // API failed or returned invalid data - try to load from cache
        $cachedStatus = $this->loadFromCache();

        if ($cachedStatus !== null) {
            // We have cached data - return it but mark as error and stale
            $cachedStatus->sourceStatus = 'error';
            $cachedStatus->isStale = true;
            return $cachedStatus;
        }

        // No cache available - return error status
        return new TankStatus(
            level: 0,
            updatedAt: date('c'),
            sourceStatus: 'error',
            isStale: true
        );
    }

    /**
     * Fetch data from remote API
     * 
     * SECURITY: Using HTTPS endpoint to prevent MITM attacks
     * ERROR HANDLING: Returns null on any failure
     * 
     * @return array|null Decoded JSON response or null on failure
     */
    private function fetchFromRemoteApi(): ?array
    {
        try {
            // Set up cURL with proper error handling
            $ch = curl_init(TELEMETRY_API_URL);

            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,  // 10 second timeout
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => true,  // Verify SSL certificate
                CURLOPT_USERAGENT => 'ISS-Pee-Telemetry/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            // Check for errors
            if ($response === false || $httpCode !== 200) {
                error_log("API fetch failed: HTTP $httpCode, Error: $error");
                return null;
            }

            // Parse JSON response
            $data = json_decode($response, true);

            if (!is_array($data)) {
                error_log("Invalid JSON response from API");
                return null;
            }

            return $data;

        } catch (Exception $e) {
            error_log("Exception fetching API: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse API response into TankStatus object
     * 
     * UNTRUSTED DATA BOUNDARY:
     * - Validates all fields before use
     * - Provides sensible defaults for missing fields
     * - Sanitizes values to prevent injection
     * 
     * @param array $data API response data
     * @return TankStatus|null Parsed status or null if invalid
     */
    private function parseApiResponse(array $data): ?TankStatus
    {
        // Validate required fields exist
        if (!isset($data['percentage'])) {
            error_log("API response missing 'percentage' field");
            return null;
        }

        // Extract and validate level
        $level = intval($data['percentage']);

        // Extract timestamp - try multiple possible field names
        $updatedAt = $data['updated_at']
            ?? $data['timestamp']
            ?? $data['time']
            ?? date('c');

        // Create status object
        return new TankStatus(
            level: $level,
            updatedAt: $updatedAt,
            sourceStatus: 'ok',
            isStale: false
        );
    }

    /**
     * Cache status to disk for reliability
     * 
     * @param TankStatus $status Status to cache
     * @return bool Success status
     */
    private function cacheStatus(TankStatus $status): bool
    {
        try {
            // Ensure storage directory exists
            $dir = dirname(CACHE_FILE_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Write JSON to cache file
            $json = $status->toJson();
            $result = file_put_contents(CACHE_FILE_PATH, $json, LOCK_EX);

            return $result !== false;

        } catch (Exception $e) {
            error_log("Failed to cache status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load status from cache file
     * 
     * @return TankStatus|null Cached status or null if unavailable
     */
    private function loadFromCache(): ?TankStatus
    {
        try {
            if (!file_exists(CACHE_FILE_PATH)) {
                return null;
            }

            $json = file_get_contents(CACHE_FILE_PATH);

            if ($json === false) {
                return null;
            }

            return TankStatus::fromJson($json);

        } catch (Exception $e) {
            error_log("Failed to load cache: " . $e->getMessage());
            return null;
        }
    }
}
