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
    const response = await fetch(`${BASE_URL}/all-properties`);
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
    const response = await fetch(`${BASE_URL}/properties/${id}/real-availability`);
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
};
