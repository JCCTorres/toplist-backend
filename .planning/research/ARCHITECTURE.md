# Architecture Patterns

**Domain:** Vacation Rental Property Management Website (Laravel + React)
**Researched:** 2026-02-07
**Confidence:** HIGH (based on official Laravel documentation and established patterns)

## Executive Summary

This document defines the architecture for connecting the React 18 frontend to the Laravel 11 backend, with recommendations for CORS configuration, API integration, and deployment strategy. Given the constraint of deploying today and the small dataset (21 properties), the recommended approach is **single-origin serving** where Laravel serves the React build as static files.

---

## Recommended Architecture

```
+------------------+     +----------------------+     +-------------------+
|                  |     |                      |     |                   |
|  React Frontend  |---->|  Laravel Backend     |---->|  Bookerville API  |
|  (Static Build)  |     |  /api/bookerville/*  |     |  (XML External)   |
|                  |     |                      |     |                   |
+------------------+     +----------------------+     +-------------------+
        |                        |
        |                        v
        |                +----------------+
        +--------------->|  Filament Admin |
         (same origin)   |  /admin         |
                         +----------------+
                                |
                                v
                         +----------------+
                         |    MySQL DB    |
                         |  (properties,  |
                         |   users, etc)  |
                         +----------------+
```

### Component Boundaries

| Component | Responsibility | Communicates With |
|-----------|---------------|-------------------|
| React Frontend | Property listing, search, booking UI, guest-facing pages | Laravel API via fetch/axios |
| Laravel API | REST endpoints at `/api/bookerville/*`, authentication, data transformation | MySQL, Bookerville XML API |
| BookervilleService | External API integration, XML parsing, caching (300s TTL) | Bookerville external API |
| Filament Admin | Property management, user admin, data CRUD | MySQL directly via Eloquent |
| MySQL Database | Persistent storage for properties, client_properties, users | All backend services |

### Data Flow

```
User Request
    |
    v
[React SPA] ---(fetch/axios)---> [Laravel /api/bookerville/*]
                                          |
                                          v
                                   [Check Cache]
                                     /      \
                                (hit)        (miss)
                                  |            |
                                  v            v
                            [Return]    [BookervilleService]
                                               |
                                               v
                                    [Bookerville XML API]
                                               |
                                               v
                                    [Parse XML -> JSON]
                                               |
                                               v
                                    [Cache for 300s]
                                               |
                                               v
                                    [Return to React]
```

---

## Deployment Options Comparison

### Option A: Same-Origin Serving (RECOMMENDED)

**What:** Build React with Vite, output to `public/react/`, Laravel serves static files
**Best for:** Today's deployment, small team, simple infrastructure

**Pros:**
- No CORS configuration needed (same origin)
- Single deployment target
- Simpler DNS/hosting setup
- No subdomain/cookie issues with Sanctum
- Faster initial load (no cross-origin preflight)

**Cons:**
- Coupled deployment (must deploy both together)
- Build process slightly more complex

**Implementation:**

1. Configure React Vite to build to Laravel public folder:
```javascript
// toplist-main/vite.config.js
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../control_toplist-homolog/control_toplist-homolog/public/react',
    emptyOutDir: true,
  },
  base: '/react/',
});
```

2. Add Laravel catch-all route for SPA:
```php
// routes/web.php
Route::get('/react/{any?}', function () {
    return file_get_contents(public_path('react/index.html'));
})->where('any', '.*');
```

3. API calls from React use relative paths:
```javascript
// React API client
const API_BASE = '/api/bookerville';
fetch(`${API_BASE}/properties/cards`);
```

### Option B: Separate Origins with CORS

**What:** React hosted separately (Vercel, Netlify), Laravel API on different domain
**Best for:** Large teams, independent deployments, serverless frontend

**Pros:**
- Independent deployment cycles
- Can use Vercel/Netlify edge for frontend
- Separation of concerns

**Cons:**
- CORS configuration required
- Sanctum stateful domain setup needed
- Cookie/session domain matching critical
- More infrastructure to manage

