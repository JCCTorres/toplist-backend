# Feature Landscape: Vacation Rental Property Website

**Domain:** Vacation rental property management website (Orlando market, ~21 properties)
**Researched:** 2026-02-07
**Confidence:** MEDIUM (WebSearch verified against reference site and competitor analysis)

---

## Table Stakes

Features users expect. Missing = users leave for Airbnb/VRBO.

| Feature | Why Expected | Complexity | Notes | Status |
|---------|--------------|------------|-------|--------|
| **Property listing with photos** | Users rely heavily on visuals to make booking decisions. Listings with professional photos receive 40% more bookings. | Low | Already have PropertyCard, need API data | PARTIAL - mock data |
| **Property details page** | Users need beds/baths/guests/amenities to evaluate fit. Standard across all rental sites. | Low | Already built, needs real data | PARTIAL - hardcoded |
| **Search by dates** | Primary filter. Users always know their travel dates first. "Can I stay here when I need to?" | Medium | SearchBar exists with Flatpickr date picker, needs API connection | PARTIAL - UI only |
| **Guest count filter** | Groups need to know if property fits. VRBO/Airbnb default. | Low | SearchBar has guests input, needs filtering logic | PARTIAL - UI only |
| **Availability display** | Users must see if dates are available before clicking to book. Prevents wasted time. | Medium | Bookerville API provides blocked dates. Need calendar visualization. | BACKEND READY |
| **Property price display** | Users filter by budget. Price per night is universal expectation. | Low | Bookerville API provides pricing. Frontend uses $81.29 placeholder. | BACKEND READY |
| **Mobile responsive design** | 52%+ of web traffic is mobile. Non-responsive = lost bookings. | Low | Tailwind CSS in use, likely responsive | VERIFY |
| **Clear booking CTA** | Users must know how to book. "Book Now" button is expected. | Low | Button exists, needs Airbnb redirect wiring | PARTIAL |
| **Contact information** | Users want to reach a human for questions. Missing = distrust. | Low | Contact form exists, footer likely has info | PARTIAL |
| **Property location/resort info** | Orlando visitors choose by proximity to parks. Resort groupings matter. | Low | Resort cards exist, need API data | PARTIAL |

### Table Stakes Priority Order (for today's ship)

1. **Connect frontend to Bookerville API** - Foundation for everything
2. **Search by dates + availability filtering** - Primary user journey
3. **Airbnb checkout redirect** - Completes booking flow
4. **Real availability calendar** - Prevents double-booking frustration
5. **Real pricing display** - Users need accurate prices

---

## Differentiators

Features that set the product apart. Not expected, but create competitive advantage.

| Feature | Value Proposition | Complexity | Notes | Recommendation |
|---------|-------------------|------------|-------|----------------|
| **Resort-based groupings** | Orlando visitors often prefer specific resort communities (Windsor Hills, Solara). Organizing by resort helps users find properties near friends/family. | Low | Data structure exists, just needs presentation | BUILD - low effort, high value |
| **Amenity filtering (pool, game room)** | Families want specific amenities. mastervacationhomes.com has this. Saves time vs reading every listing. | Medium | SearchBar has commented-out options dropdown with pool/game room checkboxes | ENABLE - code exists |
| **Guest reviews display** | "91% of consumers read online reviews." Builds trust. | Low | Bookerville API has guest reviews endpoint. PropertyDetails has review UI. | BUILD - API ready |
| **Multi-property search** | "Show me all properties available for my dates" - powerful for group travel. | Medium | Bookerville has multi-property-availability-search API | DEFER - nice but not essential |
| **Distance to Disney indicator** | Orlando-specific differentiator. Parks proximity is major decision factor. | Low | Would need data enrichment. SearchBar has "Near Disney" checkbox (commented). | DEFER - requires data |
| **Photo gallery with lightbox** | Already implemented. Sets apart from basic sites. | N/A | PropertyDetails has modal gallery | DONE |
| **WhatsApp contact** | Quick communication, popular internationally. Reference site has it. | Low | Simple link | BUILD IF TIME |

### Differentiators Priority (post-MVP or if time allows)

