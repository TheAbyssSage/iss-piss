<?php
/**
 * ISS Pee Telemetry Dashboard
 * 
 * Main entry point for the web application.
 * Fetches tank status and renders Bootstrap-styled dashboard.
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
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($currentTheme) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Auto-refresh without JavaScript -->
    <meta http-equiv="refresh" content="<?= REFRESH_INTERVAL ?>">

    <title>ISS Pee Telemetry - Tank Status</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg-primary: #f8f9fa;
            --bg-card: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --accent-color: #0d6efd;
        }

        [data-theme="astronaut"] {
            --bg-primary: #0a0e27;
            --bg-card: rgba(15, 23, 42, 0.8);
            --text-primary: #00ff9f;
            --text-secondary: #64ffda;
            --accent-color: #00ff9f;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Astronaut mode starry background */
        [data-theme="astronaut"] body {
            background-image:
                radial-gradient(2px 2px at 20% 30%, white, transparent),
                radial-gradient(2px 2px at 60% 70%, white, transparent),
                radial-gradient(1px 1px at 50% 50%, white, transparent),
                radial-gradient(1px 1px at 80% 10%, white, transparent),
                radial-gradient(2px 2px at 90% 60%, white, transparent),
                radial-gradient(1px 1px at 33% 80%, white, transparent),
                radial-gradient(1px 1px at 70% 40%, white, transparent);
            background-size: 200% 200%;
            background-position: 0% 0%;
            animation: starfield 60s linear infinite;
        }

        @keyframes starfield {
            0% {
                background-position: 0% 0%;
            }

            100% {
                background-position: 100% 100%;
            }
        }

        .header-title {
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            text-align: center;
            margin: 2rem 0;
            font-size: 2.5rem;
        }

        [data-theme="astronaut"] .header-title {
            color: var(--accent-color);
            text-shadow: 0 0 20px var(--accent-color), 0 0 40px var(--accent-color);
        }

        .tank-card {
            background-color: var(--bg-card);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-theme="astronaut"] .tank-card {
            border-color: var(--accent-color);
            box-shadow: 0 0 30px rgba(0, 255, 159, 0.2);
        }

        .tank-level {
            font-size: 5rem;
            font-weight: 900;
            font-family: 'Orbitron', sans-serif;
            text-align: center;
            margin: 1rem 0;
        }

        .progress {
            height: 40px;
            border-radius: 20px;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            font-size: 1.2rem;
            font-weight: 600;
            line-height: 40px;
            transition: width 0.6s ease;
        }

        /* Blinking warning animation (CSS only) */
        @keyframes blink-warning {

            0%,
            49% {
                opacity: 1;
            }

            50%,
            100% {
                opacity: 0.3;
            }
        }

        .blink-warning {
            animation: blink-warning 1.5s infinite;
        }

        .update-time {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .theme-toggle .btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        [data-theme="astronaut"] .theme-toggle .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid var(--accent-color);
            color: var(--accent-color);
        }

        .footer {
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        [data-theme="astronaut"] .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.3) 0%, rgba(220, 53, 69, 0.1) 100%);
            color: #ff6b6b;
            border: 2px solid #ff6b6b;
        }

        [data-theme="astronaut"] .alert-warning {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.3) 0%, rgba(255, 193, 7, 0.1) 100%);
            color: #ffd43b;
            border: 2px solid #ffd43b;
        }

        [data-theme="astronaut"] .badge {
            background: var(--accent-color) !important;
            color: #0a0e27 !important;
            font-weight: 700;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.8rem;
            }

            .tank-level {
                font-size: 3.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Theme Toggle Button -->
    <div class="theme-toggle">
        <?php if ($currentTheme === 'astronaut'): ?>
            <a href="?mode=default" class="btn btn-outline-light">
                🌍 Ground Control Mode
            </a>
        <?php else: ?>
            <a href="?mode=astronaut" class="btn btn-dark">
                🚀 Astronaut Mode
            </a>
        <?php endif; ?>
    </div>

    <div class="container py-5">
        <!-- Header -->
        <h1 class="header-title">
            🚽 ISS Pee Telemetry
        </h1>

        <?php if ($currentTheme === 'astronaut'): ?>
            <div class="text-center mb-3">
                <span class="badge bg-success">Astronaut Mode: ON</span>
            </div>
        <?php endif; ?>

        <!-- Critical Warning (if applicable) -->
        <?php if ($status->isCritical()): ?>
            <div class="alert alert-danger blink-warning mb-4" role="alert">
                <h4 class="alert-heading">⚠️ CRITICAL: Tank Almost Full!</h4>
                <p class="mb-0">
                    <strong>Tank capacity at
                        <?= $status->level ?>%
                    </strong> -
                    Urgent attention required! The waste water tank is approaching maximum capacity.
                </p>
            </div>
        <?php endif; ?>

        <!-- Error Alert (if API is down) -->
        <?php if ($status->sourceStatus === 'error'): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <h5 class="alert-heading">🔴 Telemetry Link Lost</h5>
                <p class="mb-0">
                    Connection to ISS telemetry system unavailable.
                    Showing last known value (may be stale).
                </p>
            </div>
        <?php endif; ?>

        <!-- Stale Data Warning -->
        <?php if ($status->isStale && $status->sourceStatus !== 'error'): ?>
            <div class="alert alert-warning mb-4" role="alert">
                <h5 class="alert-heading">⚠️ Data Might Be Outdated</h5>
                <p class="mb-0">
                    The displayed information may not reflect the current status.
                </p>
            </div>
        <?php endif; ?>

        <!-- Main Tank Status Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="tank-card">
                    <h2 class="text-center mb-4" style="font-family: 'Orbitron', sans-serif;">
                        Waste Water Tank Status
                    </h2>

                    <!-- Tank Level Percentage -->
                    <div class="tank-level">
                        <?= $status->level ?>%
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress mb-4">
                        <div class="progress-bar <?= $status->getProgressBarColor() ?>" role="progressbar"
                            style="width: <?= $status->level ?>%;" aria-valuenow="<?= $status->level ?>"
                            aria-valuemin="0" aria-valuemax="100">
                            <?= $status->level ?>% Full
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="row text-center mt-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-uppercase" style="color: var(--text-secondary); font-size: 0.75rem;">
                                Status
                            </h6>
                            <p class="mb-0 fw-bold">
                                <?php if ($status->level < 70): ?>
                                    ✅ Normal
                                <?php elseif ($status->level < 90): ?>
                                    ⚠️ Caution
                                <?php else: ?>
                                    🚨 Critical
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-uppercase" style="color: var(--text-secondary); font-size: 0.75rem;">
                                Data Source
                            </h6>
                            <p class="mb-0 fw-bold">
                                <?= $status->sourceStatus === 'ok' ? '🟢 Live' : '🔴 Cached' ?>
                            </p>
                        </div>
                    </div>

                    <!-- Last Updated Time -->
                    <div class="update-time">
                        <small>
                            Last updated: <strong>
                                <?= htmlspecialchars($status->getFormattedTime()) ?>
                            </strong>
                        </small>
                        <br>
                        <small style="opacity: 0.7;">
                            Auto-refreshing every
                            <?= REFRESH_INTERVAL ?> seconds
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Card -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8">
                <div class="card" style="background-color: var(--bg-card); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="card-body">
                        <h6 class="card-title">ℹ️ About This Dashboard</h6>
                        <p class="card-text mb-0" style="font-size: 0.9rem; color: var(--text-secondary);">
                            This dashboard monitors the International Space Station's waste water tank in real-time.
                            The page automatically refreshes every
                            <?= REFRESH_INTERVAL ?> seconds without JavaScript.
                            When the remote telemetry link is unavailable, the system displays the last known cached
                            data.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="mb-1">
                <strong>Powered by PHP + Antigravity + Bootstrap</strong>
            </p>
            <p class="mb-0">
                A student project exploring reliability patterns and web telemetry.
            </p>
            <p class="mt-2" style="opacity: 0.6;">
                <small>
                    Data source: <a href="<?= htmlspecialchars(TELEMETRY_API_URL) ?>" target="_blank"
                        style="color: var(--accent-color);">ISS Pee Stream API</a>
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS (optional, for potential future enhancements) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>