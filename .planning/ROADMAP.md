# Roadmap: Top List Property Manager

## Overview

This brownfield project is ~80% complete with Laravel backend and React frontend built but disconnected. The roadmap connects frontend to Bookerville API, wires search and booking flows, then polishes design and deploys. Three phases follow the critical path: API connection first (unlocks all features), then search/booking (core user journey), then polish/deploy (production-ready). Must ship today.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

- [x] **Phase 1: API Connection & Data Wiring** - Connect React frontend to Bookerville API via Laravel backend *(Completed 2026-02-07)*
- [ ] **Phase 2: Search, Availability & Booking Flow** - Wire date search, availability filtering, and Airbnb checkout
- [ ] **Phase 3: Design Polish & Deployment** - Finalize design, secure environment, deploy to production

## Phase Details

### Phase 1: API Connection & Data Wiring
**Goal**: Properties display real data from Bookerville API instead of mock data
**Depends on**: Nothing (first phase)
**Requirements**: API-01, API-02, API-03, API-04, UI-03, UI-04
**Success Criteria** (what must be TRUE):
  1. Property listings page shows real property names, images, and bed/bath/guest counts from Bookerville
  2. Property detail page displays real amenities, description, photos, and availability calendar
  3. Property cards show "Prices from $X/night" using actual Bookerville rates
  4. Loading spinners appear during API calls; error messages display when API unavailable
**Plans**: 2 plans

Plans:
- [x] 01-01: Create API client and connect property listings to Bookerville
- [x] 01-02: Connect property detail pages with availability calendar and error handling

### Phase 2: Search, Availability & Booking Flow
**Goal**: Users can search by dates, filter results, and complete booking via Airbnb redirect
**Depends on**: Phase 1
**Requirements**: SRCH-01, SRCH-02, SRCH-03, BOOK-01, BOOK-02, BOOK-03, BOOK-04
**Success Criteria** (what must be TRUE):
  1. User can enter check-in and check-out dates and see only available properties
  2. User can filter properties by number of guests
  3. "Book Now" button redirects to Airbnb checkout with dates and guest count pre-filled
  4. Clear messaging indicates booking completes on Airbnb
  5. Contact form and management inquiry form successfully send emails to property manager
**Plans**: 2 plans

Plans:
- [ ] 02-01-PLAN.md - Wire search bar to API and Properties page with search params
- [ ] 02-02-PLAN.md - Wire Book Now to Airbnb redirect and connect email forms

### Phase 3: Design Polish & Deployment
**Goal**: Production-ready site with professional design matching mastervacationhomes.com quality
**Depends on**: Phase 2
**Requirements**: UI-01, UI-02, UI-05, DEPLOY-01, DEPLOY-02
**Success Criteria** (what must be TRUE):
  1. All pages (Home, Properties, Property Detail, Resorts, Services, Contact, Management) have consistent, professional design
  2. Design matches mastervacationhomes.com quality level (clean, modern, vacation-appropriate)
  3. Site is fully responsive and polished on mobile devices
  4. Production build is deployed and accessible
  5. API keys and sensitive credentials are secured (not in source code)
**Plans**: 2 plans

Plans:
- [ ] 03-01: Design polish across all pages with mobile verification
- [ ] 03-02: Production build configuration and deployment

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. API Connection & Data Wiring | 2/2 | ✓ Complete | 2026-02-07 |
| 2. Search, Availability & Booking Flow | 0/2 | Not started | - |
| 3. Design Polish & Deployment | 0/2 | Not started | - |

---
*Roadmap created: 2026-02-07*
*Depth: quick (3 phases, 6 plans total)*
*Coverage: 18/18 v1 requirements mapped*
