# Phase 02: Search, Availability & Booking Flow - Research

**Researched:** 2026-02-07
**Domain:** React Search UI + API Integration + Email Forms + Airbnb Redirect
**Confidence:** HIGH

## Summary

This phase implements the complete search-to-booking flow: users search by dates/guests, see filtered available properties, and redirect to Airbnb to complete booking. The backend already provides all necessary APIs (multi-property search, availability checking, Airbnb checkout URL generation, email sending). The frontend has search bar UI components (SearchBar.jsx, useSearchFilters hook) but they currently only log to console.

The implementation requires:
1. Connecting SearchBar to the multi-property search API (`POST /api/bookerville/properties/search`)
2. Creating a search results page showing filtered available properties
3. Wiring "Book Now" on PropertyDetails to call the Airbnb checkout API and redirect
4. Adding "Booking completes on Airbnb" messaging
5. Connecting ContactForm and ManagementForm to existing email APIs

**Primary recommendation:** The backend is fully ready. Frontend needs minimal additions: (1) a SearchResults page/component, (2) API calls for search and email, and (3) the Airbnb redirect button logic. All APIs are public (no auth required) and already tested.

## Standard Stack

### Core (Already Installed)
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| React | 18.2.0 | UI framework | Already in package.json |
| react-flatpickr | 3.10.13 | Date range picker | Already in SearchBar.jsx |
| flatpickr | 4.6.13 | Date picker core | Already in package.json |
| react-router-dom | 6.22.3 | Routing + navigation | For search results page route |
| Tailwind CSS | 3.4.1 | Styling | Already used throughout |

### Supporting (Already Available)
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @mui/material | 7.1.0 | UI components | Loading spinners, buttons |

### No Additional Packages Needed
All required dependencies are installed. The existing `api.js` service will be extended with new methods.

## Architecture Patterns

### Recommended Project Structure
```
src/
├── services/
│   └── api.js                    # EXTEND - Add searchProperties, sendContactEmail, sendManagementEmail, getAirbnbCheckoutUrl
├── hooks/
│   └── useApi.js                 # EXISTING - Reuse for search and email
│   └── useSearchFilters.js       # MODIFY - Wire to API, add navigation
├── components/
│   └── SearchResults.jsx         # NEW - Display search results grid
│   └── AirbnbRedirectMessage.jsx # NEW - "Booking completes on Airbnb" notice
├── pages/
│   └── Properties.jsx            # MODIFY - Accept search params, show filtered results
│   └── PropertyDetails.jsx       # MODIFY - Wire Book Now to Airbnb redirect
└── features/home/components/
    ├── HeroSection/SearchBar.jsx # MODIFY - Submit searches API
    ├── ContactSection/ContactForm.jsx  # MODIFY - Submit to email API
    └── ManagementSection/ManagementForm.jsx # MODIFY - Submit to email API
```

### Pattern 1: Multi-Property Search API Call
**What:** Search for available properties by dates and guests
**When to use:** When user submits search form
**Example:**
```javascript
// src/services/api.js - Add this method
async searchProperties(searchParams) {
  // searchParams: { startDate, endDate, numAdults, numChildren }
  const response = await fetch(`${BASE_URL}/properties/search`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      startDate: searchParams.startDate,  // 'YYYY-MM-DD'
      endDate: searchParams.endDate,      // 'YYYY-MM-DD'
      numAdults: searchParams.numAdults || 1,
      numChildren: searchParams.numChildren || 0
    })
  });
  if (!response.ok) throw new Error('Search failed');
  return response.json();
}

// Response shape from API:
// { success: true, data: { request: {...}, results: [...], total_results: 5 } }
// Each result: { property_id, airbnb_id, property_name, price, max_guests, main_image, is_available, booking_target_url, ... }
```