1. Enable amenity filtering - code already exists
2. Guest reviews display - API ready, UI exists
3. WhatsApp contact - simple addition

---

## Anti-Features

Features to explicitly NOT build. Common mistakes in vacation rental domain.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| **Direct booking/payment processing** | Client model is Airbnb checkout. Building payment = scope creep, liability, security burden, PCI compliance. | Redirect to Airbnb with pre-filled parameters |
| **User registration/accounts** | Browse-only public site. Accounts add friction, require password reset flows, privacy compliance. | Keep site anonymous. Admin panel handles backend. |
| **Real-time chat widget** | Requires 24/7 staffing or bot maintenance. Contact form suffices for 21-property operation. | Contact form + phone number + WhatsApp link |
| **Dynamic pricing engine** | Pricing comes from Bookerville/Airbnb. Duplicating creates sync issues. | Display Bookerville prices, let Airbnb handle final pricing |
| **Calendar blocking/management on frontend** | Property management happens in Bookerville. Duplicating creates double-booking risk. | Read-only availability display from API |
| **Reviews collection system** | Reviews come through Airbnb/Bookerville. Own system = fragmented reputation. | Display reviews from Bookerville API |
| **Instant booking confirmation** | Airbnb handles booking confirmation. Site only redirects. | Clear messaging: "You'll complete booking on Airbnb" |
| **Virtual tours/3D walkthroughs** | High production cost, requires specialized content. Photos suffice for 21 properties. | High-quality photo galleries |
| **AI chatbot/negotiation agent** | Overly complex for launch. Trends for 2026, not essentials. | Simple contact options |
| **Blog/content marketing** | Content needs ongoing maintenance. Not essential for launch. | Add post-launch if SEO becomes priority |
| **Multi-language support** | Reference site has it, but adds complexity. Ship English first. | DEFER - add if international bookings are significant |

---

## Feature Dependencies

```
BOOKERVILLE API CONNECTION (foundation)
    |
    +---> Property Listings (real data)
    |         |
    |         +---> Property Detail Pages (real data)
    |         |         |
    |         |         +---> Availability Calendar
    |         |         +---> Pricing Display
    |         |         +---> Guest Reviews
    |         |         +---> Airbnb Checkout Redirect
    |         |
    |         +---> Search/Filter Results
    |                   |
    |                   +---> Date-based Availability Search
    |                   +---> Guest Count Filtering
    |                   +---> Amenity Filtering (optional)
    |
    +---> Resort Listings (real data)

CONTACT FORM ---> Email Backend (independent)
MANAGEMENT FORM ---> Email Backend (independent)
```

**Critical Path:** Bookerville API -> Property Listings -> Property Detail -> Airbnb Redirect

**Parallel Work:** Contact/Management forms (independent of Bookerville)

---

## MVP Recommendation (Ship Today)

### Must Have (Blocks Launch)

1. **Connect Property Listings to Bookerville API**
   - Replace mock data in `featuredProperties` with API call
   - Display real property names, images, beds/baths/guests
   - Complexity: Medium (API services exist, need frontend wiring)

2. **Connect Property Details to Bookerville API**
   - Fetch property details by ID
   - Display real amenities, description, pricing
   - Show real availability (blocked dates calendar)
   - Complexity: Medium

3. **Wire Airbnb Checkout Redirect**
   - "Book Now" button generates Airbnb URL with dates/guests
   - Backend endpoint exists (`generateAirbnbCheckoutLink`)
   - Add clear messaging: "Complete your booking on Airbnb"
   - Complexity: Low (backend done, frontend wiring needed)

4. **Date-based Search Functionality**
   - SearchBar dates -> filter available properties
   - Use `checkMultiplePropertiesAvailability` from availability service
   - Show only properties available for selected dates
   - Complexity: Medium

5. **Error States and Loading**
   - Loading spinners during API calls
   - Error messages if Bookerville unavailable
   - Empty states if no properties match
   - Complexity: Low

### Should Have (Polish)

6. **Guest Reviews Display**
   - Fetch from Bookerville API
   - Display in PropertyDetails (UI exists)
   - Complexity: Low

