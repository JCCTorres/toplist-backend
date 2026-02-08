import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import Flatpickr from 'react-flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { useApi } from '../hooks/useApi';
import { api } from '../services/api';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

function PropertyDetails() {
  const { id } = useParams();
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalImageIndex, setModalImageIndex] = useState(0);
  const [originalOverflow, setOriginalOverflow] = useState('');
  const [selectedDates, setSelectedDates] = useState([]);
  const [guestCount, setGuestCount] = useState(2);
  const [childrenCount, setChildrenCount] = useState(0);
  const [bookingLoading, setBookingLoading] = useState(false);

  const { data, loading, error, refetch } = useApi(() => api.getPropertyDetails(id), [id]);
  // API returns { success, data: { success, data: { id, name, details, ... } } }
  const rawProperty = data?.data?.data || data?.data || null;

  // Flatten nested details into top-level for easier access
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

  const getDisabledDates = (availData) => {
    if (!availData?.data) return [];

    // Handle bookedStays format
    if (Array.isArray(availData.data.bookedStays)) {
      return availData.data.bookedStays.map(stay => ({
        from: stay.arrivalDate || stay.startDate,
        to: stay.departureDate || stay.endDate
      })).filter(range => range.from && range.to);
    }

    // Handle availableDates format: dates NOT in this list are unavailable
    if (availData.data.availableDates && Array.isArray(availData.data.availableDates)) {
      const availableSet = new Set();
      availData.data.availableDates.forEach(d => {
        availableSet.add(d.startDate || d.date);
      });

      // Flatpickr disable expects an array; a function must be wrapped in [fn]
      return [function(date) {
        const dateStr = date.toISOString().split('T')[0];
        return !availableSet.has(dateStr);
      }];
    }

    return [];
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

  const handleBookNow = async () => {
    if (!property.airbnb_id) {
      alert('This property cannot be booked online. Please contact us to make a reservation.');
      return;
    }

    if (selectedDates.length !== 2) {
      alert('Please select check-in and check-out dates before booking.');
      return;
    }

    setBookingLoading(true);
    try {
      const response = await api.getAirbnbCheckoutUrl(property.airbnb_id, {
        checkin: formatDate(selectedDates[0]),
        checkout: formatDate(selectedDates[1]),
        numberOfAdults: guestCount,
        numberOfChildren: childrenCount
      });

      if (response.success && response.data?.checkout_url) {
        window.open(response.data.checkout_url, '_blank');
      } else {
        alert('Unable to generate booking link. Please try again or contact us.');
      }
    } catch (err) {
      console.error('Booking error:', err);
      alert('An error occurred. Please try again or contact us to book.');
    } finally {
      setBookingLoading(false);
    }
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
      <div className="pt-20 bg-gray-50 min-h-screen">
        <LoadingSpinner />
      </div>
    );
  }

  if (error || !property) {
    return (
      <div className="pt-20 bg-gray-50 min-h-screen">
        <ErrorMessage
          message={error || "Property not found"}
          onRetry={refetch}
        />
        <div className="flex justify-center mt-4">
          <Link
            to="/homes"
            className="text-blue-600 hover:text-blue-700 underline"
          >
            Back to Properties
          </Link>
        </div>
      </div>
    );
  }

  const getDisplayPrice = () => {
    // rates is an array of rate periods with nightly_rate, weekend_rate, etc.
    if (Array.isArray(property.rates) && property.rates.length > 0) {
      // Find the first rate with a non-zero nightly_rate
      const activeRate = property.rates.find(r => r.nightly_rate > 0);
      if (activeRate) return `$${activeRate.nightly_rate}`;
      // Fallback to weekend rate
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

  return (
    <div className="pt-20 bg-gray-50 min-h-screen">
      {/* Image Carousel */}
      {propertyImages.length > 0 && (
        <div className="relative w-full h-[400px] overflow-hidden bg-gray-200">
          <img
            src={propertyImages[currentImageIndex]}
            alt={`Property view ${currentImageIndex + 1}`}
            className="w-full h-full object-cover cursor-pointer"
            onClick={() => openModal(currentImageIndex)}
          />

          {propertyImages.length > 1 && (
            <>
              <button
                onClick={prevImage}
                className="absolute left-6 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-800 p-2 rounded-full transition-all duration-200 shadow-lg"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <button
                onClick={nextImage}
                className="absolute right-6 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-800 p-2 rounded-full transition-all duration-200 shadow-lg"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </>
          )}

          {propertyImages.length > 1 && (
            <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
              {propertyImages.map((_, index) => (
                <button
                  key={index}
                  onClick={() => goToImage(index)}
                  className={`w-3 h-3 rounded-full transition-all duration-200 ${
                    index === currentImageIndex
                      ? 'bg-white'
                      : 'bg-white bg-opacity-50 hover:bg-opacity-75'
                  }`}
                />
              ))}
            </div>
          )}
        </div>
      )}

      {/* Image Modal */}
      {isModalOpen && propertyImages.length > 0 && (
        <div
          className="fixed inset-0 bg-black bg-opacity-90 z-[9998] flex items-center justify-center"
          onClick={closeModal}
        >
          <button
            onClick={(e) => {
              e.stopPropagation();
              closeModal();
            }}
            className="absolute top-4 right-4 text-white hover:text-gray-300 z-[9999] bg-black bg-opacity-70 hover:bg-opacity-90 rounded-full p-3 transition-all duration-200 border border-white border-opacity-30"
            title="Close (ESC)"
          >
            <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                  className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full transition-all duration-200"
                >
                  <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                  </svg>
                </button>

                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    nextModalImage();
                  }}
                  className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full transition-all duration-200"
                >
                  <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </>
            )}
          </div>

          {propertyImages.length > 1 && (
            <div
              className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 bg-white bg-opacity-10 rounded-lg p-3"
              onClick={(e) => e.stopPropagation()}
            >
              {propertyImages.map((image, index) => (
                <button
                  key={index}
                  onClick={(e) => {
                    e.stopPropagation();
                    goToModalImage(index);
                  }}
                  className={`relative overflow-hidden rounded transition-all duration-200 ${
                    index === modalImageIndex
                      ? 'ring-2 ring-white scale-110'
                      : 'hover:scale-105 opacity-70 hover:opacity-100'
                  }`}
                >
                  <img
                    src={image}
                    alt={`Thumbnail ${index + 1}`}
                    className="w-16 h-12 object-cover"
                  />
                </button>
              ))}
            </div>
          )}

          <div className="absolute top-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm font-medium">
            {modalImageIndex + 1} / {propertyImages.length}
          </div>
        </div>
      )}

      {/* Content Section */}
      <div className="max-w-7xl mx-auto px-6 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">

          {/* Left Column - Property Info */}
          <div className="lg:col-span-2">
            <div className="mb-6">
              <div className="flex items-center mb-2">
                <span className="bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium">{getDisplayPrice()}/night</span>
              </div>
              <p className="text-sm text-gray-500 mb-1">
                {property.bedrooms && `${property.bedrooms} Bedrooms`}
                {property.bathrooms && ` | ${property.bathrooms} Bathrooms`}
                {property.max_guests && ` | ${property.max_guests} Guests`}
                {property.category && ` | ${property.category}`}
              </p>
              <h1 className="font-heading text-2xl font-bold text-navy-900 mb-4">
                {property.title}
                {getLocationString() && ` - ${getLocationString()}`}
              </h1>
              {property.description && (
                <p className="text-gray-600 text-sm leading-relaxed">
                  {property.description}
                </p>
              )}
            </div>

            {/* Dynamic Amenities Grid */}
            {property.amenities && property.amenities.length > 0 && (
              <div className="mb-8">
                <h2 className="font-heading text-xl font-semibold mb-4 text-navy-900">Amenities</h2>
                <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                  {property.amenities.map((amenity, index) => (
                    <div key={index} className="flex flex-col items-center p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                      <div className="w-8 h-8 mb-2 flex items-center justify-center">
                        <svg className="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M9,16.17L4.83,12l-1.42,1.41L9,19 21,7l-1.41-1.41L9,16.17z"/>
                        </svg>
                      </div>
                      <span className="text-xs text-center text-gray-600">{amenity}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Guest Reviews */}
            <div>
              <h2 className="font-heading text-xl font-semibold mb-4 text-navy-900">Guest Reviews</h2>
              <div className="flex items-center mb-4">
                <span className="text-2xl font-bold mr-2 text-navy-900">5.0</span>
                <div className="flex text-yellow-400">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-5 h-5 fill-current" viewBox="0 0 24 24">
                      <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                    </svg>
                  ))}
                </div>
                <span className="text-gray-400 ml-2">31 reviews</span>
              </div>

              <div className="space-y-4">
                <div className="flex items-start space-x-3 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                  <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-white text-sm font-medium">A</span>
                  </div>
                  <div>
                    <p className="font-medium text-navy-900">Anonymous Guest</p>
                    <div className="flex text-yellow-400 text-sm">
                      {[...Array(5)].map((_, i) => (
                        <svg key={i} className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="flex items-start space-x-3 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                  <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span className="text-white text-sm font-medium">A</span>
                  </div>
                  <div>
                    <p className="font-medium text-navy-900">Anonymous Guest</p>
                    <div className="flex text-yellow-400 text-sm">
                      {[...Array(5)].map((_, i) => (
                        <svg key={i} className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                          <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                        </svg>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column - Booking Info */}
          <div className="lg:col-span-1">
            <div className="bg-white border border-gray-200 rounded-xl p-6 sticky top-24 shadow-md">
              <h3 className="font-heading text-lg font-semibold mb-4 text-navy-900">Booking Information</h3>

              <div className="mb-4">
                <div className="flex justify-between items-center mb-2">
                  <span className="font-medium text-gray-500">Base Price:</span>
                  <span className="text-navy-900">{getDisplayPrice()}/night</span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="font-medium text-gray-500">Total per night:</span>
                  <span className="font-bold text-navy-900">{getDisplayPrice()}</span>
                </div>
              </div>

              <div className="space-y-4 mb-6">
                <div>
                  <label className="block text-sm font-medium mb-1 text-gray-700">Select Dates</label>
                  <Flatpickr
                    options={{
                      mode: 'range',
                      minDate: 'today',
                      dateFormat: 'Y-m-d',
                      disable: getDisabledDates(availability.data)
                    }}
                    onChange={(dates) => setSelectedDates(dates)}
                    placeholder="Select check-in - check-out"
                    className="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  {availability.loading && (
                    <p className="text-xs text-gray-400 mt-1">Loading availability...</p>
                  )}
                  {selectedDates.length === 2 && (
                    <p className="text-sm text-gray-500 mt-2">
                      {getNightsCount()} night{getNightsCount() !== 1 ? 's' : ''} selected
                    </p>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1 text-gray-700">Adults</label>
                  <select
                    className="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                  <label className="block text-sm font-medium mb-1 text-gray-700">Children</label>
                  <select
                    className="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div className="flex items-start">
                  <svg className="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                  </svg>
                  <p className="text-sm text-blue-700">
                    <strong>Secure Booking:</strong> You will be redirected to Airbnb to complete your reservation securely.
                  </p>
                </div>
              </div>

              {property.airbnb_id ? (
                <button
                  onClick={handleBookNow}
                  disabled={bookingLoading}
                  className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {bookingLoading ? 'Preparing Booking...' : 'Book Now on Airbnb'}
                </button>
              ) : (
                <div className="text-center">
                  <p className="text-gray-500 mb-3">This property requires direct booking.</p>
                  <Link
                    to="/contact"
                    className="w-full block bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center shadow-md"
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
