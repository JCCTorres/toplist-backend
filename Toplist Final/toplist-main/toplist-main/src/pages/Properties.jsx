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
      <div className="min-h-screen pt-20">
        <LoadingSpinner />
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen pt-20">
        <ErrorMessage onRetry={refetch} />
      </div>
    );
  }

  const properties = hasSearchParams
    ? data?.data?.results || []
    : [...(data?.data?.properties || []), ...(data?.data?.resorts || [])];

  return (
    <div className="min-h-screen pt-20">
      {/* Header Section */}
      <div className="bg-navy-950">
        <div className="container mx-auto px-4 py-16">
          <h1 className="font-heading text-4xl md:text-5xl font-bold text-center text-white mb-4">
            {hasSearchParams ? (
              <span className="gold-line">Available Properties</span>
            ) : (
              <span className="gold-line">All Properties</span>
            )}
          </h1>
          <p className="text-lg text-sand-200/70 text-center max-w-2xl mx-auto">
            {hasSearchParams
              ? `Showing properties available from ${checkin} to ${checkout} for ${adults} adult${adults !== '1' ? 's' : ''}${children && children !== '0' ? ` and ${children} child${children !== '1' ? 'ren' : ''}` : ''}.`
              : 'Discover our complete collection of premium vacation rental properties in Orlando\'s most sought-after resorts.'
            }
          </p>
          {hasSearchParams && (
            <div className="text-center mt-6">
              <Link to="/homes" className="text-gold-400 hover:text-gold-500 underline underline-offset-4 transition-colors">
                Clear search and show all properties
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* Properties Grid */}
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {properties.map(property => (
            <PropertyCard key={property.id} property={property} />
          ))}
        </div>

        {properties.length === 0 && !loading && !error && (
          <div className="text-center py-12">
            <div className="bg-white/5 border border-white/10 rounded-2xl p-8 max-w-lg mx-auto shadow-sm">
              <svg className="w-16 h-16 text-navy-800/20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 className="font-heading text-xl font-semibold text-navy-900 mb-2">No Properties Available</h3>
              <p className="text-navy-800/50 mb-4">
                {hasSearchParams
                  ? 'No properties are available for your selected dates. Try different dates or contact us for assistance.'
                  : 'No properties found. Please check back later.'
                }
              </p>
              <Link to="/contact" className="text-gold-400 hover:text-gold-500 font-medium transition-colors">
                Contact us for help
              </Link>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default Properties;