### Pattern 2: Airbnb Checkout URL Generation
**What:** Get pre-filled Airbnb checkout URL with dates and guests
**When to use:** User clicks "Book Now" on property detail page
**Example:**
```javascript
// src/services/api.js - Add this method
async getAirbnbCheckoutUrl(airbnbId, bookingParams) {
  // airbnbId: The Airbnb listing ID (from property.airbnb_id)
  // bookingParams: { checkin, checkout, numberOfAdults, numberOfChildren }
  const response = await fetch(`${BASE_URL}/airbnb/${airbnbId}/checkout-link`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(bookingParams)
  });
  if (!response.ok) throw new Error('Failed to generate checkout URL');
  return response.json();
}

// Response: { success: true, data: { checkout_url: 'https://www.airbnb.com.br/book/stays/12345?checkin=...' } }

// In PropertyDetails.jsx:
const handleBookNow = async () => {
  if (!property.airbnb_id) {
    alert('This property cannot be booked online. Please contact us.');
    return;
  }
  if (selectedDates.length !== 2) {
    alert('Please select check-in and check-out dates.');
    return;
  }

  const response = await api.getAirbnbCheckoutUrl(property.airbnb_id, {
    checkin: formatDate(selectedDates[0]),
    checkout: formatDate(selectedDates[1]),
    numberOfAdults: guestCount,
    numberOfChildren: childrenCount
  });

  if (response.success) {
    window.open(response.data.checkout_url, '_blank');
  }
};
```

### Pattern 3: Contact Form Email Submission
**What:** Send contact form data to property manager via email API
**When to use:** User submits contact form
**Example:**
```javascript
// src/services/api.js - Add this method
async sendContactEmail(formData) {
  // formData: { name, email, phone, message }
  const response = await fetch('/api/email/contact', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  });
  return response.json();
}

// Response: { success: true, message: 'Thank you for your message!' }
// Error: { success: false, error: 'MISSING_FIELDS', message: '...' }

// In ContactForm.jsx:
const handleSubmit = async (e) => {
  e.preventDefault();
  setSubmitting(true);
  try {
    const result = await api.sendContactEmail({ name, email, phone, message });
    if (result.success) {
      setSuccess(true);
      // Clear form
    } else {
      setError(result.message);
    }
  } catch (err) {
    setError('Failed to send message. Please try again.');
  } finally {
    setSubmitting(false);
  }
};
```

### Pattern 4: Management Inquiry Email
**What:** Send management inquiry form to property manager
**When to use:** Property owner submits inquiry about listing their property
**Example:**
```javascript
// src/services/api.js - Add this method
async sendManagementEmail(formData) {
  // formData: { name, email, propertyType, bedrooms, message }
  const response = await fetch('/api/email/management-request', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  });
  return response.json();
}
// Same pattern as contact form
```

### Pattern 5: Search Results Navigation
**What:** Navigate to properties page with search parameters
**When to use:** User submits search from HeroSection
**Example:**
```javascript
// In useSearchFilters.js
import { useNavigate } from 'react-router-dom';

const handleSearch = () => {
  const navigate = useNavigate();

  if (dates.length !== 2) {
    alert('Please select check-in and check-out dates');
    return;
  }

  // Navigate to properties page with search params
  const params = new URLSearchParams({
    checkin: formatDate(dates[0]),
    checkout: formatDate(dates[1]),
    adults: guests || '1',
    children: houseName || '0'  // houseName is actually children count in current UI
  });

  navigate(`/homes?${params.toString()}`);
};

// In Properties.jsx, read URL params and fetch filtered results
import { useSearchParams } from 'react-router-dom';

function Properties() {
  const [searchParams] = useSearchParams();
  const checkin = searchParams.get('checkin');
  const checkout = searchParams.get('checkout');
  const adults = searchParams.get('adults');
  const children = searchParams.get('children');

  const hasSearchParams = checkin && checkout;

  const { data, loading, error } = useApi(
    () => hasSearchParams
      ? api.searchProperties({ startDate: checkin, endDate: checkout, numAdults: adults, numChildren: children })
      : api.getProperties()
  , [checkin, checkout, adults, children]);

  const properties = hasSearchParams
    ? data?.data?.results || []
    : data?.data?.properties || [];

  // ...render
}
```

