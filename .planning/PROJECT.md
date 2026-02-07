# Top List - Property Manager Website

## What This Is

A vacation rental property management website for Top List, showcasing ~21 Orlando-area properties. The site connects to Bookerville (property management system) for property data and availability, and redirects guests to Airbnb for checkout. It's a multi-page React frontend backed by a Laravel API, with a Filament admin panel.

## Core Value

Guests can browse available properties, check real-time availability via Bookerville, and seamlessly proceed to book through Airbnb — with a polished, professional design that matches the quality of the vacation experience.

## Requirements

### Validated

- ✓ Laravel 11 backend with Bookerville API integration (6 endpoints) — existing
- ✓ React 18 + Vite frontend with 12 routes — existing
- ✓ Filament 3.3 admin panel with Property and User CRUD — existing
- ✓ Airbnb checkout URL generation with date/guest parameters — existing
- ✓ Sanctum token-based API authentication — existing
- ✓ Tailwind CSS + MUI responsive design — existing
- ✓ Property/Resort/Service image and video assets — existing
- ✓ SMTP email configuration — existing

### Active

- [ ] Connect frontend to Bookerville API (currently using mock data)
- [ ] Real-time availability calendar on property detail pages
- [ ] Search/filter properties by dates, guests, bedrooms, bathrooms
- [ ] Multi-page layout (Home, Properties, Resorts, Services, Property Detail, Resort Detail, Contact, Management)
- [ ] Airbnb checkout redirect from property detail page (frontend integration)
- [ ] Frontend design polish — professional, modern, matching mastervacationhomes.com quality
- [ ] Contact form backend integration (send emails)
- [ ] Management inquiry form backend integration
- [ ] Loading states and error handling across all pages
- [ ] Production deployment setup

### Out of Scope

- Real-time chat — not needed for v1
- Direct booking (all bookings go through Airbnb) — client business model
- Payment processing — handled by Airbnb
- Mobile app — web-first
- Guest/owner portal functionality — admin panel covers management needs
- User registration — public site is browse-only, admin handles backend

## Context

- **Existing codebase** built by previous developer, ~70-80% complete
- **Backend** is well-structured: Bookerville service layer with caching, error handling, rate limiting
- **Frontend** has all pages/routes but uses mock data — needs API connection
- **Bookerville API** is XML-based, authenticated via `s3cr3tK3y` parameter. Credentials already in `.env`
- **Airbnb redirect** logic exists in backend controller, needs frontend wiring
- **Design reference:** https://mastervacationhomes.com/ — multi-page, clean, professional vacation rental site
- **~21 properties** in Orlando area — small dataset, no pagination concerns
- **Tight deadline** — must be production-ready today
- **API key:** Bookerville credentials already configured in backend `.env`

## Constraints

- **Tech stack**: Laravel 11 (PHP) backend + React 18 (Vite) frontend — locked, existing code
- **Timeline**: Must ship to production today
- **External API**: Bookerville XML API — all property data depends on this working
- **Booking flow**: Must redirect to Airbnb — no direct booking capability
- **Design**: Must match quality/professionalism of mastervacationhomes.com reference
- **Data**: Properties managed through Bookerville, not local database for listings
- **Hosting**: TBD — needs to support both PHP (Laravel) and static React build

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Keep Laravel + React architecture | Existing codebase, no time to rebuild | -- Pending |
| Bookerville as data source | Client's property management system | -- Pending |
| Airbnb for checkout | Client's booking platform | -- Pending |
| Multi-page layout over SPA single-page | Client preference, matches reference site | -- Pending |
| Deploy both backend + frontend | Laravel serves API, React builds to static | -- Pending |

---
*Last updated: 2026-02-07 after initialization*
