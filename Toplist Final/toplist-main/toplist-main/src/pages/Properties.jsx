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

  // Always fetch the full properties list (has validated images from local DB)
  const allPropsResponse = useApi(() => api.getProperties(), []);

  // Fetch search results only when search params are present
  const searchResponse = useApi(
    () => hasSearchParams
      ? api.searchProperties({ startDate: checkin, endDate: checkout, numAdults: adults, numChildren: children })
      : Promise.resolve(null),
    [checkin, checkout, adults, children]
  );

  const loading = hasSearchParams ? (searchResponse.loading || allPropsResponse.loading) : allPropsResponse.loading;
  const error = hasSearchParams ? (searchResponse.error || allPropsResponse.error) : allPropsResponse.error;
  const refetch = hasSearchParams ? searchResponse.refetch : allPropsResponse.refetch;

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

  // Build a lookup map from the full properties list (id -> property with image)
  const allPropsData = allPropsResponse.data?.data?.data || allPropsResponse.data?.data || {};
  const allPropsList = [...(allPropsData.properties || []), ...(allPropsData.resorts || [])];
  const imageMap = {};
  allPropsList.forEach(p => {
    if (p.id) {
      imageMap[p.id] = p;
    }
  });

  // Handle double-nested API responses: {success, data: {success, data: {results}}}
  const searchData = searchResponse.data;
  const searchResults = searchData?.data?.data?.results || searchData?.data?.results || [];

  // Normalize search results and enrich with images from the full properties list
  const isPlaceholderImage = (url) =>
    !url || url.includes('NoPrimaryPhoto') || url.includes('noprimaryphoto');

  const normalizedSearchResults = searchResults.map(r => {
    const rawUrl = typeof r.main_image === 'object' ? Object.values(r.main_image)[0] : r.main_image;
    const searchImageUrl = isPlaceholderImage(rawUrl) ? null : rawUrl;

    // Look up this property in the full list to get its validated image
    const localProp = imageMap[r.property_id];
    const localImage = localProp?.image || localProp?.main_image || null;

    return {
      id: r.property_id,
      title: localProp?.title || r.property_name,
      name: r.property_name,
      image: searchImageUrl || localImage,
      main_image: searchImageUrl || localImage,
      photos: localProp?.photos || [],
      city: r.city || localProp?.city,
      max_guests: r.max_guests || localProp?.max_guests,
      bedrooms: r.b_b?.bedrooms || localProp?.bedrooms,
      bathrooms: r.b_b?.bathrooms || localProp?.bathrooms,
      airbnb_id: r.airbnb_id || localProp?.airbnb_id,
      guests: localProp?.guests,
    };
  });

  const properties = hasSearchParams
    ? normalizedSearchResults
    : allPropsList;

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
            <PropertyCard
              key={property.id}
              property={property}
              searchParams={hasSearchParams ? searchParams.toString() : ''}
            />
          ))}
        </div>

        {properties.length === 0 && !loading && !error && (
          <div className="text-center py-12">
            <div className="bg-white/5 border border-white/10 rounded-2xl p-8 max-w-lg mx-auto shadow-sm">
              <svg className="w-16 h-16 text-white/20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 className="font-heading text-xl font-semibold text-white mb-2">No Properties Available</h3>
              <p className="text-sand-200/60 mb-4">
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