### Anti-Patterns to Avoid
- **Client-side date validation only:** Backend validates dates too, but show user-friendly errors before API call
- **Opening Airbnb in same tab:** Always use `window.open(..., '_blank')` so user doesn't lose their place
- **Hiding the Airbnb redirect:** Clear messaging that booking completes on Airbnb (legal/UX requirement)
- **Blocking form submit on error:** Show error but let user retry, don't permanently disable

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Date range selection | Custom calendar | react-flatpickr (mode: 'range') | Already in use, battle-tested |
| Search API with availability | Custom availability check | `/api/bookerville/properties/search` | Backend already does multi-property search |
| Airbnb URL construction | Manual URL building | `/api/bookerville/airbnb/{id}/checkout-link` | Backend handles parameter mapping, currency, etc. |
| Email sending | SMTP client in React | `/api/email/contact` and `/api/email/management-request` | Backend has SMTP configured |
| Form validation | Custom validators | HTML5 validation + backend validation | EmailController already validates |

**Key insight:** The Laravel backend is feature-complete for this phase. All search, availability, checkout URL, and email APIs are built, tested, and working. Frontend work is purely UI/UX wiring.

## Common Pitfalls

### Pitfall 1: Date Format Mismatch
**What goes wrong:** API expects `YYYY-MM-DD` but Flatpickr may return Date objects or different format
**Why it happens:** Flatpickr's dateFormat affects display, not the actual Date values returned
**How to avoid:** Always format dates before sending to API:
```javascript
const formatDate = (date) => date.toISOString().split('T')[0]; // 'YYYY-MM-DD'
```
**Warning signs:** 400 errors from API, "Invalid date format" messages

### Pitfall 2: Missing airbnb_id for Checkout
**What goes wrong:** Property exists but has no `airbnb_id`, checkout URL generation fails
**Why it happens:** Some properties in Bookerville may not be listed on Airbnb
**How to avoid:** Check `property.airbnb_id` before showing "Book Now", fall back to contact form
```javascript
{property.airbnb_id ? (
  <button onClick={handleBookNow}>Book Now on Airbnb</button>
) : (
  <p>Contact us to book this property</p>
)}
```
**Warning signs:** 404 from checkout URL API, "Property not found" errors

### Pitfall 3: Search with No Results
**What goes wrong:** User searches dates with no availability, sees empty page
**Why it happens:** All properties booked for those dates
**How to avoid:** Show friendly "No properties available" message with suggestions (try different dates, contact us)
**Warning signs:** Empty results array, users abandoning site

### Pitfall 4: Email Form Double Submit
**What goes wrong:** User clicks submit multiple times, sends multiple emails
**Why it happens:** No loading state disabling the button
**How to avoid:** Set `submitting` state, disable button while submitting
**Warning signs:** Duplicate emails, confused users

### Pitfall 5: Search Results Not Showing Price
**What goes wrong:** Price shows as $0 or undefined
**Why it happens:** API returns `booking_price` not just `price`, or price is in different field
**How to avoid:** Check API response shape:
```javascript
// From multiPropertySearch API:
// result.price or result.booking_price - API normalizes to 'price'
const price = result.price || result.booking_price || 0;
```
**Warning signs:** "$0/night" displayed, NaN in price

## Code Examples

