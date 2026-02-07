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
    <div className="bg-white rounded-lg overflow-hidden shadow-md flex flex-col h-full min-h-[450px]">
      <div className="relative h-64">
        {image && (
          <img src={image} alt={title} className="w-full h-full object-cover" />
        )}
      </div>
      <div className="p-6 flex flex-col flex-grow">
        <h3 className="text-lg font-semibold text-gray-800 mb-2">{title}</h3>

        {/* Property details - only show if values exist */}
        {(bedrooms || bathrooms || maxGuests) && (
          <div className="text-gray-700 mb-2">
            {bedrooms && <span>{bedrooms} Bedrooms</span>}
            {bedrooms && bathrooms && <span> / </span>}
            {bathrooms && <span>{bathrooms} Baths</span>}
            {(bedrooms || bathrooms) && maxGuests && <span> / </span>}
            {maxGuests && <span>{maxGuests} Guests</span>}
          </div>
        )}

        {/* Location - only show if city exists */}
        {city && (
          <div className="text-gray-600 mb-2">
            {city}
          </div>
        )}

        {/* Price - only show if price > 0 */}
        {price > 0 && (
          <div className="text-blue-900 font-semibold mb-4">
            Prices from ${price}/night
          </div>
        )}

        <div className="mt-auto flex justify-end">
          <Link
            to={`/property-details/${property.id}`}
            className="bg-blue-900 text-white py-1 px-3 rounded-lg text-sm hover:bg-blue-800 transition-colors"
          >
            View Details
          </Link>
        </div>
      </div>
    </div>
  );
};

export default PropertyCard;
