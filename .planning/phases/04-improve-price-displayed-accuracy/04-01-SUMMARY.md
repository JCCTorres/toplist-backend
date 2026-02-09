---
phase: 04-improve-price-displayed-accuracy
plan: 01
subsystem: api/price-calculation
tags: [backend, api, price-calculation, bookerville]
dependency_graph:
  requires: []
  provides: [price-estimate-api, price-calculator-service]
  affects: [property-details-page, search-results]
tech_stack:
  added: []
  patterns: [service-class, carbon-dates, fee-calculation]
key_files:
  created:
    - app/Services/PriceCalculatorService.php
  modified:
    - app/Http/Controllers/Api/BookervilleController.php
    - routes/api.php
decisions:
  - id: airbnb-fee-estimate
    choice: Use 14.2% as average Airbnb guest service fee
    rationale: Industry standard average based on Airbnb's typical 14-16% range
metrics:
  duration: ~2 minutes
  completed: 2026-02-09
---

# Phase 4 Plan 01: Backend Price Calculation Service Summary

Price calculation service with Bookerville data integration and API endpoint for frontend price estimates.

## One-liner

Backend service calculating stay prices using Bookerville rates/fees with configurable weekend nights and Airbnb fee estimation.

## What Was Built

### PriceCalculatorService (app/Services/PriceCalculatorService.php)

New service class providing price calculation capabilities:

- **calculateStayPrice()**: Calculates complete price breakdown including:
  - Base price (weekday nights x nightly rate + weekend nights x weekend rate)
  - Cleaning fee
  - Additional guest fee (for guests exceeding free guest count)
  - Tax amount (based on property tax rate)
  - Estimated Airbnb service fee (14.2%)
  - Total estimated price

- **findApplicableRate()**: Finds the correct seasonal rate based on check-in date by matching against rate period date ranges. Falls back to first rate if no match.

- **countWeekendNights()**: Counts weekend nights based on property's weekend_nights configuration (e.g., 'Fri|Sat' or 'Fri|Sat|Sun').

### API Endpoint

**POST /api/bookerville/properties/{propertyId}/price-estimate**

Request body:
```json
{
  "checkIn": "2025-03-14",
  "checkOut": "2025-03-17",
  "guests": 2
}
```

Response:
```json
{
  "success": true,
  "data": {
    "nights": 3,
    "weekday_nights": 1,
    "weekend_nights": 2,
    "nightly_avg": 233.33,
    "base_price": 700,
    "cleaning_fee": 150,
    "additional_guest_fee": 0,
    "subtotal": 850,
    "tax_rate": 13.5,
    "tax_amount": 114.75,
    "estimated_airbnb_fee": 120.70,
    "estimated_total": 1085.45,
    "currency": "USD",
    "rate_season": "Default",
    "guests": 2,
    "free_guests": 2
  },
  "property_id": "8558",
  "property_name": "1020 Baseball And Boardwalk Ct"
}
```

## Commits

| Task | Description | Commit | Files |
|------|-------------|--------|-------|
| 1 | Create PriceCalculatorService | 40c2959 | app/Services/PriceCalculatorService.php |
| 2 | Add API endpoint and route | 8934975 | BookervilleController.php, api.php |

## Deviations from Plan

None - plan executed exactly as written.

## Self-Check

- [x] app/Services/PriceCalculatorService.php exists
- [x] Commit 40c2959 exists
- [x] Commit 8934975 exists
- [x] Route registered in api.php
- [x] getPriceEstimate method in BookervilleController

## Self-Check: PASSED

## Next Steps

Plan 04-02 will integrate this API with the frontend PropertyDetailsPage to display accurate price estimates instead of placeholder data.