**CORS Configuration (if needed):**

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://toplist-orlando.example.com', // Production frontend
        'http://localhost:3001',                // Development
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // CRITICAL for Sanctum
];
```

```env
# .env
SANCTUM_STATEFUL_DOMAINS=toplist-orlando.example.com,localhost:3001
SESSION_DOMAIN=.example.com
```

---

## API Integration Pattern

### React API Client Structure

Create a centralized API client to replace the missing `lib/api/bookerville/client` references:

```javascript
// src/lib/api/client.js
const API_BASE = import.meta.env.VITE_API_URL || '/api/bookerville';

export const apiClient = {
  async get(endpoint, params = {}) {
    const url = new URL(`${API_BASE}${endpoint}`, window.location.origin);
    Object.entries(params).forEach(([key, value]) =>
      url.searchParams.append(key, value)
    );

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin', // or 'include' for cross-origin
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return response.json();
  },

  async post(endpoint, data) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return response.json();
  }
};
```

### Environment Configuration

```javascript
// src/lib/api/config.js
export const BOOKERVILLE_CONFIG = {
  ENDPOINTS: {
    PROPERTIES: '/properties',
    PROPERTY_CARDS: '/properties/cards',
    PROPERTY_DETAIL: '/properties',
    AVAILABILITY: '/availability',
    SEARCH: '/properties/search',
    HOME_CARDS: '/home-cards',
  },
  CACHE: {
    AVAILABILITY: 300000, // 5 minutes (matches Laravel 300s)
    PROPERTIES: 600000,   // 10 minutes for static data
  }
};
```

---

## Caching Strategy

### Current State (Laravel Backend)
The BookervilleService already implements 300-second caching for Bookerville API responses. This is appropriate for availability data that changes relatively frequently.

### Recommended Enhancements

| Data Type | Laravel Cache TTL | React Cache TTL | Rationale |
|-----------|------------------|-----------------|-----------|
| Property Summary | 300s (current) | 5 min | Changes with Bookerville updates |
| Property Details | 300s (current) | 10 min | Rarely changes mid-session |
| Availability | 300s (current) | 2 min | Most time-sensitive |
| Property Cards (DB) | 600s | 10 min | Local DB, less volatile |
| Guest Reviews | 3600s | 30 min | Rarely updated |

### React-Side Caching Pattern

```javascript
// Use browser memory cache to avoid redundant calls
const cache = new Map();

export function useCachedFetch(key, fetcher, ttl = 300000) {
  const cached = cache.get(key);
  if (cached && Date.now() - cached.timestamp < ttl) {
    return { data: cached.data, loading: false, fromCache: true };
  }
  // ... fetch logic
}
```

---

## Patterns to Follow

### Pattern 1: API Response Envelope

**What:** All Laravel API responses use consistent structure
**When:** Every API endpoint

```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message",
  "meta": {
    "timestamp": "2026-02-07T12:00:00Z",
    "cached": true,
    "cache_expires": "2026-02-07T12:05:00Z"
  }
}
```

### Pattern 2: Error Handling Consistency

**What:** Standardized error responses
**When:** Any API error condition

```json
{
  "success": false,
  "error": "ERROR_CODE",
  "message": "Human-readable message",
  "details": { ... }
}
```

### Pattern 3: Environment-Based Configuration

**What:** API URL configurable via environment
**When:** Development vs production

```javascript
// .env.development
VITE_API_URL=/api/bookerville

// .env.production
VITE_API_URL=/api/bookerville
// or for cross-origin:
VITE_API_URL=https://api.example.com/api/bookerville
```

---

## Anti-Patterns to Avoid

### Anti-Pattern 1: Direct Bookerville Calls from React

**What:** Calling Bookerville XML API directly from frontend
**Why bad:** Exposes API keys, no caching, CORS issues with third-party
**Instead:** Always proxy through Laravel API

### Anti-Pattern 2: Hardcoded API URLs

**What:** `fetch('http://localhost:8000/api/...')` in production code
**Why bad:** Breaks in different environments
**Instead:** Use environment variables and relative paths

### Anti-Pattern 3: Ignoring Cache Headers

**What:** Not leveraging HTTP cache headers
**Why bad:** Increases server load, slower UX
**Instead:** Set appropriate Cache-Control headers in Laravel

```php
// In Laravel controller
return response()->json($data)
    ->header('Cache-Control', 'public, max-age=300');
