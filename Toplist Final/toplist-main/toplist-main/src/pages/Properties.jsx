import React from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import PropertyCard from '../features/home/components/PropertiesSection/PropertyCard';
import { useApi } from '../hooks/useApi';
import { api } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

function Properties() {
  const [searchParams] = useSearchParams();
  const checkin = searchParams.get('checkin');
  const checkout = searchParams.get('checkout');
  const adults = searchParams.get('adults');
  const children = searchParams.get('children');

  const hasSearchParams = checkin && checkout;

  const { data, loading, error, refetch } = useApi(
    () => hasSearchParams
      ? api.searchProperties({ startDate: checkin, endDate: checkout, numAdults: adults, numChildren: children })
      : api.getProperties(),
    [checkin, checkout, adults, children]
  );

  if (loading) {
    return (
      <div className="bg-dark-800 min-h-screen pt-20">
        <LoadingSpinner />
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-dark-800 min-h-screen pt-20">
        <ErrorMessage onRetry={refetch} />
      </div>
    );
  }

  const properties = hasSearchParams
    ? data?.data?.results || []
    : data?.data?.properties || [];

  return (
    <div className="bg-dark-800 min-h-screen pt-20">
      {/* Header Section */}
      <div className="bg-dark-900">
        <div className="container mx-auto px-4 py-8">
          <h1 className="font-heading text-4xl font-bold text-center text-white mb-4">
            {hasSearchParams ? 'Available Properties' : 'All Properties'}
          </h1>
          <p className="text-lg text-gray-400 text-center max-w-2xl mx-auto">
            {hasSearchParams
              ? `Showing properties available from ${checkin} to ${checkout} for ${adults} adult${adults !== '1' ? 's' : ''}${children && children !== '0' ? ` and ${children} child${children !== '1' ? 'ren' : ''}` : ''}.`
              : 'Discover our complete collection of premium vacation rental properties in Orlando\'s most sought-after resorts.'
            }
          </p>
          {hasSearchParams && (
            <div className="text-center mt-4">
              <Link to="/homes" className="text-blue-400 hover:text-blue-300 underline">
                Clear search and show all properties
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* Properties Grid */}
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-8 max-w-6xl mx-auto">
          {properties.map(property => (
            <div key={property.id} className="flex justify-center">
              <div className="w-full max-w-md">
                <PropertyCard property={property} />
              </div>
            </div>
          ))}
        </div>

        {/* Empty State */}
        {properties.length === 0 && !loading && !error && (
          <div className="text-center py-12">
            <div className="bg-dark-700 rounded-lg p-8 max-w-lg mx-auto">
              <svg className="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 className="font-heading text-xl font-semibold text-white mb-2">No Properties Available</h3>
              <p className="text-gray-400 mb-4">
                {hasSearchParams
                  ? 'No properties are available for your selected dates. Try different dates or contact us for assistance.'
                  : 'No properties found. Please check back later.'
                }
              </p>
              <Link to="/contact" className="text-blue-400 hover:text-blue-300 underline">
                Contact us for help
              </Link>
            </div>
          </div>
        )}
      </div>

      {/* Additional Info Section */}
      <div className="bg-dark-900">
        <div className="container mx-auto px-4 py-12">
          <div className="text-center">
            <h2 className="font-heading text-3xl font-bold text-white mb-8">
              Why Choose Our Properties?
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
              <div className="text-center">
                <div className="bg-blue-600/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h3 className="font-heading text-xl font-semibold text-white mb-2">Premium Quality</h3>
                <p className="text-gray-400">All properties are carefully selected and maintained to the highest standards.</p>
              </div>
              <div className="text-center">
                <div className="bg-blue-600/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <h3 className="font-heading text-xl font-semibold text-white mb-2">Prime Locations</h3>
                <p className="text-gray-400">Located in Orlando's most popular resort communities near theme parks.</p>
              </div>
              <div className="text-center">
                <div className="bg-blue-600/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 11-9.75 9.75A9.75 9.75 0 0112 2.25z" />
                  </svg>
                </div>
                <h3 className="font-heading text-xl font-semibold text-white mb-2">24/7 Support</h3>
                <p className="text-gray-400">Round-the-clock guest support to ensure your perfect vacation experience.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Properties;
