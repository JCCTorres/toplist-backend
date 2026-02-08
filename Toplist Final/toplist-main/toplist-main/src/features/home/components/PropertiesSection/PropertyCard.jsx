import React from 'react';
import { Link } from 'react-router-dom';

/**
 * Property card component - handles both mock and API data shapes
 * API fields: id, title, main_image, bedrooms, bathrooms, max_guests, city, state, category, price
 */
const PropertyCard = ({ property }) => {
  // Handle both API (title) and mock (name) data shapes
  const title = property.title || property.name || 'Property';
  const image = property.main_image || property.image;
  const bedrooms = property.bedrooms;
  const bathrooms = property.bathrooms;
  const maxGuests = property.max_guests || property.guests;
  const city = property.city;
  const price = property.price;

  return (
    <div className="bg-dark-700 rounded-lg overflow-hidden shadow-lg flex flex-col h-full min-h-[450px] transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
      <div className="relative h-64 overflow-hidden">
        {image && (
          <img src={image} alt={title} className="w-full h-full object-cover transition-transform duration-300 hover:scale-105" />
        )}
      </div>
      <div className="p-6 flex flex-col flex-grow">
        <h3 className="font-heading text-lg font-semibold text-white mb-2">{title}</h3>

        {/* Property details - only show if values exist */}
        {(bedrooms || bathrooms || maxGuests) && (
          <div className="text-gray-400 mb-2 text-sm">
            {bedrooms && <span>{bedrooms} Bedrooms</span>}
            {bedrooms && bathrooms && <span> / </span>}
            {bathrooms && <span>{bathrooms} Baths</span>}
            {(bedrooms || bathrooms) && maxGuests && <span> / </span>}
            {maxGuests && <span>{maxGuests} Guests</span>}
          </div>
        )}

        {/* Location - only show if city exists */}
        {city && (
          <div className="text-gray-500 mb-2 text-sm">
            {city}
          </div>
        )}

        {/* Price - only show if price > 0 */}
        {price > 0 && (
          <div className="text-blue-400 font-semibold mb-4">
            Prices from ${price}/night
          </div>
        )}

        <div className="mt-auto flex justify-end">
          <Link
            to={`/property-details/${property.id}`}
            className="bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
          >
            View Details
          </Link>
        </div>
      </div>
    </div>
  );
};

export default PropertyCard;