```

### Anti-Pattern 4: Using `allowed_origins => ['*']` with Credentials

**What:** Wildcard CORS origin with `supports_credentials => true`
**Why bad:** Browsers block this combination for security
**Instead:** Explicitly list allowed origins

---

## Build Order / Phase Dependencies

### Phase 1: API Client Foundation
**Must complete first - all other features depend on this**
1. Create React API client (`src/lib/api/client.js`)
2. Create config file (`src/lib/api/config.js`)
3. Test connection to Laravel API endpoints

### Phase 2: Replace Mock Data
**Depends on Phase 1**
1. Update `featuredProperties` to fetch from `/api/bookerville/properties/cards`
2. Replace static data imports with API calls
3. Add loading states to components

### Phase 3: Build & Deploy Configuration
**Can run parallel to Phase 2**
1. Configure Vite build output
2. Add Laravel catch-all route
3. Test build locally

### Phase 4: Production Deployment
**Depends on Phase 2 and 3**
1. Run `npm run build` for React
2. Deploy Laravel with React build in public folder
3. Verify API endpoints work
4. Verify SPA routing works

---

## Scalability Considerations

| Concern | At 100 users | At 10K users | At 1M users |
|---------|--------------|--------------|-------------|
| API Response Time | Current caching sufficient | Add Redis caching | CDN + Edge caching |
| Static Assets | Served by Laravel | Nginx/Apache static serving | CDN (CloudFlare, etc.) |
| Database Load | MySQL handles easily | Read replicas | Sharding unlikely needed |
| Bookerville API | 300s cache sufficient | Increase cache TTL | Consider queue-based refresh |

**Note:** With 21 properties, current architecture handles all realistic traffic. Premature optimization is unnecessary.

---

## Development vs Production Configuration

### Development
```
React: http://localhost:3001 (Vite dev server)
Laravel: http://localhost:8000 (php artisan serve)
```

React Vite proxy config for development:
```javascript
// vite.config.js (development only)
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
});
```

### Production (Same-Origin)
```
Both: https://toplist-orlando.example.com
React: /react/* (static files)
API: /api/bookerville/* (Laravel)
Admin: /admin/* (Filament)
```

---

## Quality Checklist

- [x] Components clearly defined with boundaries
- [x] Data flow direction explicit
- [x] Build order implications noted
- [x] CORS configuration documented for both scenarios
- [x] Caching strategy aligned with existing 300s backend cache
- [x] Anti-patterns identified and prevented
- [x] Development vs production configuration documented

---

## Sources

### Official Documentation (HIGH confidence)
- [Laravel 11 Vite Asset Bundling](https://laravel.com/docs/11.x/vite)
- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/12.x/sanctum)

### Verified Community Patterns (MEDIUM confidence)
- [Laravel CORS Guide](https://www.stackhawk.com/blog/laravel-cors/)
- [Deploying Laravel + React to Shared Hosting](https://kritimyantra.com/blogs/how-to-deploy-laravel-12-with-reactjs-vite-on-shared-hosting-a-beginners-guide)
- [Laravel + Next.js Integration (similar patterns)](https://dzone.com/articles/laravel-nextjs-integration-guide-real-world-setup)
- [VPS Deployment Guide](https://dev.to/emmo00/self-guide-for-deploying-laravel-and-react-applications-on-a-vps-1o57)

### Existing Codebase Analysis (HIGH confidence)
- Current CORS config at `config/cors.php` - already allows `['*']` origins
- Sanctum middleware properly configured in `bootstrap/app.php`
- API routes defined at `/api/bookerville/*` (public) and `/api/v1/*` (authenticated)
- BookervilleService caching at 300 seconds already implemented
