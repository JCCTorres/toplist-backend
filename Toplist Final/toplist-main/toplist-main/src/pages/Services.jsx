import React, { useState } from 'react';
import { Link } from 'react-router-dom';

function Services() {
  // State for contact form
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    serviceType: '',
    message: '',
  });

  // State for popup
  const [showMoreServices, setShowMoreServices] = useState(false);

  const services = [
    {
      title: "BBQ",
      description: "Have a BBQ with your family and friends during your stay.",
      image: "/AddsOn/BBQ.mp4",
      category: "amenities"
    },
    {
      title: "Pool Heat",
      description: "Keep your private pool at a comfortable temperature throughout your stay.",
      image: "/AddsOn/Pool heat.mp4",
      category: "amenities"
    },
    {
      title: "Baby Items",
      description: "Travel lighter with our baby equipment rental: cribs, high chairs, strollers, and more.",
      image: "/AddsOn/Baby Items.mp4",
      category: "equipment"
    },
    {
      title: "Celebration",
      description: "Make your special occasion memorable with our celebration packages including decorations, cakes, and more.",
      image: "/AddsOn/Celebration.mp4",
      category: "celebration"
    }
  ];

  // Additional services shown in popup
  const additionalServices = [
    {
      title: "Mid-Clean",
      description: "Keep your vacation home spotless with our mid-stay cleaning service.",
      image: "/AddsOn/Mid-Clean 2.mp4",
      category: "additional"
    },
    {
      title: "Additional Linen Delivery",
      description: "Extra fresh linens delivered to your door whenever you need them.",
      image: "/AddsOn/Additional Linen Delivery 2.mp4",
      category: "additional"
    }
  ];

  // Handle form input changes
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  // Handle form submission
  const handleSubmit = (e) => {
    e.preventDefault();
    // Add your form submission logic here
    console.log('Form submitted:', formData);
    // Reset form after submission
    setFormData({
      name: '',
      email: '',
      phone: '',
      serviceType: '',
      message: '',
    });
  };

  return (
    <div className="pt-24 pb-16 bg-dark-800 min-h-screen">
      {/* Services Cards */}
      <div className="container mx-auto px-4">
        <h1 className="font-heading text-4xl font-bold mb-4 text-white">Our Add-ons</h1>
        <p className="text-lg mb-8 text-gray-400">Enhance your stay with our premium add-on services.</p>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {services.map((service, index) => (
            <div key={index} className="bg-dark-700 rounded-lg shadow-lg overflow-hidden relative h-auto transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
              <div className="h-48 overflow-hidden">
                {service.image.endsWith('.mp4') ? (
                  <video autoPlay muted loop playsInline className="w-full h-full object-cover">
                    <source src={service.image} type="video/mp4" />
                  </video>
                ) : (
                  <img src={service.image} alt={service.title} className="w-full h-full object-cover" />
                )}
              </div>
              <div className="p-4">
                <h3 className="font-heading font-bold text-lg mb-1 text-white">{service.title}</h3>
                <p className="text-gray-400 text-sm">{service.description}</p>
              </div>
            </div>
          ))}

          {/* See More Button */}
          <div
            className="bg-dark-700 rounded-lg shadow-lg overflow-hidden relative h-80 flex items-center justify-center cursor-pointer hover:bg-dark-600 transition-colors"
            onClick={() => setShowMoreServices(true)}
          >
            <div className="text-center p-4">
              <div className="w-16 h-16 mx-auto bg-blue-600 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-8 h-8 text-white">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <h3 className="font-heading font-bold text-lg mb-2 text-white">See More Add-ons</h3>
              <p className="text-gray-400 text-sm">Click to view additional add-ons we offer</p>
            </div>
          </div>
        </div>
      </div>

      {/* Additional Services Popup */}
      {showMoreServices && (
        <div className="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
          <div className="bg-dark-800 rounded-lg shadow-xl max-w-4xl w-full max-h-90vh overflow-y-auto border border-dark-600">
            <div className="flex justify-between items-center p-6 border-b border-dark-600">
              <h2 className="font-heading text-2xl font-bold text-white">Additional Add-ons</h2>
              <button
                onClick={() => setShowMoreServices(false)}
                className="text-gray-400 hover:text-white transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {additionalServices.map((service, index) => (
                  <div key={index} className="bg-dark-700 rounded-lg shadow-lg overflow-hidden relative h-auto">
                    <div className="h-48 overflow-hidden">
                      {service.image.endsWith('.mp4') ? (
                        <video autoPlay muted loop playsInline className="w-full h-full object-cover">
                          <source src={service.image} type="video/mp4" />
                        </video>
                      ) : (
                        <img src={service.image} alt={service.title} className="w-full h-full object-cover" />
                      )}
                    </div>
                    <div className="p-4">
                      <h3 className="font-heading font-bold text-lg mb-1 text-white">{service.title}</h3>
                      <p className="text-gray-400 text-sm">{service.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div className="p-6 border-t border-dark-600 flex justify-end">
              <button
                onClick={() => setShowMoreServices(false)}
                className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Service Request Form */}
      <div className="container mx-auto px-4 py-12 bg-dark-700 rounded-lg shadow-lg my-12">
        <h2 className="font-heading text-2xl font-bold text-center text-white mb-8">Request an Add-on</h2>
        <form onSubmit={handleSubmit} className="max-w-2xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="name" className="block text-gray-300 mb-2">Name</label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleInputChange}
                className="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
                required
              />
            </div>
            <div>
              <label htmlFor="email" className="block text-gray-300 mb-2">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleInputChange}
                className="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
                required
              />
            </div>
            <div>
              <label htmlFor="phone" className="block text-gray-300 mb-2">Phone</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                value={formData.phone}
                onChange={handleInputChange}
                className="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
              />
            </div>
            <div>
              <label htmlFor="serviceType" className="block text-gray-300 mb-2">Add-on Type</label>
              <select
                id="serviceType"
                name="serviceType"
                value={formData.serviceType}
                onChange={handleInputChange}
                className="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white"
                required
              >
                <option value="">Select an add-on</option>
                <option value="bbq">BBQ</option>
                <option value="pool">Pool Heating</option>
                <option value="baby">Baby Items</option>
                <option value="celebration">Celebration Package</option>
                <option value="midclean">Mid-Clean</option>
                <option value="linen">Additional Linen Delivery</option>
              </select>
            </div>
            <div className="md:col-span-2">
              <label htmlFor="message" className="block text-gray-300 mb-2">Message</label>
              <textarea
                id="message"
                name="message"
                value={formData.message}
                onChange={handleInputChange}
                rows="4"
                className="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"
                required
              ></textarea>
            </div>
          </div>
          <div className="mt-6 text-center">
            <button
              type="submit"
              className="bg-blue-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-700 transition"
            >
              Submit Request
            </button>
          </div>
        </form>
      </div>

      {/* Service Description Section */}
      <div className="container mx-auto px-4 py-12 bg-dark-700 rounded-lg shadow-lg my-12">
        <h2 className="font-heading text-2xl font-bold text-center text-white mb-8">Enhance Your Stay</h2>
        <p className="text-gray-400 text-center max-w-3xl mx-auto mb-8">
          At Toplist Vacations, we understand that the little details make a big difference in your vacation experience.
          Our array of additional add-ons is designed to make your stay as comfortable and enjoyable as possible.
          From heating your private pool to providing essential baby equipment, we've got everything covered.
        </p>
        <div className="text-center">
          <Link to="/contact" className="inline-block bg-blue-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-700 transition">
            Contact Us for More Information
          </Link>
        </div>
      </div>

      {/* Complimentary Extras Section */}
      <div className="container mx-auto px-4 py-16 bg-dark-900 rounded-lg">
        <h2 className="font-heading text-4xl font-bold text-center text-white mb-6">Complimentary Extras</h2>
        <p className="text-gray-400 text-center max-w-3xl mx-auto mb-8">
          Enjoy a host of free & convenient perks during every stay. (Note: Select add-ons
          - including patio grill use, stroller or crib rentals, and pool heat for your villa's
          private pool - incur a small fee)
        </p>

        <div className="relative flex justify-center my-12">
          {/* Carousel Navigation */}
          <button className="absolute left-0 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white h-10 w-10 rounded-full flex items-center justify-center z-10 hover:bg-blue-700 transition">
            <i className="fas fa-chevron-left"></i>
          </button>

          {/* Carousel Items */}
          <div className="flex justify-center gap-8 w-full max-w-5xl overflow-hidden">
            {/* Add carousel items here */}
          </div>

          <button className="absolute right-0 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white h-10 w-10 rounded-full flex items-center justify-center z-10 hover:bg-blue-700 transition">
            <i className="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  );
}

export default Services; 