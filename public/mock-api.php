<?php
/**
 * Mock ISS Pee Telemetry API Endpoint
 * 
 * This simulates a real telemetry API for testing purposes.
 * Returns JSON with current tank percentage.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simulate varying tank levels between 0-100%
// Use time-based pseudo-random to make it change gradually
$baseLevel = 45;
$variation = (int) ((sin(time() / 100) * 30) + 30); // Oscillates between 0-60, centered around 30
$currentLevel = min(100, max(0, $baseLevel + $variation));

// Occasionally simulate "critical" level for testing
if (date('s') % 30 < 5) {
    $currentLevel = 92; // Critical level
}

$response = [
    'percentage' => $currentLevel,
    'updated_at' => date('c'), // ISO 8601 format
    'timestamp' => time(),
    'status' => 'ok',
    'station' => 'ISS',
    'tank_id' => 'WASTE_WATER_001'
];

echo json_encode($response, JSON_PRETTY_PRINT);
