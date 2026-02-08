import React from 'react';
import { Link } from 'react-router-dom';

const PropertyCard = ({ property, searchParams }) => {
  const title = property.title || property.name || 'Property';
  const image = property.image || property.main_image;
  const city = property.city;

  // Parse guests string from API (e.g. "6 guests . 2 beds . 2 baths")
  const guestsStr = property.guests || '';
  const hasStructuredDetails = property.bedrooms || property.bathrooms || property.max_guests;

  return (
    <div className="card-hover bg-white/5 backdrop-blur-sm rounded-2xl overflow-hidden flex flex-col h-full border border-white/10 shadow-sm">
      <div className="relative h-64 overflow-hidden bg-navy-900">
        {image ? (
          <>
            <img src={image} alt={title} className="card-img w-full h-full object-cover" />
            {/* Gradient overlay for text readability */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none" />
          </>
        ) : (
          <div className="w-full h-full flex items-center justify-center text-navy-800/30">
            <svg className="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
          </div>
        )}
      </div>
      <div className="p-6 flex flex-col flex-grow">
        <h3 className="font-heading text-lg font-semibold text-white mb-2">{title}</h3>

        {hasStructuredDetails ? (
          <div className="flex items-center gap-4 text-sand-200/60 mb-3 text-sm">
            {property.bedrooms > 0 && (
              <span className="flex items-center gap-1.5">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                {property.bedrooms} Bed
              </span>
            )}
            {property.bathrooms > 0 && (
              <span className="flex items-center gap-1.5">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                {property.bathrooms} Bath
              </span>
            )}
            {property.max_guests > 0 && (
              <span className="flex items-center gap-1.5">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                {property.max_guests} Guests
              </span>
            )}
          </div>
        ) : guestsStr && (
          <div className="text-sand-200/60 mb-3 text-sm">
            {guestsStr}
          </div>
        )}

        {city && (
          <div className="text-xs uppercase tracking-wider text-sand-200/40 mb-4 flex items-center gap-1.5">
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            {city}
          </div>
        )}

        <div className="mt-auto">
          <Link
            to={`/property-details/${property.id}${searchParams ? `?${searchParams}` : ''}`}
            className="block w-full text-center bg-gold-500/20 text-gold-400 border border-gold-500/30 py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gold-500/30 transition-colors"
          >
            View Details
          </Link>
        </div>
      </div>
    </div>
  );
};

export default PropertyCard;
