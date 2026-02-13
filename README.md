# ISS Pee Telemetry Dashboard

A PHP-based web application for monitoring the International Space Station's waste water (pee) tank status in real-time. This project demonstrates reliability patterns, error handling, and modern web design using pure PHP and Bootstrap - no JavaScript required!

## Features

### Core Functionality
- **Real-time Telemetry Monitoring**: Fetches live data from the ISS pee stream API
- **Auto-refresh**: Page automatically updates every 2 seconds using HTML meta tags (no JavaScript)
- **Visual Indicators**: 
  - Color-coded progress bar (green → yellow → red)
  - Large percentage display
  - Status badges and alerts
- **Critical Warnings**: Blinking alert when tank exceeds 90% capacity (pure CSS animation)

### Reliability & Error Handling
- **Graceful Degradation**: Falls back to cached data when API is unavailable
- **Caching System**: Stores last successful result to disk
- **Error States**: Clear visual indicators for:
  - Connection failures
  - Stale data
  - Missing data scenarios
- **Timezone Support**: Displays timestamps in UTC with proper formatting


## Project Structure

```
iss-piss/
├── config.php                 # Configuration constants
├── src/
│   ├── TankStatus.php        # Tank status data model
│   └── TelemetryClient.php   # API client with caching
├── public/
│   └── index.php             # Main dashboard interface
├── storage/
│   └── last_tank_status.json # Cache file (auto-generated)
└── .gitignore
```

## Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- cURL extension enabled


### Quick Start

1. **Clone or download the project**:
   ```bash
   cd /path/to/iss-piss
   ```

2. **Configure the API endpoint** (optional):
   Open `config.php` and update the `TELEMETRY_API_URL` constant if needed:
   ```php
   define('TELEMETRY_API_URL', 'https://your-actual-api-endpoint.com');
   ```

3. **Start the PHP development server**:
   ```bash
   php -S localhost:8000
   ```

4. **Open in browser**:
   Navigate to `http://localhost:8000/public/index.php`

## Configuration

All configuration is centralized in `config.php`:

| Constant | Default | Description |
|----------|---------|-------------|
| `REFRESH_INTERVAL` | `5` | Auto-refresh interval (seconds) |
| `CRITICAL_THRESHOLD` | `90` | Warning threshold (%) |
| `CACHE_FILE_PATH` | `storage/last_tank_status.json` | Cache location |
| `DISPLAY_TIMEZONE` | `UTC` | Timezone for timestamps |


## Architecture & Design Decisions

### Request Flow
1. Browser requests `public/index.php`
2. `TelemetryClient` attempts to fetch from remote API
3. On success: parses data, updates cache, returns fresh `TankStatus`
4. On failure: loads from cache, marks as stale/error
5. `index.php` renders Bootstrap page with status data
6. HTML meta tag causes auto-refresh after configured interval


### Reliability Patterns

#### 1. **Error Handling Levels**
- **Network Errors**: Caught by cURL timeout and error checking
- **Invalid JSON**: Handled by json_decode validation
- **Missing Fields**: Default values prevent crashes
- **File I/O Errors**: Logged but don't break the UI

#### 2. **Graceful Degradation**
- First run with no cache: Shows "Telemetry Link Lost" message
- API down with cache: Shows last known data with "stale" warning
- API up: Normal operation with green indicators

## UI/UX Highlights

### Bootstrap Components Used
- Container/Grid system for responsive layout
- Cards for content organization
- Progress bars with color variants
- Alert components for status messages
- Badges for mode indicators


## Reflection & Learning

### Challenges Solved

**Challenge**: How to show dynamic updates without JavaScript?
- **Solution**: HTML meta refresh + server-side state management

**Challenge**: Handling API unavailability gracefully?
- **Solution**: Multi-level fallback (live → cache → error state)

**Challenge**: Working with an ai that can halucinate?
- **Solution**: Review the code, double check


## Acknowledgments

- **ISS Pee Stream API**: Thanks to the team providing this quirky but fascinating data
- **Bootstrap**: For making beautiful UIs accessible to everyone
- **Google Fonts**: Orbitron and Inter for typography
- **Antigravity**: AI pair programming assistant that helped build this

---

**Built with PHP + Bootstrap + Pure CSS 🚀**

*"One small drip for man, one giant flush for mankind."*
**