7. **Availability Calendar Visualization**
   - Visual calendar showing blocked vs available dates
   - Helps users pick dates without trial/error
   - Complexity: Medium

8. **Resort Filtering**
   - Enable resort dropdown in SearchBar
   - Filter properties by resort selection
   - Complexity: Low (UI exists, needs logic)

### Defer (Post-Launch)

- Multi-property advanced search
- Amenity filtering (pool, game room, near Disney)
- WhatsApp integration
- Multi-language support
- SEO optimization
- Analytics integration

---

## Existing Code Assessment

### What's Already Built (Use As-Is)

| Component | Location | Status |
|-----------|----------|--------|
| SearchBar with date picker | `src/features/home/components/HeroSection/SearchBar.jsx` | Working, needs API connection |
| Property cards | `src/features/home/components/PropertiesSection/PropertyCard.jsx` | Working, needs real data |
| Property details page | `src/pages/PropertyDetails.jsx` | Working, needs real data |
| Image gallery with lightbox | `src/pages/PropertyDetails.jsx` | Working |
| Contact form | `src/features/home/components/ContactSection/ContactForm.jsx` | Needs backend wiring |
| Resort cards | `src/features/home/components/ResortsSection/ResortCard.jsx` | Working, needs real data |
| Bookerville availability service | `src/features/availability/services/bookervilleAvailability.js` | Complete, not connected |

### What's Commented Out (Enable)

| Feature | Location | Action |
|---------|----------|--------|
| Resort dropdown | SearchBar.jsx lines 26-47 | Uncomment and wire |
| Options dropdown (beds/baths/pool) | SearchBar.jsx lines 67-137 | Uncomment if time allows |

### What's Missing (Build)

| Feature | Effort | Notes |
|---------|--------|-------|
| API data fetching in pages | Medium | useEffect + fetch to backend |
| Availability calendar component | Medium | react-calendar or custom |
| Airbnb redirect button handler | Low | Call backend endpoint |
| Loading/error states | Low | Standard patterns |

---

## Competitive Reference: mastervacationhomes.com

Features observed on reference site:

| Feature | Have It? | Priority |
|---------|----------|----------|
| Multi-page layout (Home, Properties, Resorts, Services) | YES | - |
| Property search with date filter | PARTIAL (UI only) | CRITICAL |
| Guest/bedroom/bathroom filters | PARTIAL (commented) | MEDIUM |
| Property cards with image, beds, baths, guests | YES (mock data) | CRITICAL |
| Resort groupings | YES | LOW |
| Property detail page with gallery | YES | - |
| Availability calendar | NO | HIGH |
| Book Now -> external redirect | PARTIAL | CRITICAL |
| WhatsApp contact | NO | LOW |
| Multi-language | NO | DEFER |
| Cookie policy | Likely NO | LOW |

---

## Sources

- [8 Features Your Vacation Rental Website Needs to Succeed](https://blog.usewebready.com/features-vacation-rental-website-needs/) - MEDIUM confidence
- [23 Experts On The Biggest Vacation Rental Website Design Mistakes](https://www.rentalscaleup.com/vacation-rental-website-design-mistakes/) - MEDIUM confidence
- [The UX of Vacation Rental Websites - MeasuringU](https://measuringu.com/vacation-rental-benchmarks-2020/) - MEDIUM confidence
- [Best Vacation Rental Website Builders 2026](https://www.websiteplanet.com/blog/best-vacation-rental-website-builders/) - MEDIUM confidence
- mastervacationhomes.com (reference site) - HIGH confidence via WebFetch
- Project codebase analysis - HIGH confidence

---

## Summary for Roadmap

**Essential (Ship Today):**
1. Connect Bookerville API to frontend (unlocks everything)
2. Date-based availability search
3. Airbnb checkout redirect
4. Loading/error states

**Achievable with Extra Time:**
5. Visual availability calendar
6. Guest reviews display
7. Enable commented-out filters

**Explicitly Skip:**
- Direct booking/payments
- User accounts
- Chat widgets
- Dynamic pricing
- Content management

**The 80% to 100% gap is primarily "wire the existing backend to the existing frontend."** The architecture is sound; the features are stubbed. The work is integration, not creation.
