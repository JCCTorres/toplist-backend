import React, { useState, useEffect, useRef } from 'react';
import { useParams, Link, useSearchParams } from 'react-router-dom';
import Flatpickr from 'react-flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { useApi } from '../hooks/useApi';
import { api } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';
import { getAmenityIcon, getAmenityLabel } from '../utils/amenityIcons';

function PropertyDetails() {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const checkin = searchParams.get('checkin');
  const checkout = searchParams.get('checkout');
  const adultsParam = searchParams.get('adults');
  const childrenParam = searchParams.get('children');

  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalImageIndex, setModalImageIndex] = useState(0);
  const [originalOverflow, setOriginalOverflow] = useState('');
  const [selectedDates, setSelectedDates] = useState(() => {
    if (checkin && checkout) {
      return [new Date(checkin + 'T00:00:00'), new Date(checkout + 'T00:00:00')];
    }
    return [];
  });
  const [guestCount, setGuestCount] = useState(adultsParam ? parseInt(adultsParam) : 2);
  const [childrenCount, setChildrenCount] = useState(childrenParam ? parseInt(childrenParam) : 0);
  const [showFullDescription, setShowFullDescription] = useState(false);
  const thumbnailStripRef = useRef(null);

  const { data, loading, error, refetch } = useApi(() => api.getPropertyDetails(id), [id]);
  const rawProperty = data?.data?.data || data?.data || null;

  const property = rawProperty ? {
    ...rawProperty,
    title: rawProperty.title || rawProperty.name,
    bedrooms: rawProperty.details?.bedrooms || rawProperty.bedrooms,
    bathrooms: rawProperty.details?.bathrooms || rawProperty.bathrooms,
    max_guests: rawProperty.details?.max_occupancy || rawProperty.max_guests,
    category: rawProperty.details?.property_type || rawProperty.category,
    city: rawProperty.address?.city || rawProperty.city,
    state: rawProperty.address?.state || rawProperty.state,
    amenities: Array.isArray(rawProperty.amenities) ? rawProperty.amenities : (rawProperty.amenities?.list || []),
  } : null;

  const availability = useApi(() => api.getPropertyAvailability(id), [id]);
  const reviewsData = useApi(() => api.getGuestReviews(id), [id]);

  const getAvailabilityConfig = (availData) => {
    if (!availData?.data) return { disable: [], maxDate: undefined };

    const rawAvail = availData.data;

    // Compute maxDate from availableDates if present
    let maxDate = undefined;
    if (rawAvail.availableDates && Array.isArray(rawAvail.availableDates)) {
      rawAvail.availableDates.forEach(d => {
        const dateStr = d.startDate || d.date;
        if (!maxDate || dateStr > maxDate) maxDate = dateStr;
      });
    }

    // Use bookedStays array for disabling date ranges (most reliable)
    if (Array.isArray(rawAvail.bookedStays) && rawAvail.bookedStays.length > 0) {
      return {
        disable: rawAvail.bookedStays.map(stay => ({
          from: stay.arrivalDate || stay.startDate,
          to: stay.departureDate || stay.endDate
        })).filter(range => range.from && range.to),
        maxDate,
      };
    }

    // Fallback: use availableDates set to disable dates not in the set
    if (maxDate) {
      const availableSet = new Set();
      rawAvail.availableDates.forEach(d => {
        availableSet.add(d.startDate || d.date);
      });
      return {
        disable: [function(date) {
          const dateStr = date.toISOString().split('T')[0];
          return !availableSet.has(dateStr);
        }],
        maxDate,
      };
    }

    return { disable: [], maxDate: undefined };
  };

  const getNightsCount = () => {
    if (selectedDates.length === 2) {
      const diffTime = Math.abs(selectedDates[1] - selectedDates[0]);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      return diffDays;
    }
    return 0;
  };

  const formatDate = (date) => date.toISOString().split('T')[0];

  const handleBookNow = () => {
    if (!property.airbnb_id) {
      alert('This property cannot be booked online. Please contact us to make a reservation.');
      return;
    }

    if (selectedDates.length !== 2) {
      alert('Please select check-in and check-out dates before booking.');
      return;
    }

    const airbnbId = property.airbnb_id;
    const params = new URLSearchParams({
      checkin: formatDate(selectedDates[0]),
      numberOfGuests: String(guestCount + childrenCount),
      numberOfAdults: String(guestCount),
      checkout: formatDate(selectedDates[1]),
      guestCurrency: 'USD',
      productId: String(airbnbId),
      isWorkTrip: 'false',
      numberOfChildren: String(childrenCount),
      numberOfInfants: '0',
      numberOfPets: '0',
    });

    const checkoutUrl = `https://www.airbnb.com/book/stays/${airbnbId}?${params.toString()}`;
    window.open(checkoutUrl, '_blank');
  };

  useEffect(() => {
    if (availability.error) {
      console.warn('Availability fetch failed:', availability.error);
    }
  }, [availability.error]);

  const PLACEHOLDER_IMAGE = '/images/properties/property-placeholder.jpg';
  const propertyImages = property?.photos?.length > 0
    ? property.photos
    : (property?.main_image ? [property.main_image] : [PLACEHOLDER_IMAGE]);

  useEffect(() => {
    const handleEscKey = (event) => {
      if (event.key === 'Escape' && isModalOpen) {
        closeModal();
      }
    };

    if (isModalOpen) {
      document.addEventListener('keydown', handleEscKey);
    }

    return () => {
      document.removeEventListener('keydown', handleEscKey);
    };
  }, [isModalOpen]);

  // Scroll thumbnail strip to keep active thumbnail visible
  useEffect(() => {
    if (thumbnailStripRef.current) {
      const strip = thumbnailStripRef.current;
      const activeThumb = strip.children[currentImageIndex];
      if (activeThumb) {
        const stripRect = strip.getBoundingClientRect();
        const thumbRect = activeThumb.getBoundingClientRect();
        const scrollLeft = activeThumb.offsetLeft - strip.offsetWidth / 2 + activeThumb.offsetWidth / 2;
        strip.scrollTo({ left: scrollLeft, behavior: 'smooth' });
      }
    }
  }, [currentImageIndex]);

  const nextImage = () => {
    setCurrentImageIndex((prevIndex) =>
      prevIndex === propertyImages.length - 1 ? 0 : prevIndex + 1
    );
  };

  const prevImage = () => {
    setCurrentImageIndex((prevIndex) =>
      prevIndex === 0 ? propertyImages.length - 1 : prevIndex - 1
    );
  };

  const goToImage = (index) => {
    setCurrentImageIndex(index);
  };

  const openModal = (index) => {
    setOriginalOverflow(document.body.style.overflow || '');
    setModalImageIndex(index);
    setIsModalOpen(true);
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    setIsModalOpen(false);
    document.body.style.overflow = originalOverflow;
  };

  const nextModalImage = () => {
    setModalImageIndex((prevIndex) =>
      prevIndex === propertyImages.length - 1 ? 0 : prevIndex + 1
    );
  };

  const prevModalImage = () => {
    setModalImageIndex((prevIndex) =>
      prevIndex === 0 ? propertyImages.length - 1 : prevIndex - 1
    );
  };

  const goToModalImage = (index) => {
    setModalImageIndex(index);
  };

  if (loading) {
    return (
      <div className="pt-20 min-h-screen">
        <LoadingSpinner />
      </div>
    );
  }

  if (error || !property) {
    return (
      <div className="pt-20 min-h-screen">
        <ErrorMessage
          message={error || "Property not found"}
          onRetry={refetch}
        />
        <div className="flex justify-center mt-4">
          <Link
            to="/homes"
            className="text-gold-400 hover:text-gold-500 underline"
          >
            Back to Properties
          </Link>
        </div>
      </div>
    );
  }

  const getDisplayPrice = () => {
    if (Array.isArray(property.rates) && property.rates.length > 0) {
      const activeRate = property.rates.find(r => r.nightly_rate > 0);
      if (activeRate) return `$${activeRate.nightly_rate}`;
      const weekendRate = property.rates.find(r => r.weekend_rate > 0);
      if (weekendRate) return `$${weekendRate.weekend_rate}`;
    }
    if (property.rates?.nightly) {
      return `$${property.rates.nightly}`;
    }
    if (property.rates?.base) {
      return `$${property.rates.base}`;
    }
    return '$--';
  };

  const getLocationString = () => {
    const parts = [];
    if (property.city) parts.push(property.city);
    if (property.state) parts.push(property.state);
    return parts.join(', ') || 'Orlando, FL';
  };

  const DESCRIPTION_CHAR_LIMIT = 300;
  const descriptionText = property.description || '';
  const isDescriptionLong = descriptionText.length > DESCRIPTION_CHAR_LIMIT;
  const displayDescription = showFullDescription
    ? descriptionText
    : descriptionText.slice(0, DESCRIPTION_CHAR_LIMIT);

  // Categorize amenities for organized display
  const amenityCategories = {
    'Property': ['Pool', 'HotTub', 'Patio', 'Balcony', 'Garage', 'Parking', 'FreeParking', 'BasketBall', 'GameRoom', 'Fireplace', 'PropertyClass'],
    'Kitchen': ['Kitchen', 'Refrigerator', 'Oven', 'Microwave', 'DishWasher', 'CoffeeMaker', 'Toaster', 'Blender', 'Grill'],
    'Comfort': ['AirConditioning', 'Heating', 'CeilingFans', 'WiFi', 'TV', 'CableSatellite', 'WasherDryer', 'Linens', 'Towels', 'HairDryer', 'IronAndBoard', 'Elevator', 'PetsAllowed'],
    'Safety': ['SmokeDetector', 'FireExtinguisher'],
  };

  const categorizeAmenities = (amenities) => {
    const result = {};
    const used = new Set();

    for (const [category, keys] of Object.entries(amenityCategories)) {
      const matched = amenities.filter(a => keys.includes(a));
      if (matched.length > 0) {
        result[category] = matched;
        matched.forEach(a => used.add(a));
      }
    }

    const uncategorized = amenities.filter(a => !used.has(a));
    if (uncategorized.length > 0) {
      result['Other'] = uncategorized;
    }

    return result;
  };

  const categorized = property.amenities?.length > 0 ? categorizeAmenities(property.amenities) : {};

  return (
    <div className="pt-20 min-h-screen">
      {/* Main Cover Image */}
      {propertyImages.length > 0 && (
        <div>
          <div className="relative w-full h-[450px] overflow-hidden bg-navy-900">
            <img
              src={propertyImages[currentImageIndex]}
              alt={`Property view ${currentImageIndex + 1}`}
              className="w-full h-full object-cover cursor-pointer transition-opacity duration-300"
              onClick={() => openModal(currentImageIndex)}
            />

            {propertyImages.length > 1 && (
              <>
                <button
                  onClick={prevImage}
                  className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-2.5 rounded-full transition-all duration-200 backdrop-blur-sm"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>

                <button
                  onClick={nextImage}
                  className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-2.5 rounded-full transition-all duration-200 backdrop-blur-sm"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}

            {/* Photo count badge */}
            <div className="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {currentImageIndex + 1} / {propertyImages.length}
            </div>
          </div>

          {/* Thumbnail Strip */}
          {propertyImages.length > 1 && (
            <div className="bg-navy-950/80 border-t border-white/5">
              <div
                ref={thumbnailStripRef}
                className="flex gap-1.5 overflow-x-auto py-2.5 px-4 max-w-7xl mx-auto scrollbar-hide"
                style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
              >
                {propertyImages.map((image, index) => (
                  <button
                    key={index}
                    onClick={() => goToImage(index)}
                    className={`relative flex-shrink-0 rounded-lg overflow-hidden transition-all duration-200 ${
                      index === currentImageIndex
                        ? 'ring-2 ring-gold-500 opacity-100'
                        : 'opacity-50 hover:opacity-80'
                    }`}
                  >
                    <img
                      src={image}
                      alt={`Thumbnail ${index + 1}`}
                      className="w-20 h-14 object-cover"
                    />
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      {/* Image Modal */}
      {isModalOpen && propertyImages.length > 0 && (
        <div
          className="fixed inset-0 bg-black/95 z-[9998] flex items-center justify-center"
          onClick={closeModal}
        >
          <button
            onClick={(e) => {
              e.stopPropagation();
              closeModal();
            }}
            className="absolute top-4 right-4 text-white hover:text-gray-300 z-[9999] bg-white/10 hover:bg-white/20 rounded-full p-3 transition-all duration-200 backdrop-blur-sm"
            title="Close (ESC)"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div
            className="relative w-full h-full flex items-center justify-center"
            onClick={(e) => e.stopPropagation()}
          >
            <img
              src={propertyImages[modalImageIndex]}
              alt={`Property view ${modalImageIndex + 1}`}
              className="max-w-[90%] max-h-[80%] object-contain"
            />

            {propertyImages.length > 1 && (
              <>
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    prevModalImage();
                  }}
                  className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/10 hover:bg-white/25 text-white p-3 rounded-full transition-all duration-200 backdrop-blur-sm"
                >
                  <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>

                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    nextModalImage();
                  }}
                  className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/10 hover:bg-white/25 text-white p-3 rounded-full transition-all duration-200 backdrop-blur-sm"
                >
                  <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
          </div>

          {/* Modal Thumbnail Strip */}
          {propertyImages.length > 1 && (
            <div
              className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-1.5 bg-black/50 backdrop-blur-md rounded-xl p-2.5 max-w-[90vw] overflow-x-auto"
              onClick={(e) => e.stopPropagation()}
              style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
            >
              {propertyImages.map((image, index) => (
                <button
                  key={index}
                  onClick={(e) => {
                    e.stopPropagation();
                    goToModalImage(index);
                  }}
                  className={`relative flex-shrink-0 overflow-hidden rounded-lg transition-all duration-200 ${
                    index === modalImageIndex
                      ? 'ring-2 ring-white opacity-100 scale-105'
                      : 'opacity-50 hover:opacity-90'
                  }`}
                >
                  <img
                    src={image}
                    alt={`Thumbnail ${index + 1}`}
                    className="w-16 h-11 object-cover"
                  />
                </button>
              ))}
            </div>
          )}

          <div className="absolute top-4 left-1/2 transform -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-medium">
            {modalImageIndex + 1} / {propertyImages.length}
          </div>
        </div>
      )}

      {/* Content Section */}
      <div className="max-w-7xl mx-auto px-6 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">

          {/* Left Column - Property Info */}
          <div className="lg:col-span-2">
            <div className="mb-8">
              <div className="flex items-center gap-3 mb-3">
                <span className="bg-gold-500/15 text-gold-400 px-3 py-1 rounded-lg text-sm font-semibold border border-gold-500/20">{getDisplayPrice()}/night</span>
                {property.category && (
                  <span className="bg-white/5 text-sand-200/60 px-3 py-1 rounded-lg text-sm border border-white/10">{property.category}</span>
                )}
              </div>
              <h1 className="font-heading text-3xl font-bold text-white mb-2">
                {property.title}
              </h1>
              <div className="flex items-center gap-4 text-sm text-sand-200/60 mb-4">
                {getLocationString() && (
                  <span className="flex items-center gap-1">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {getLocationString()}
                  </span>
                )}
                {property.bedrooms && (
                  <span>{property.bedrooms} Bed{property.bedrooms > 1 ? 's' : ''}</span>
                )}
                {property.bathrooms && (
                  <span>{property.bathrooms} Bath{property.bathrooms > 1 ? 's' : ''}</span>
                )}
                {property.max_guests && (
                  <span>{property.max_guests} Guests</span>
                )}
              </div>

              {/* Description with View More */}
              {descriptionText && (
                <div className="mb-2">
                  <p className="text-sand-200/70 text-sm leading-relaxed">
                    {displayDescription}
                    {isDescriptionLong && !showFullDescription && '...'}
                  </p>
                  {isDescriptionLong && (
                    <button
                      onClick={() => setShowFullDescription(!showFullDescription)}
                      className="mt-2 text-gold-400 hover:text-gold-300 text-sm font-medium transition-colors flex items-center gap-1"
                    >
                      {showFullDescription ? (
                        <>
                          View Less
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
                          </svg>
                        </>
                      ) : (
                        <>
                          View More
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                          </svg>
                        </>
                      )}
                    </button>
                  )}
                </div>
              )}
            </div>

            {/* Amenities Grid with Icons */}
            {Object.keys(categorized).length > 0 && (
              <div className="mb-8">
                <h2 className="font-heading text-2xl font-semibold mb-5 text-white">Amenities</h2>
                <div className="space-y-6">
                  {Object.entries(categorized).map(([category, amenities]) => (
                    <div key={category}>
                      <h3 className="text-xs font-medium uppercase tracking-wider text-sand-200/40 mb-3">{category}</h3>
                      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        {amenities.map((amenity, index) => (
                          <div
                            key={index}
                            className="flex items-center gap-3 p-3 bg-white/[0.03] border border-white/[0.06] rounded-xl hover:bg-white/[0.06] transition-colors"
                          >
                            <div className="w-9 h-9 rounded-lg bg-gold-500/10 flex items-center justify-center flex-shrink-0 text-gold-500">
                              <div className="w-5 h-5">
                                {getAmenityIcon(amenity)}
                              </div>
                            </div>
                            <span className="text-sm text-sand-200/80">{getAmenityLabel(amenity)}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Guest Reviews */}
            {(() => {
              const reviewsRaw = reviewsData.data?.data?.data || reviewsData.data?.data || null;
              const reviews = reviewsRaw?.reviews || [];
              const avgRating = reviewsRaw?.average_rating || 0;
              const totalReviews = reviewsRaw?.total_reviews || reviews.length;

              if (reviewsData.loading) {
                return (
                  <div className="py-4">
                    <h2 className="font-heading text-2xl font-semibold mb-4 text-white">Guest Reviews</h2>
                    <p className="text-sand-200/50 text-sm">Loading reviews...</p>
                  </div>
                );
              }

              if (reviews.length === 0) return null;

              return (
                <div>
                  <h2 className="font-heading text-2xl font-semibold mb-4 text-white">Guest Reviews</h2>
                  <div className="flex items-center mb-4">
                    <span className="text-2xl font-bold mr-2 text-white">{avgRating.toFixed(1)}</span>
                    <div className="flex text-yellow-400">
                      {[...Array(5)].map((_, i) => (
                        <svg key={i} className={`w-5 h-5 fill-current ${i < Math.round(avgRating) ? '' : 'opacity-25'}`} viewBox="0 0 24 24">
                          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                      ))}
                    </div>
                    <span className="text-sand-200/50 ml-2">{totalReviews} review{totalReviews !== 1 ? 's' : ''}</span>
                  </div>

                  <div className="space-y-4">
                    {reviews.slice(0, 6).map((review, index) => {
                      const guestName = review.guest_name === 'Anônimo' ? 'Guest' : review.guest_name;
                      const initials = guestName.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
                      const rating = review.rating || 5;
                      const reviewDate = review.review_date ? new Date(review.review_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short' }) : '';

                      return (
                        <div key={review.review_id || index} className="bg-white/5 p-4 rounded-lg border border-white/10">
                          <div className="flex items-start space-x-3">
                            <div className="w-10 h-10 bg-gold-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                              <span className="text-gold-400 text-sm font-medium">{initials}</span>
                            </div>
                            <div className="flex-1 min-w-0">
                              <div className="flex items-center justify-between mb-1">
                                <p className="font-medium text-white text-sm">{guestName}</p>
                                {reviewDate && <span className="text-sand-200/40 text-xs">{reviewDate}</span>}
                              </div>
                              <div className="flex text-yellow-400 mb-2">
                                {[...Array(5)].map((_, i) => (
                                  <svg key={i} className={`w-3.5 h-3.5 fill-current ${i < rating ? '' : 'opacity-25'}`} viewBox="0 0 24 24">
                                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                  </svg>
                                ))}
                              </div>
                              {review.title && (
                                <p className="text-white text-sm font-medium mb-1">{review.title}</p>
                              )}
                              {review.review_text && (
                                <p className="text-sand-200/60 text-sm leading-relaxed">{review.review_text}</p>
                              )}
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                  {reviews.length > 6 && (
                    <p className="text-sand-200/40 text-sm mt-3 text-center">Showing 6 of {totalReviews} reviews</p>
                  )}
                </div>
              );
            })()}
          </div>

          {/* Right Column - Booking Info */}
          <div className="lg:col-span-1">
            <div className="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 sticky top-24">
              <h3 className="font-heading text-xl font-semibold mb-4 text-white">Booking Information</h3>

              <div className="mb-4">
                <div className="flex justify-between items-center mb-2">
                  <span className="font-medium text-sand-200/60">Base Price:</span>
                  <span className="text-white">{getDisplayPrice()}/night</span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="font-medium text-sand-200/60">Total per night:</span>
                  <span className="font-bold text-white">{getDisplayPrice()}</span>
                </div>
              </div>

              <div className="space-y-4 mb-6">
                <div>
                  <label className="block text-sm font-medium mb-1 text-sand-200/60">Select Dates</label>
                  <Flatpickr
                    key={availability.loading ? 'loading' : 'loaded'}
                    options={{
                      mode: 'range',
                      minDate: 'today',
                      maxDate: getAvailabilityConfig(availability.data).maxDate,
                      dateFormat: 'Y-m-d',
                      showMonths: 2,
                      disable: getAvailabilityConfig(availability.data).disable,
                      defaultDate: selectedDates.length === 2 ? selectedDates : undefined,
                    }}
                    value={selectedDates}
                    onChange={(dates) => setSelectedDates(dates)}
                    placeholder="Select check-in - check-out"
                    className="w-full p-3 bg-white/10 border border-white/10 rounded-xl text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-gold-500"
                  />
                  {availability.loading && (
                    <p className="text-xs text-sand-200/40 mt-1">Loading availability...</p>
                  )}
                  {selectedDates.length === 2 && (
                    <p className="text-sm text-sand-200/60 mt-2">
                      {getNightsCount()} night{getNightsCount() !== 1 ? 's' : ''} selected
                    </p>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1 text-sand-200/60">Adults</label>
                  <select
                    className="w-full p-3 bg-navy-900 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-gold-500"
                    value={guestCount}
                    onChange={(e) => setGuestCount(parseInt(e.target.value))}
                  >
                    <option value="1">1 adult</option>
                    <option value="2">2 adults</option>
                    <option value="3">3 adults</option>
                    <option value="4">4 adults</option>
                    <option value="5">5 adults</option>
                    <option value="6">6 adults</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1 text-sand-200/60">Children</label>
                  <select
                    className="w-full p-3 bg-navy-900 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-gold-500"
                    value={childrenCount}
                    onChange={(e) => setChildrenCount(parseInt(e.target.value))}
                  >
                    <option value="0">0 children</option>
                    <option value="1">1 child</option>
                    <option value="2">2 children</option>
                    <option value="3">3 children</option>
                    <option value="4">4 children</option>
                  </select>
                </div>
              </div>

              {/* Airbnb Redirect Notice */}
              <div className="bg-white/5 border border-white/10 rounded-lg p-3 mb-4">
                <div className="flex items-start">
                  <svg className="w-5 h-5 text-gold-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                  </svg>
                  <p className="text-sm text-sand-200/70">
                    <strong className="text-white">Secure Booking:</strong> You will be redirected to Airbnb to complete your reservation securely.
                  </p>
                </div>
              </div>

              {property.airbnb_id ? (
                <button
                  onClick={handleBookNow}
                  className="w-full bg-gold-500 text-navy-950 py-3 rounded-lg font-semibold hover:bg-gold-400 transition-colors shadow-md"
                >
                  Book Now on Airbnb
                </button>
              ) : (
                <div className="text-center">
                  <p className="text-sand-200/60 mb-3">This property requires direct booking.</p>
                  <Link
                    to="/contact"
                    className="w-full block bg-gold-500/20 text-gold-400 border border-gold-500/30 py-3 rounded-lg font-semibold hover:bg-gold-500/30 transition-colors text-center"
                  >
                    Contact Us to Book
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default PropertyDetails;
