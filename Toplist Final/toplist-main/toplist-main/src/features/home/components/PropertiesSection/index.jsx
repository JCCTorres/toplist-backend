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
      <section className="bg-gray-50 py-20">
        <div className="container mx-auto px-4">
          <h2 className="font-heading text-3xl font-bold mb-10 text-center text-navy-900">Featured Properties</h2>
          <LoadingSpinner />
        </div>
      </section>
    );
  }

  if (error) {
    return (
      <section className="bg-gray-50 py-20">
        <div className="container mx-auto px-4">
          <h2 className="font-heading text-3xl font-bold mb-10 text-center text-navy-900">Featured Properties</h2>
          <ErrorMessage onRetry={refetch} />
        </div>
      </section>
    );
  }

  return (
    <section className="bg-gray-50 py-20">
      <div className="container mx-auto px-4">
        <h2 className="font-heading text-3xl font-bold mb-4 text-center text-navy-900">Featured Properties</h2>
        <p className="text-gray-500 text-center mb-12 max-w-2xl mx-auto">Discover our handpicked selection of premium vacation homes in Orlando's finest resort communities.</p>

        <div className="relative">
          <button
            onClick={prevProperties}
            className="absolute -left-4 md:-left-12 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 rounded-full p-3 shadow-lg z-20 transition-all duration-200 border border-gray-200"
          >
            <svg className="w-5 h-5 text-navy-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto px-8 md:px-0">
            {visibleProperties.map(property => (
              <div key={property.id}>
                <PropertyCard property={property} />
              </div>
            ))}
          </div>

          <button
            onClick={nextProperties}
            className="absolute -right-4 md:-right-12 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 rounded-full p-3 shadow-lg z-20 transition-all duration-200 border border-gray-200"
          >
            <svg className="w-5 h-5 text-navy-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div className="text-center mt-12">
          <Link to="/homes" className="inline-block bg-blue-600 text-white py-3 px-8 rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-md">
            View All Properties
          </Link>
        </div>
      </div>
    </section>
  );
};

export default PropertiesSection;
