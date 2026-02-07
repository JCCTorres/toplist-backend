# Phase 1: API Connection & Data Wiring - Context

**Gathered:** 2026-02-07
**Status:** Ready for planning

<domain>
## Phase Boundary

Connect React frontend to Bookerville API via Laravel backend so properties display real data instead of mock data. Property listings show real names, images, bed/bath/guest counts, and pricing. Property detail pages show real amenities, descriptions, photos, and an availability calendar. Loading and error states handle API latency and failures gracefully.

</domain>

<decisions>
## Implementation Decisions

### Data display mapping
- Property cards show rich info: photo, name, price per night, bed/bath/guest count, location, rating, and a brief description snippet
- When Bookerville data is missing a field, hide that field entirely — no placeholders, no empty space
- Availability calendar on property detail page should be an interactive date range picker with unavailable dates greyed out
- Photo gallery style on detail page is Claude's discretion based on existing frontend patterns

### Loading & error states
- Use a simple centered loading spinner while property data loads (not skeleton cards)
- API errors show a friendly message: "Unable to load properties, please try again later" with a retry button — no technical details exposed to users
- Detail page error handling is Claude's discretion (error with back link vs partial render)

### Claude's Discretion
- Photo gallery style on property detail page (pick what fits existing frontend)
- Caching strategy on Laravel backend (balance API limits vs data freshness)
- Detail page error behavior (error + back link vs partial render of available data)
- Exact spinner style and placement

</decisions>

<specifics>
## Specific Ideas

- Rich property cards — user wants visitors to get a full picture at a glance without clicking into details
- Date range picker for availability, not just a static calendar — interactive selection of check-in/check-out
- Keep error messaging simple and non-technical — this is a vacation rental site for guests, not developers

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope

</deferred>

---

*Phase: 01-api-connection-data-wiring*
*Context gathered: 2026-02-07*