### Extended api.js with Phase 2 Methods
```javascript
// src/services/api.js - EXTEND with these methods

/**
 * Search properties by availability, dates, and guests
 * @param {Object} params - { startDate, endDate, numAdults, numChildren }
 * @returns {Promise<{success: boolean, data: {results: Array, total_results: number}}>}
 */
searchProperties: async (params) => {
  const response = await fetch(`${BASE_URL}/properties/search`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      startDate: params.startDate,
      endDate: params.endDate,
      numAdults: parseInt(params.numAdults) || 1,
      numChildren: parseInt(params.numChildren) || 0
    })
  });
  return handleResponse(response);
},

/**
 * Generate Airbnb checkout URL with pre-filled dates/guests
 * @param {string} airbnbId - Airbnb listing ID
 * @param {Object} params - { checkin, checkout, numberOfAdults, numberOfChildren }
 * @returns {Promise<{success: boolean, data: {checkout_url: string}}>}
 */
getAirbnbCheckoutUrl: async (airbnbId, params) => {
  const response = await fetch(`${BASE_URL}/airbnb/${airbnbId}/checkout-link`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      checkin: params.checkin,
      checkout: params.checkout,
      numberOfGuests: (parseInt(params.numberOfAdults) || 1) + (parseInt(params.numberOfChildren) || 0),
      numberOfAdults: parseInt(params.numberOfAdults) || 1,
      numberOfChildren: parseInt(params.numberOfChildren) || 0
    })
  });
  return handleResponse(response);
},

/**
 * Send contact form email
 * @param {Object} data - { name, email, phone, message }
 * @returns {Promise<{success: boolean, message: string}>}
 */
sendContactEmail: async (data) => {
  const response = await fetch('/api/email/contact', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  return handleResponse(response);
},

/**
 * Send management inquiry email
 * @param {Object} data - { name, email, propertyType, bedrooms, message }
 * @returns {Promise<{success: boolean, message: string}>}
 */
sendManagementEmail: async (data) => {
  const response = await fetch('/api/email/management-request', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  return handleResponse(response);
}
```

### Airbnb Redirect Notice Component
```javascript
// src/components/AirbnbRedirectMessage.jsx
export default function AirbnbRedirectMessage() {
  return (
    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
      <div className="flex items-start">
        <svg className="w-5 h-5 text-blue-500 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
        </svg>
        <p className="text-sm text-blue-700">
          <strong>Secure Booking:</strong> You will be redirected to Airbnb to complete your reservation.
          All payments are processed securely through Airbnb's platform.
        </p>
      </div>
    </div>
  );
}
```

### ContactForm with Email API
```javascript
// src/features/home/components/ContactSection/ContactForm.jsx
import React, { useState } from 'react';
import { api } from '../../../../services/api';

const ContactForm = () => {
  const [formData, setFormData] = useState({ name: '', email: '', phone: '', message: '' });
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.id]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      const result = await api.sendContactEmail(formData);
      if (result.success) {
        setSuccess(true);
        setFormData({ name: '', email: '', phone: '', message: '' });
      } else {
        setError(result.message || 'Failed to send message');
      }
    } catch (err) {
      setError('Failed to send message. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  if (success) {
    return (
      <div className="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <h3 className="text-xl font-semibold text-green-800 mb-2">Message Sent!</h3>
        <p className="text-green-700">Thank you for contacting us. We'll get back to you soon.</p>
        <button
          onClick={() => setSuccess(false)}
          className="mt-4 text-green-600 underline"
        >
          Send another message
        </button>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-8">
      <form onSubmit={handleSubmit}>
        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
            {error}
          </div>
        )}
        {/* Form fields with handleChange */}
        <button
          type="submit"
          disabled={submitting}
          className="w-full bg-pink-400 text-white py-3 px-6 rounded-lg hover:bg-pink-500 disabled:opacity-50"
        >
          {submitting ? 'Sending...' : 'Send Message'}
        </button>
      </form>
    </div>
  );
};

export default ContactForm;
```

## Backend API Reference

### Available Endpoints (All Public, No Auth Required)

| Endpoint | Method | Purpose | Request Body |
|----------|--------|---------|--------------|
| `/api/bookerville/properties/search` | POST | Multi-property availability search | `{ startDate, endDate, numAdults, numChildren }` |
| `/api/bookerville/airbnb/{airbnbId}/checkout-link` | POST | Generate Airbnb checkout URL | `{ checkin, checkout, numberOfGuests, numberOfAdults, numberOfChildren }` |
| `/api/email/contact` | POST | Send contact form email | `{ name, email, phone, message }` |
| `/api/email/management-request` | POST | Send management inquiry | `{ name, email, propertyType, bedrooms, message }` |

