<?php
/**
 * ISS Telemetry System v2.0
 * 
 * Futuristic space station interface for monitoring waste water tank status.
 */

// Load configuration and classes
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/TelemetryClient.php';

// Handle theme switching
$currentTheme = $_GET['mode'] ?? $_COOKIE[THEME_COOKIE_NAME] ?? 'default';

// Validate theme value (security: prevent injection)
if (!in_array($currentTheme, ['default', 'astronaut'])) {
    $currentTheme = 'default';
}

// Set theme cookie
setcookie(THEME_COOKIE_NAME, $currentTheme, time() + THEME_COOKIE_LIFETIME, '/');

// Fetch current tank status
$client = new TelemetryClient();
$status = $client->fetchCurrentStatus();

// Calculate time ago
$now = new DateTime();
$updated = new DateTime($status->updatedAt);
$interval = $now->diff($updated);
$secondsAgo = ($interval->days * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($currentTheme) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto-refresh without JavaScript -->
    <meta http-equiv="refresh" content="<?= REFRESH_INTERVAL ?>">

    <title>ISS TELEMETRY SYSTEM v2.0</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-main: #0a0b0f;
            --bg-panel: #13151d;
            --border-color: #00e5ff;
            --text-primary: #ffffff;
            --text-secondary: #7dd3fc;
            --text-dim: #64748b;
            --accent-cyan: #00e5ff;
            --accent-green: #00ff9f;
            --accent-yellow: #fbbf24;
            --accent-red: #ef4444;
            --glow-cyan: rgba(0, 229, 255, 0.5);
            --glow-green: rgba(0, 255, 159, 0.5);
        }

        [data-theme="astronaut"] {
            --border-color: #00ff9f;
            --text-secondary: #00ff9f;
            --accent-cyan: #00ff9f;
        }

        body {
            background: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Rajdhani', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Grid Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(0, 229, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 229, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridScroll 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }


        .container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .header-title {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 4px;
            color: var(--accent-cyan);
            text-shadow:
                0 0 10px var(--glow-cyan),
                0 0 20px var(--glow-cyan),
                0 0 30px var(--glow-cyan);
            margin-bottom: 0.5rem;
            animation: titlePulse 2s ease-in-out infinite;
        }

        @keyframes titlePulse {

            0%,
            100% {
                text-shadow:
                    0 0 10px var(--glow-cyan),
                    0 0 20px var(--glow-cyan),
                    0 0 30px var(--glow-cyan);
            }

            50% {
                text-shadow:
                    0 0 20px var(--glow-cyan),
                    0 0 30px var(--glow-cyan),
                    0 0 40px var(--glow-cyan),
                    0 0 50px var(--glow-cyan);
            }
        }

        .header-subtitle {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
            color: var(--text-dim);
            letter-spacing: 2px;
            animation: dataFlicker 3s infinite;
        }

        @keyframes dataFlicker {

            0%,
            100% {
                opacity: 0.7;
            }

            50% {
                opacity: 1;
            }
        }

        .system-time {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .theme-btn {
            padding: 0.75rem 1.5rem;
            background: var(--bg-panel);
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            font-family: 'Orbitron', monospace;
            font-size: 0.8rem;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }

        .theme-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, var(--glow-cyan), transparent);
            transition: left 0.5s;
        }

        .theme-btn:hover::before {
            left: 100%;
        }

        .theme-btn:hover {
            box-shadow: 0 0 20px var(--glow-cyan);
            transform: translateY(-2px);
        }

        /* Critical Alert */
        .critical-alert {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05));
            border: 2px solid var(--accent-red);
            border-radius: 0;
            padding: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            animation: criticalPulse 1.5s infinite;
        }

        @keyframes criticalPulse {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
                border-color: var(--accent-red);
            }

            50% {
                box-shadow: 0 0 40px rgba(239, 68, 68, 0.8);
                border-color: #ff6b6b;
            }
        }

        .critical-alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.3), transparent);
            animation: alertSweep 2s linear infinite;
        }

        @keyframes alertSweep {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .alert-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            color: var(--accent-red);
            margin-bottom: 0.5rem;
            letter-spacing: 2px;
        }

        /* Status Alerts */
        .status-alert {
            background: var(--bg-panel);
            border-left: 4px solid var(--accent-yellow);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
            position: relative;
        }

        .status-alert::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-yellow);
            animation: statusBlink 2s infinite;
        }

        @keyframes statusBlink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Main Panel */
        .main-panel {
            background: var(--bg-panel);
            border: 2px solid var(--border-color);
            padding: 3rem;
            position: relative;
            box-shadow:
                0 0 30px rgba(0, 229, 255, 0.1),
                inset 0 0 30px rgba(0, 229, 255, 0.05);
        }

        /* Corner Accents */
        .main-panel::before,
        .main-panel::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
        }

        .main-panel::before {
            top: -2px;
            left: -2px;
            border-right: none;
            border-bottom: none;
            animation: cornerGlow 2s infinite;
        }

        .main-panel::after {
            bottom: -2px;
            right: -2px;
            border-left: none;
            border-top: none;
            animation: cornerGlow 2s infinite 1s;
        }

        @keyframes cornerGlow {

            0%,
            100% {
                box-shadow: 0 0 5px var(--glow-cyan);
            }

            50% {
                box-shadow: 0 0 15px var(--glow-cyan);
            }
        }

        .panel-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.8rem;
            text-align: center;
            color: var(--text-secondary);
            letter-spacing: 3px;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        /* Tank Display */
        .tank-display {
            text-align: center;
            margin: 3rem 0;
        }

        .tank-percentage {
            font-family: 'Orbitron', monospace;
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            color: var(--accent-cyan);
            text-shadow:
                0 0 20px var(--glow-cyan),
                0 0 40px var(--glow-cyan);
            position: relative;
            display: inline-block;
            animation: percentageGlow 3s ease-in-out infinite;
        }

        @keyframes percentageGlow {

            0%,
            100% {
                filter: brightness(1);
                transform: scale(1);
            }

            50% {
                filter: brightness(1.2);
                transform: scale(1.02);
            }
        }

        .tank-percentage.critical {
            color: var(--accent-red);
            text-shadow:
                0 0 20px rgba(239, 68, 68, 0.8),
                0 0 40px rgba(239, 68, 68, 0.8);
            animation: criticalFlash 0.5s infinite;
        }

        .tank-percentage.warning {
            color: var(--accent-yellow);
            text-shadow:
                0 0 20px rgba(251, 191, 36, 0.8),
                0 0 40px rgba(251, 191, 36, 0.8);
        }

        @keyframes criticalFlash {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .tank-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 1.2rem;
            color: var(--text-dim);
            letter-spacing: 2px;
            margin-top: 1rem;
        }

        /* Progress Container */
        .progress-container {
            margin: 3rem 0;
            position: relative;
        }

        .progress-bar-custom {
            height: 60px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg,
                    var(--accent-cyan),
                    var(--accent-green));
            transition: width 1s ease;
            position: relative;
            box-shadow:
                0 0 20px var(--glow-cyan),
                inset 0 0 20px rgba(255, 255, 255, 0.2);
            animation: progressShimmer 2s linear infinite;
        }

        .progress-fill.warning {
            background: linear-gradient(90deg,
                    var(--accent-yellow),
                    #fb923c);
            box-shadow:
                0 0 20px rgba(251, 191, 36, 0.7),
                inset 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .progress-fill.critical {
            background: linear-gradient(90deg,
                    var(--accent-red),
                    #dc2626);
            box-shadow:
                0 0 20px rgba(239, 68, 68, 0.7),
                inset 0 0 20px rgba(255, 255, 255, 0.2);
        }

        @keyframes progressShimmer {
            0% {
                filter: brightness(1);
            }

            50% {
                filter: brightness(1.3);
            }

            100% {
                filter: brightness(1);
            }
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
            z-index: 10;
            letter-spacing: 2px;
        }

        /* Data Grid */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .data-cell {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .data-cell::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg,
                    transparent,
                    var(--accent-cyan),
                    transparent);
            animation: dataScan 3s linear infinite;
        }

        @keyframes dataScan {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .data-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .data-value {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            color: var(--text-secondary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            animation: statusPulse 2s infinite;
        }

        .status-indicator.live {
            background: var(--accent-green);
            box-shadow: 0 0 10px var(--glow-green);
        }

        .status-indicator.cached {
            background: var(--accent-red);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.7);
        }

        @keyframes statusPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
        }

        /* Timestamp */
        .timestamp-display {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 229, 255, 0.2);
        }

        .timestamp-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .timestamp-value {
            font-family: 'Orbitron', monospace;
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            font-weight: 600;
        }

        .refresh-notice {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.7rem;
            color: var(--text-dim);
            margin-top: 0.5rem;
            animation: dataFlicker 3s infinite;
        }

        /* Info Panel */
        .info-panel {
            background: var(--bg-panel);
            border: 1px solid rgba(0, 229, 255, 0.3);
            padding: 1.5rem;
            margin-top: 2rem;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.85rem;
            color: var(--text-dim);
            line-height: 1.6;
        }

        .info-title {
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem;
            border-top: 1px solid rgba(0, 229, 255, 0.2);
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-dim);
        }

        .footer a {
            color: var(--accent-cyan);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer a:hover {
            text-shadow: 0 0 10px var(--glow-cyan);
        }

        /* Particles */
        .particle {
            position: fixed;
            width: 2px;
            height: 2px;
            background: var(--accent-cyan);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 10s linear infinite;
            z-index: 0;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.8rem;
                letter-spacing: 2px;
            }

            .tank-percentage {
                font-size: 5rem;
            }

            .main-panel {
                padding: 1.5rem;
            }

            .data-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Loading Animation */
        @keyframes loadingDots {

            0%,
            20% {
                content: '.';
            }

            40% {
                content: '..';
            }

            60%,
            100% {
                content: '...';
            }
        }

        .loading::after {
            content: '';
            animation: loadingDots 1.5s infinite;
        }
    </style>
</head>

<body>
    <!-- Grid Plane Background -->
    <div class="grid-plane"></div>

    <!-- Multi-layer Stars -->
    <div class="stars-container">
        <div class="star-layer"></div>
        <div class="star-layer"></div>
        <div class="star-layer"></div>
    </div>

    <!-- Holographic Checkbox Control -->
    <div class="checkbox-container">
        <input class="holo-checkbox-input" id="holo-check" type="checkbox" />
        <label class="holo-checkbox" for="holo-check">
            <div class="holo-box">
                <div class="holo-inner"></div>
                <div class="scan-effect"></div>

                <div class="holo-particles">
                    <div class="holo-particle"></div>
                    <div class="holo-particle"></div>
                    <div class="holo-particle"></div>
                    <div class="holo-particle"></div>
                    <div class="holo-particle"></div>
                    <div class="holo-particle"></div>
                </div>

                <div class="activation-rings">
                    <div class="activation-ring"></div>
                    <div class="activation-ring"></div>
                    <div class="activation-ring"></div>
                </div>

                <div class="cube-transform">
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                    <div class="cube-face"></div>
                </div>
            </div>

            <div class="corner-accent"></div>
            <div class="corner-accent"></div>
            <div class="corner-accent"></div>
            <div class="corner-accent"></div>

            <div class="holo-glow"></div>
        </label>

        <div class="status-text"></div>


        <div class="data-chips">
            <div class="data-chip">STATUS: IDLE [0x4F]</div>
            <div class="data-chip">QUANTUM VERIFY: 82.6%</div>
            <div class="data-chip">SYNCH: PENDING</div>
            <div class="data-chip">0x7A2C8B9F</div>
        </div>
    </div>

    <!-- Floating Particles -->
    <?php for ($i = 0; $i < 15; $i++): ?>
        <div class="particle"
            style="left: <?= rand(0, 100) ?>%; animation-delay: <?= rand(0, 10) ?>s; animation-duration: <?= rand(8, 15) ?>s;">
        </div>
    <?php endfor; ?>

    <!-- Theme Toggle -->
    <div class="theme-toggle">
        <?php if ($currentTheme === 'astronaut'): ?>
            <a href="?mode=default" class="theme-btn">
                GROUND MODE
            </a>
        <?php else: ?>
            <a href="?mode=astronaut" class="theme-btn">
                ASTRONAUT MODE
            </a>
        <?php endif; ?>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="header-title">ISS TELEMETRY SYSTEM</h1>
            <div class="header-subtitle">WASTE WATER TANK MONITORING // v2.0</div>
            <div class="system-time">
                SYSTEM TIME: <?= date('Y.m.d // H:i:s') ?> UTC
            </div>
        </div>

        <!-- Critical Alert -->
        <?php if ($status->isCritical()): ?>
            <div class="critical-alert">
                <div class="alert-title">⚠ CRITICAL WARNING</div>
                <div style="font-family: 'Share Tech Mono', monospace; font-size: 1.1rem; color: #ffffff;">
                    TANK CAPACITY AT <?= $status->level ?>% // IMMEDIATE ACTION REQUIRED
                </div>
                <div
                    style="font-family: 'Share Tech Mono', monospace; font-size: 0.9rem; color: #fca5a5; margin-top: 0.5rem;">
                    WASTE WATER TANK APPROACHING MAXIMUM CAPACITY
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Alerts -->
        <?php if ($status->sourceStatus === 'error'): ?>
            <div class="status-alert" style="border-left-color: var(--accent-red);">
                <div style="color: var(--accent-red); font-weight: 600; margin-bottom: 0.3rem;">
                    ⬢ TELEMETRY LINK: DISCONNECTED
                </div>
                <div>
                    Connection to ISS telemetry system unavailable // Displaying cached data
                </div>
            </div>
        <?php endif; ?>

        <?php if ($status->isStale && $status->sourceStatus !== 'error'): ?>
            <div class="status-alert">
                <div style="color: var(--accent-yellow); font-weight: 600; margin-bottom: 0.3rem;">
                    ⬢ DATA STATUS: STALE
                </div>
                <div>
                    Displayed information may not reflect current system state
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Panel -->
        <div class="main-panel">
            <div class="panel-title">// TANK STATUS //</div>

            <!-- Tank Percentage Display -->
            <div class="tank-display">
                <div
                    class="tank-percentage <?= $status->level >= 90 ? 'critical' : ($status->level >= 70 ? 'warning' : '') ?>">
                    <?= $status->level ?><span style="font-size: 0.5em;">%</span>
                </div>
                <div class="tank-label">CAPACITY UTILIZATION</div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar-custom">
                    <div class="progress-fill <?= $status->level >= 90 ? 'critical' : ($status->level >= 70 ? 'warning' : '') ?>"
                        style="width: <?= $status->level ?>%;">
                    </div>
                    <div class="progress-text"><?= $status->level ?>% FULL</div>
                </div>
            </div>

            <!-- Data Grid -->
            <div class="data-grid">
                <div class="data-cell">
                    <div class="data-label">SYSTEM STATUS</div>
                    <div class="data-value">
                        <?php if ($status->level < 70): ?>
                            ✓ NOMINAL
                        <?php elseif ($status->level < 90): ?>
                            ⚠ CAUTION
                        <?php else: ?>
                            ⚠ CRITICAL
                        <?php endif; ?>
                    </div>
                </div>

                <div class="data-cell">
                    <div class="data-label">DATA SOURCE</div>
                    <div class="data-value">
                        <span class="status-indicator <?= $status->sourceStatus === 'ok' ? 'live' : 'cached' ?>"></span>
                        <?= $status->sourceStatus === 'ok' ? 'LIVE FEED' : 'CACHED DATA' ?>
                    </div>
                </div>

                <div class="data-cell">
                    <div class="data-label">REFRESH RATE</div>
                    <div class="data-value">
                        <?= REFRESH_INTERVAL ?>s
                    </div>
                </div>

                <div class="data-cell">
                    <div class="data-label">TANK ID</div>
                    <div class="data-value">
                        WWT-001
                    </div>
                </div>
            </div>

            <!-- Timestamp -->
            <div class="timestamp-display">
                <div class="timestamp-label">LAST DATA SYNC</div>
                <div class="timestamp-value"><?= htmlspecialchars($status->getFormattedTime()) ?></div>
                <div class="refresh-notice">
                    AUTO-REFRESH ENABLED // NEXT SYNC IN <?= REFRESH_INTERVAL ?>s
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="info-panel">
            <div class="info-title">⬡ SYSTEM INFORMATION</div>
            <div>
                This interface monitors the International Space Station's waste water tank in real-time.
                The system automatically refreshes every <?= REFRESH_INTERVAL ?> seconds without requiring
                user interaction. When telemetry link is unavailable, the display shows last known cached
                data with appropriate status indicators.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="margin-bottom: 0.5rem;">
                POWERED BY PHP TELEMETRY ENGINE v8.4 // BOOTSTRAP FRAMEWORK
            </div>
            <div style="opacity: 0.6;">
                Educational Research Project // Space Systems Monitoring
            </div>
            <div style="margin-top: 1rem; opacity: 0.5;">
                DATA SOURCE: <a href="<?= htmlspecialchars(TELEMETRY_API_URL) ?>" target="_blank">ISS Telemetry
                    Stream</a>
            </div>
        </div>
    </div>
</body>

</html>