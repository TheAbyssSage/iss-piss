# 🚽 ISS Pee Telemetry Dashboard

A PHP-based web application for monitoring the International Space Station's waste water (pee) tank status in real-time. This project demonstrates reliability patterns, error handling, and modern web design using pure PHP and Bootstrap - no JavaScript required!

## 🌟 Features

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

### Themes
- **Default Mode**: Clean, professional Bootstrap design
- **Astronaut Mode**: 
  - Dark space theme with animated starry background
  - Neon green accents with glowing text effects
  - Toggle via button or cookie persistence

## 📁 Project Structure

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

## 🚀 Installation & Setup

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
   php -S localhost:8080 -t public
   ```

4. **Open in browser**:
   Navigate to `http://localhost:8080`

## ⚙️ Configuration

All configuration is centralized in `config.php`:

| Constant | Default | Description |
|----------|---------|-------------|
| `TELEMETRY_API_URL` | `https://gigazane.net/gsc_news/en/20241225-pissstream/` | Remote API endpoint |
| `REFRESH_INTERVAL` | `10` | Auto-refresh interval (seconds) |
| `CRITICAL_THRESHOLD` | `90` | Warning threshold (%) |
| `CACHE_FILE_PATH` | `storage/last_tank_status.json` | Cache location |
| `DISPLAY_TIMEZONE` | `UTC` | Timezone for timestamps |

## 🏗️ Architecture & Design Decisions

### Request Flow
1. Browser requests `index.php`
2. `TelemetryClient` attempts to fetch from remote API
3. On success: parses data, updates cache, returns fresh `TankStatus`
4. On failure: loads from cache, marks as stale/error
5. `index.php` renders Bootstrap page with status data
6. HTML meta tag causes auto-refresh after configured interval

### Boundary Between Untrusted and Trusted Data

**Untrusted Data (External API)**:
- All API responses are validated before use
- Fields are sanitized and type-checked
- Missing fields get sensible defaults
- Invalid responses trigger error handling

**Trust Boundary**: `TelemetryClient::parseApiResponse()`

**Trusted Data** (Application Domain):
- `TankStatus` objects with validated data
- All values clamped to safe ranges (0-100%)
- Timestamps parsed and formatted safely

### Security Considerations

1. **No Secrets in HTML**: 
   - Configuration stays server-side
   - No API keys exposed to client

2. **Input Validation**:
   - Theme mode validated against whitelist
   - API responses sanitized before use

3. **Output Escaping**:
   - All user-facing data escaped with `htmlspecialchars()`
   - XSS prevention in status messages

4. **HTTPS**: 
   - cURL configured to verify SSL certificates
   - Prevents MITM attacks on API calls

5. **File Permissions**:
   - Cache directory created with 0755 permissions
   - Write operations use `LOCK_EX` flag

### Reliability Patterns

#### 1. **Caching Strategy**
```
Try Remote API
    ↓ Success
Save to Cache → Return Fresh Data
    ↓ Failure
Load Cache → Mark as Stale → Return Cached Data
    ↓ No Cache
Return Error State (0%, error status)
```

#### 2. **Error Handling Levels**
- **Network Errors**: Caught by cURL timeout and error checking
- **Invalid JSON**: Handled by json_decode validation
- **Missing Fields**: Default values prevent crashes
- **File I/O Errors**: Logged but don't break the UI

#### 3. **Graceful Degradation**
- First run with no cache: Shows "Telemetry Link Lost" message
- API down with cache: Shows last known data with "stale" warning
- API up: Normal operation with green indicators

## 🎨 UI/UX Highlights

### Bootstrap Components Used
- Container/Grid system for responsive layout
- Cards for content organization
- Progress bars with color variants
- Alert components for status messages
- Badges for mode indicators

### Pure CSS Features
- **Blinking Animation**: `@keyframes blink-warning` (no JS)
- **Starfield Animation**: Moving gradient background in astronaut mode
- **Theme Switching**: CSS variables + data attributes
- **Responsive Design**: Mobile-friendly breakpoints

### Color Coding Logic
| Tank Level | Color | Bootstrap Class | Meaning |
|------------|-------|----------------|---------|
| 0-69% | Green | `bg-success` | Normal operation |
| 70-89% | Yellow | `bg-warning` | Caution needed |
| 90-100% | Red | `bg-danger` | Critical - urgent attention |

## 🧪 Testing Scenarios

### Scenario 1: First Run (No Cache, API Available)
- **Expected**: Fetch from API, display live data, create cache
- **Result**: Green "Live" indicator, current percentage

### Scenario 2: API Down, Cache Exists
- **Expected**: Load cache, show "Telemetry Link Lost" alert
- **Result**: Red "Cached" indicator, stale warning, last known data

### Scenario 3: API Down, No Cache
- **Expected**: Display error message, 0% status
- **Result**: Error alerts, graceful failure message

### Scenario 4: Critical Level (≥90%)
- **Expected**: Blinking warning, red progress bar
- **Result**: Animated alert at top of page

### Scenario 5: Theme Switching
- **Expected**: Dark theme with neon effects
- **Result**: Persisted via cookie, starry background

## 🔮 Future Enhancements

Potential improvements for v2:
- [ ] Historical data graphs (using Chart.js or similar)
- [ ] Email/SMS alerts when critical threshold reached
- [ ] Multiple tank monitoring
- [ ] API authentication support
- [ ] WebSocket for true real-time updates
- [ ] Admin panel for threshold configuration
- [ ] Prometheus metrics export

## 📝 Reflection & Learning

### Key Takeaways

**1. Reliability Without Complexity**:
- Simple file-based caching provides surprisingly robust fallback
- No database needed for basic persistence
- Explicit error states better than silent failures

**2. PHP-Only Auto-Refresh**:
- Meta refresh tag is underutilized but effective
- No need for JavaScript polling or WebSockets for simple use cases
- Reduces attack surface (XSS risks)

**3. Bootstrap as Design System**:
- Pre-built components accelerate development
- Consistent design language out of the box
- Easy theming with CSS variables

**4. Security by Default**:
- Validating inputs at boundaries prevents many issues
- Escaping outputs is non-negotiable
- Logging errors without exposing them to users

### Challenges Solved

**Challenge**: How to show dynamic updates without JavaScript?
- **Solution**: HTML meta refresh + server-side state management

**Challenge**: Handling API unavailability gracefully?
- **Solution**: Multi-level fallback (live → cache → error state)

**Challenge**: Creating engaging UI with Bootstrap alone?
- **Solution**: Custom CSS variables, animations, and themes

## 👨‍🎓 Educational Context

This project was created as a student assignment to explore:
- **Web architecture patterns** (MVC-inspired separation)
- **Error handling strategies** (defensive programming)
- **Caching mechanisms** (simple file-based persistence)
- **Security boundaries** (input validation, output escaping)
- **Modern CSS** (animations, variables, responsive design)

## 📄 License

This is an educational project. Feel free to modify and experiment!

## 🙏 Acknowledgments

- **ISS Pee Stream API**: Thanks to the team providing this quirky but fascinating data
- **Bootstrap**: For making beautiful UIs accessible to everyone
- **Google Fonts**: Orbitron and Inter for typography
- **Antigravity**: AI pair programming assistant that helped build this

---

**Built with PHP + Bootstrap + Pure CSS 🚀**

*"One small drip for man, one giant flush for mankind."*