### Search API Response Shape
```javascript
{
  "success": true,
  "data": {
    "request": { "startDate": "2026-02-10", "endDate": "2026-02-15", ... },
    "results": [
      {
        "property_id": "11684",
        "airbnb_id": "12345678",
        "property_name": "Lake Berkley House 1",
        "address": "123 Main St",
        "city": "Orlando",
        "price": 150.00,
        "max_guests": 8,
        "description": "Beautiful lakefront home...",
        "main_image": "https://...",
        "is_available": true,
        "booking_target_url": "https://bookerville.com/...",
        "b_b": { "bedrooms": 4, "bathrooms": 3 }
      }
    ],
    "total_results": 5
  }
}
```

### Airbnb Checkout URL Response
```javascript
{
  "success": true,
  "data": {
    "checkout_url": "https://www.airbnb.com.br/book/stays/12345678?checkin=2026-02-10&checkout=2026-02-15&numberOfGuests=4&numberOfAdults=2&numberOfChildren=2&guestCurrency=BRL&productId=12345678"
  }
}
```

### Email API Response
```javascript
// Success
{
  "success": true,
  "message": "Thank you for your message! We will get back to you soon.",
  "data": { "timestamp": "2026-02-07T15:00:00Z", "contact": { "name": "...", "email": "..." } }
}

// Error
{
  "success": false,
  "error": "MISSING_FIELDS",
  "message": "All fields are required: name, email, phone, message"
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Console.log in handleSearch | API call + navigation | This phase | Real search functionality |
| Hardcoded reviews | API reviews (future) | v2 | Guest reviews from Bookerville |
| No booking button | Airbnb redirect | This phase | Complete booking flow |

**Deprecated/outdated:**
- `alert('Search functionality would be implemented here')` in useSearchFilters.js - Replace with API call

## Open Questions

1. **Guest count vs Adults + Children split**
   - What we know: SearchBar has `guests` (Adults) and `houseName` (actually Children)
   - What's unclear: Are these labels correct? "houseName" is confusing
   - Recommendation: Rename in UI or keep as-is for quick ship, document for v2

2. **Properties without airbnb_id**
   - What we know: Some Bookerville properties may not have Airbnb listings
   - What's unclear: How many? What's the fallback?
   - Recommendation: Show "Contact us to book" for properties without airbnb_id

3. **Search results persistence**
   - What we know: User navigates to property detail, then back
   - What's unclear: Should search results be cached/preserved?
   - Recommendation: URL params preserve search, re-fetch on back navigation (simple, works)

## Sources

### Primary (HIGH confidence)
- Codebase analysis: `routes/api.php` - All API routes verified
- Codebase analysis: `BookervilleService.php` - Multi-property search implementation
- Codebase analysis: `BookervilleController.php` - Airbnb checkout URL generation (buildAirbnbCheckoutUrl)
- Codebase analysis: `EmailController.php` and `EmailService.php` - Email endpoints and validation
- Codebase analysis: `SearchBar.jsx`, `useSearchFilters.js` - Current frontend implementation

### Secondary (MEDIUM confidence)
- [Flatpickr official examples](https://flatpickr.js.org/examples/) - Date range and disable patterns
- [react-flatpickr npm](https://www.npmjs.com/package/react-flatpickr) - React wrapper API

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - All libraries already installed, no additions needed
- Architecture: HIGH - Extends existing patterns from Phase 1 (api.js, useApi hook)
- API integration: HIGH - All endpoints verified in codebase, response shapes documented
- Pitfalls: HIGH - Derived from actual backend response shapes and frontend state

**Research date:** 2026-02-07
**Valid until:** 2026-03-07 (30 days - stable backend, no expected changes)
