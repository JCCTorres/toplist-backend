# Top List - Property Manager Website

## What This Is

A vacation rental property management website for Top List, showcasing ~21 Orlando-area properties. The React frontend connects to Bookerville API via Laravel backend for real property data, availability calendars, and date-based search. Guests browse, search by dates/guests, and book through Airbnb redirect. Professional dark-theme design with starfield animation, Montserrat/Poppins typography, and mobile-first responsiveness.

## Core Value

Guests can browse available properties, check real-time availability via Bookerville, and seamlessly proceed to book through Airbnb — with a polished, professional design that matches the quality of the vacation experience.

## Requirements

### Validated

- ✓ Laravel 11 backend with Bookerville API integration (6 endpoints) — existing
- ✓ React 18 + Vite frontend with 12 routes — existing
- ✓ Filament 3.3 admin panel with Property and User CRUD — existing
- ✓ Sanctum token-based API authentication — existing
- ✓ Property/Resort/Service image and video assets — existing
- ✓ SMTP email configuration — existing
- ✓ Property listings display real Bookerville data (names, images, beds/baths/guests) — v1.0
- ✓ Property detail page with real amenities, description, photos from Bookerville — v1.0
- ✓ Real-time availability calendar with blocked dates from Bookerville — v1.0
- ✓ Property cards show "Prices from $X/night" from Bookerville rates — v1.0
- ✓ Search by check-in/check-out dates — v1.0
- ✓ Filter by number of guests — v1.0
- ✓ Search results show only available properties — v1.0
- ✓ Book Now redirects to Airbnb with dates/guests pre-filled — v1.0
- ✓ Clear messaging that booking completes on Airbnb — v1.0
- ✓ Contact form sends email to property manager — v1.0
- ✓ Management inquiry form sends email to property manager — v1.0
- ✓ Professional dark-theme design across all pages — v1.0
- ✓ Loading states and error handling — v1.0
- ✓ Mobile responsive design — v1.0
- ✓ Production build configured — v1.0
- ✓ Environment variables secured — v1.0

### Active

(None — next milestone will define new requirements)

### Out of Scope

- Real-time chat — not needed for v1
- Direct booking (all bookings go through Airbnb) — client business model
- Payment processing — handled by Airbnb
- Mobile app — web-first
- Guest/owner portal functionality — admin panel covers management needs
- User registration — public site is browse-only, admin handles backend
- Offline mode — not applicable for property search site

## Context

- **v1.0 shipped** on 2026-02-08 — all 18 requirements delivered across 3 phases, 6 plans
- **Tech stack:** Laravel 11 backend + React 18 (Vite) frontend + Tailwind CSS
- **71 files** modified, 11,859 lines added during v1.0
- **Dark theme design** with Montserrat/Poppins typography, starfield animation, dual footer system
- **Bookerville API** is XML-based, authenticated via `s3cr3tK3y` parameter
- **~21 properties** in Orlando area — small dataset, no pagination concerns
- **Deployment:** Production build configured, Railway deployment guide created
- **Known debt:** CORS wildcard needs tightening, API credentials in .env.example

## Constraints

- **Tech stack**: Laravel 11 (PHP) backend + React 18 (Vite) frontend — locked
- **External API**: Bookerville XML API — all property data depends on this working
- **Booking flow**: Must redirect to Airbnb — no direct booking capability
- **Data**: Properties managed through Bookerville, not local database for listings
- **Hosting**: Railway (Laravel + React same-origin serving)

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Keep Laravel + React architecture | Existing codebase, no time to rebuild | ✓ Good — worked well |
| Bookerville as data source | Client's property management system | ✓ Good — XML API reliable |
| Airbnb for checkout | Client's booking platform | ✓ Good — clean redirect flow |
| Same-origin serving (Laravel serves React) | Avoids CORS issues | ✓ Good — simplified deployment |
| Native fetch over axios/react-query | Minimal deps for simple API layer | ✓ Good — no issues |
| URL params for search state | Shareable links, bookmarkable searches | ✓ Good — clean UX |
| Dark theme with Starfield animation | Luxury vacation aesthetic | ✓ Good — user approved |
| Montserrat/Poppins via Google Fonts CDN | Simple loading, no build dependencies | ✓ Good |
| Dual footer (SimpleFooter Home, FullFooter elsewhere) | Clean home page, info on inner pages | ✓ Good |
| Resorts removed from nav (files kept) | Not needed for v1, rollback possible | ✓ Good |
| Railway over Cloudways | Instant signup, no verification wait | ✓ Good |

---
*Last updated: 2026-02-08 after v1.0 milestone*
