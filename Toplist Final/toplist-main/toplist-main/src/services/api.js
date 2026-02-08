/**
 * Centralized API client for Bookerville endpoints
 * Uses native fetch, throws Error on non-ok response
 */

const BASE_URL = '/api/bookerville';

/**
 * Helper to handle fetch responses
 * @param {Response} response - Fetch response
 * @returns {Promise<Object>} - Parsed JSON response
 * @throws {Error} - If response is not ok
 */
const handleResponse = async (response) => {
  if (!response.ok) {
    const errorText = await response.text().catch(() => 'Unknown error');
    throw new Error(`API Error: ${response.status} - ${errorText}`);
  }
  return response.json();
};

/**
 * API client with methods for Bookerville endpoints
 */
export const api = {
  /**
   * Fetch all properties for the properties listing page
   * @returns {Promise<{success: boolean, data: {properties: Array, count: number}}>}
   */
  getProperties: async () => {
    const response = await fetch(`${BASE_URL}/home-cards?limit=100`);
    return handleResponse(response);
  },

  /**
   * Fetch properties for home page carousel/cards
   * @returns {Promise<{success: boolean, data: {properties: Array, resorts: Array}}>}
   */
  getHomeCards: async () => {
    const response = await fetch(`${BASE_URL}/home-cards`);
    return handleResponse(response);
  },

  /**
   * Fetch details for a specific property
   * @param {string} id - Property ID
   * @returns {Promise<{success: boolean, data: Object}>}
   */
  getPropertyDetails: async (id) => {
    const response = await fetch(`${BASE_URL}/properties/${id}/details`);
    return handleResponse(response);
  },

  /**
   * Fetch availability for a specific property
   * @param {string} id - Property ID
   * @returns {Promise<{success: boolean, data: Object}>}
   */
  getPropertyAvailability: async (id) => {
    // Request 6 months of availability data so the calendar can show booked dates
    const today = new Date();
    const startDate = today.toISOString().split('T')[0];
    const endDate = new Date(today.getFullYear(), today.getMonth() + 6, today.getDate()).toISOString().split('T')[0];
    const response = await fetch(`${BASE_URL}/properties/${id}/real-availability?startDate=${startDate}&endDate=${endDate}`);
    return handleResponse(response);
  },

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
   * Fetch guest reviews for a specific property
   * @param {string} id - Property ID
   * @returns {Promise<{success: boolean, data: Object}>}
   */
  getGuestReviews: async (id) => {
    const response = await fetch(`${BASE_URL}/properties/${id}/reviews`);
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
  },
};
