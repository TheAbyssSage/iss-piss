<?php
/**
 * TankStatus Class
 * 
 * Represents the current status of the ISS pee tank.
 * This is the boundary between untrusted API data and our application's domain model.
 */

class TankStatus
{
    /**
     * @var int Tank fill level (0-100 percentage)
     */
    public int $level;

    /**
     * @var string Timestamp when the data was last updated
     */
    public string $updatedAt;

    /**
     * @var string Source status: "ok" or "error"
     */
    public string $sourceStatus;

    /**
     * @var bool Whether the data is stale (cached/outdated)
     */
    public bool $isStale;

    /**
     * Constructor
     */
    public function __construct(
        int $level = 0,
        string $updatedAt = '',
        string $sourceStatus = 'ok',
        bool $isStale = false
    ) {
        $this->level = max(0, min(100, $level)); // Clamp between 0-100
        $this->updatedAt = $updatedAt;
        $this->sourceStatus = $sourceStatus;
        $this->isStale = $isStale;
    }

    /**
     * Check if the tank level is at or above critical threshold
     * 
     * @return bool True if level >= CRITICAL_THRESHOLD
     */
    public function isCritical(): bool
    {
        return $this->level >= CRITICAL_THRESHOLD;
    }

    /**
     * Get the Bootstrap color class for the progress bar
     * 
     * @return string Bootstrap bg-* class name
     */
    public function getProgressBarColor(): string
    {
        if ($this->level >= 90) {
            return 'bg-danger'; // Red
        } elseif ($this->level >= 70) {
            return 'bg-warning'; // Yellow
        } else {
            return 'bg-success'; // Green
        }
    }

    /**
     * Get formatted timestamp in the configured timezone
     * 
     * @return string Formatted datetime string
     */
    public function getFormattedTime(): string
    {
        if (empty($this->updatedAt)) {
            return 'Unknown';
        }

        try {
            $date = new DateTime($this->updatedAt);
            $date->setTimezone(new DateTimeZone(DISPLAY_TIMEZONE));
            return $date->format('Y-m-d H:i:s') . ' ' . DISPLAY_TIMEZONE;
        } catch (Exception $e) {
            return $this->updatedAt;
        }
    }

    /**
     * Convert to JSON for caching
     * 
     * @return string JSON representation
     */
    public function toJson(): string
    {
        return json_encode([
            'level' => $this->level,
            'updatedAt' => $this->updatedAt,
            'sourceStatus' => $this->sourceStatus,
            'isStale' => $this->isStale
        ]);
    }

    /**
     * Create instance from JSON data
     * 
     * @param string $json JSON string
     * @return TankStatus|null
     */
    public static function fromJson(string $json): ?TankStatus
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return null;
        }

        return new TankStatus(
            $data['level'] ?? 0,
            $data['updatedAt'] ?? '',
            $data['sourceStatus'] ?? 'error',
            $data['isStale'] ?? true
        );
    }
}
