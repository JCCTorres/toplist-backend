import { useState, useEffect, useCallback } from 'react';

/**
 * Custom hook for data fetching with loading/error states
 * Uses ignore flag pattern in useEffect cleanup to prevent race conditions
 *
 * @param {Function} fetchFn - Async function that fetches data
 * @param {Array} deps - Dependency array for re-fetching (default: [])
 * @returns {{data: any, loading: boolean, error: string|null, refetch: Function}}
 */
export const useApi = (fetchFn, deps = []) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchData = useCallback(async (ignore = false) => {
    setLoading(true);
    setError(null);

    try {
      const result = await fetchFn();
      if (!ignore) {
        setData(result);
        setLoading(false);
      }
    } catch (err) {
      if (!ignore) {
        setError(err.message || 'An unexpected error occurred');
        setLoading(false);
      }
    }
  }, [fetchFn]);

  useEffect(() => {
    let ignore = false;

    fetchData(ignore);

    return () => {
      ignore = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);

  const refetch = useCallback(() => {
    fetchData(false);
  }, [fetchData]);

  return { data, loading, error, refetch };
};
