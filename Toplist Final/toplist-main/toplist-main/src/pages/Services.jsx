import React, { useState } from 'react';
import { Link } from 'react-router-dom';

function Services() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    serviceType: '',
    message: '',
  });

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

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Form submitted:', formData);
    setFormData({ name: '', email: '', phone: '', serviceType: '', message: '' });
  };

  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="bg-navy-950 pt-24 pb-12">
        <div className="container mx-auto px-4">
          <h1 className="font-heading text-4xl md:text-5xl font-bold text-white text-center">Our Add-ons</h1>
          <div className="gold-line mx-auto mt-4"></div>
          <p className="text-sand-200/70 text-center mt-4 max-w-2xl mx-auto">Enhance your stay with our premium add-on services.</p>
        </div>
      </div>

      {/* Services Cards */}
      <div className="bg-sand-50 py-16">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {services.map((service, index) => (
              <div key={index} className="bg-white border border-sand-200/60 rounded-2xl shadow-sm overflow-hidden card-hover transition-all duration-300">
                <div className="h-48 overflow-hidden">
                  {service.image.endsWith('.mp4') ? (
                    <video autoPlay muted loop playsInline className="w-full h-full object-cover">
                      <source src={service.image} type="video/mp4" />
                    </video>
                  ) : (
                    <img src={service.image} alt={service.title} className="w-full h-full object-cover" />
                  )}
                </div>
                <div className="p-5">
                  <h3 className="font-heading font-bold text-lg mb-2 text-navy-950">{service.title}</h3>
                  <p className="text-navy-800/60 text-sm">{service.description}</p>
                </div>
              </div>
            ))}

            <div
              className="bg-sand-100 border border-sand-200/60 rounded-2xl overflow-hidden flex items-center justify-center cursor-pointer card-hover transition-all duration-300"
              onClick={() => setShowMoreServices(true)}
            >
              <div className="text-center p-6">
                <div className="w-16 h-16 mx-auto bg-gold-500/10 border border-gold-500/30 rounded-full flex items-center justify-center mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-8 h-8 text-gold-500">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </div>
                <h3 className="font-heading font-bold text-lg mb-2 text-navy-950">See More</h3>
                <p className="text-navy-800/40 text-sm">View additional add-ons</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Additional Services Popup */}
      {showMoreServices && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
          <div className="bg-white border border-sand-200/60 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center p-6 border-b border-sand-200/40">
              <h2 className="font-heading text-2xl font-bold text-navy-950">Additional Add-ons</h2>
              <button onClick={() => setShowMoreServices(false)} className="text-navy-800/40 hover:text-navy-800/70 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {additionalServices.map((service, index) => (
                  <div key={index} className="bg-sand-50 border border-sand-200/60 rounded-2xl overflow-hidden">
                    <div className="h-48 overflow-hidden">
                      {service.image.endsWith('.mp4') ? (
                        <video autoPlay muted loop playsInline className="w-full h-full object-cover">
                          <source src={service.image} type="video/mp4" />
                        </video>
                      ) : (
                        <img src={service.image} alt={service.title} className="w-full h-full object-cover" />
                      )}
                    </div>
                    <div className="p-5">
                      <h3 className="font-heading font-bold text-lg mb-2 text-navy-950">{service.title}</h3>
                      <p className="text-navy-800/60 text-sm">{service.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div className="p-6 border-t border-sand-200/40 flex justify-end">
              <button onClick={() => setShowMoreServices(false)} className="px-6 py-2 bg-gold-500 text-navy-950 font-semibold rounded-xl hover:bg-gold-400 transition">Close</button>
            </div>
          </div>
        </div>
      )}

      {/* Service Request Form */}
      <div className="bg-sand-50 py-16">
        <div className="container mx-auto px-4 max-w-3xl">
          <h2 className="font-heading text-2xl font-bold text-center text-navy-950 mb-8">Request an Add-on</h2>
          <form onSubmit={handleSubmit} className="bg-white border border-sand-200/60 rounded-2xl p-8 shadow-sm">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label htmlFor="name" className="block text-navy-800/60 mb-2 text-sm font-medium">Name</label>
                <input type="text" id="name" name="name" value={formData.name} onChange={handleInputChange} className="w-full px-4 py-3 bg-white border border-sand-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-navy-900" required />
              </div>
              <div>
                <label htmlFor="email" className="block text-navy-800/60 mb-2 text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value={formData.email} onChange={handleInputChange} className="w-full px-4 py-3 bg-white border border-sand-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-navy-900" required />
              </div>
              <div>
                <label htmlFor="phone" className="block text-navy-800/60 mb-2 text-sm font-medium">Phone</label>
                <input type="tel" id="phone" name="phone" value={formData.phone} onChange={handleInputChange} className="w-full px-4 py-3 bg-white border border-sand-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-navy-900" />
              </div>
              <div>
                <label htmlFor="serviceType" className="block text-navy-800/60 mb-2 text-sm font-medium">Add-on Type</label>
                <select id="serviceType" name="serviceType" value={formData.serviceType} onChange={handleInputChange} className="w-full px-4 py-3 bg-white border border-sand-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-navy-900" required>
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
                <label htmlFor="message" className="block text-navy-800/60 mb-2 text-sm font-medium">Message</label>
                <textarea id="message" name="message" value={formData.message} onChange={handleInputChange} rows="4" className="w-full px-4 py-3 bg-white border border-sand-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-navy-900" required></textarea>
              </div>
            </div>
            <div className="mt-6 text-center">
              <button type="submit" className="bg-gold-500 text-navy-950 py-3 px-8 rounded-xl font-semibold hover:bg-gold-400 transition shadow-md">Submit Request</button>
            </div>
          </form>
        </div>
      </div>

      {/* Enhance Your Stay */}
      <div className="bg-navy-950 py-16">
        <div className="container mx-auto px-4 text-center max-w-3xl">
          <h2 className="font-heading text-2xl font-bold text-white mb-6">Enhance Your Stay</h2>
          <p className="text-sand-200/70 mb-8">
            At Toplist Vacations, we understand that the little details make a big difference in your vacation experience.
            Our array of additional add-ons is designed to make your stay as comfortable and enjoyable as possible.
          </p>
          <Link to="/contact" className="inline-block border border-gold-400 text-gold-400 py-3 px-8 rounded-xl font-semibold hover:bg-gold-400 hover:text-navy-950 transition-all duration-300">
            Contact Us for More Information
          </Link>
        </div>
      </div>
    </div>
  );
}

export default Services;
