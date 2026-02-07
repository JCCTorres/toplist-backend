import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import PropertyCard from './PropertyCard';
import { useApi } from '../../../../hooks/useApi';
import { api } from '../../../../services/api';
import LoadingSpinner from '../../../../components/LoadingSpinner';
import ErrorMessage from '../../../../components/ErrorMessage';

const PropertiesSection = () => {
  const [currentIndex, setCurrentIndex] = useState(0);
  const { data, loading, error, refetch } = useApi(() => api.getHomeCards());

  const properties = data?.data?.properties || [];

  const nextProperties = () => {
    if (properties.length === 0) return;
    setCurrentIndex((prevIndex) =>
      prevIndex + 2 >= properties.length ? 0 : prevIndex + 2
    );
  };

  const prevProperties = () => {
    if (properties.length === 0) return;
    setCurrentIndex((prevIndex) =>
      prevIndex - 2 < 0 ? Math.max(0, properties.length - 2) : prevIndex - 2
    );
  };

  const visibleProperties = properties.slice(currentIndex, currentIndex + 2);

  if (loading) {
    return (
      <section id="properties" className="bg-gray-100 py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-semibold mb-10 text-center">Featured Properties</h2>
          <LoadingSpinner />
        </div>
      </section>
    );
  }

  if (error) {
    return (
      <section id="properties" className="bg-gray-100 py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-semibold mb-10 text-center">Featured Properties</h2>
          <ErrorMessage onRetry={refetch} />
        </div>
      </section>
    );
  }

  return (
    <section id="properties" className="bg-gray-100 py-16">
      <div className="container mx-auto px-4">
        <h2 className="text-3xl font-semibold mb-10 text-center">Featured Properties</h2>

        {/* Carousel Container */}
        <div className="relative">
          {/* Left Arrow */}
          <button
            onClick={prevProperties}
            className="absolute -left-12 top-1/2 transform -translate-y-1/2 bg-gray-500 rounded-md p-3 shadow-lg z-20 hover:bg-gray-600 transition-colors"
          >
            <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          {/* Properties Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            {visibleProperties.map(property => (
              <div key={property.id} className="transform scale-90">
                <PropertyCard property={property} />
              </div>
            ))}
          </div>

          {/* Right Arrow */}
          <button
            onClick={nextProperties}
            className="absolute -right-12 top-1/2 transform -translate-y-1/2 bg-gray-500 rounded-md p-3 shadow-lg z-20 hover:bg-gray-600 transition-colors"
          >
            <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div className="text-center mt-8">
          <Link to="/homes" className="bg-white border border-blue-900 text-blue-900 py-2 px-6 rounded-lg inline-block hover:bg-blue-100 transition-colors">
            View All Properties
          </Link>
        </div>
      </div>
    </section>
  );
};

export default PropertiesSection;
