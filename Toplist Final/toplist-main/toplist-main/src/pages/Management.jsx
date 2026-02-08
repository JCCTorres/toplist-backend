import React from 'react';

function Management() {
  const managementServices = [
    {
      title: "OCCUPANCY",
      description: "Strive for top-tier performance by maintaining occupancy rates above 80%",
      icon: (
        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V9h14v10zM5 7V5h14v2H5zm2 4h10v2H7v-2zm0 4h7v2H7v-2z" />
      )
    },
    {
      title: "PERFORMANCE",
      description: "Maximize your property's earning potential with our data-driven performance optimization strategies.",
      icon: (
        <path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99l1.5 1.5z" />
      )
    },
    {
      title: "HOME CARE",
      description: "Professional property maintenance and enhanced cleaning protocols to keep your property in pristine condition.",
      icon: (
        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
      )
    }
  ];

  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="bg-navy-950 pt-24 pb-12">
        <div className="container mx-auto px-4">
          <h1 className="font-heading text-4xl md:text-5xl font-bold text-white text-center">Property Management</h1>
          <div className="gold-line mx-auto mt-4"></div>
          <p className="text-sand-200/70 text-center mt-4 max-w-2xl mx-auto">Learn about our property management services in Orlando.</p>
        </div>
      </div>

      {/* Services Cards */}
      <div className="bg-sand-50 py-16">
        <div className="container mx-auto px-4">
          <h2 className="font-heading text-3xl font-bold text-center text-navy-950 mb-4">Property Management Services</h2>
          <p className="text-navy-800/60 text-center max-w-3xl mx-auto mb-12">
            Our comprehensive property management services help maximize your rental property's potential while minimizing your involvement.
          </p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {managementServices.map((service, index) => (
              <div key={index} className="bg-white border border-sand-200/60 rounded-2xl overflow-hidden shadow-sm card-hover transition-all duration-300">
                <div className="p-8 flex flex-col items-center justify-center">
                  <div className="bg-gold-500/10 w-16 h-16 rounded-full flex items-center justify-center mb-4">
                    <svg className="w-8 h-8 text-gold-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                      {service.icon}
                    </svg>
                  </div>
                  <h3 className="font-heading text-navy-950 text-lg font-bold uppercase tracking-wider">{service.title}</h3>
                </div>
                <div className="bg-navy-950 p-6">
                  <p className="text-sand-200/80 text-sm leading-relaxed">{service.description}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Details */}
      <div className="bg-white py-16">
        <div className="container mx-auto px-4 max-w-4xl">
          <h2 className="font-heading text-2xl font-bold mb-6 text-navy-950">Our Management Services</h2>
          <p className="mb-6 text-navy-800/60">We offer comprehensive property management services for vacation homes and rental properties in the Orlando area.</p>
          <ul className="space-y-3">
            {['24/7 Guest Support', 'Property Maintenance', 'Marketing and Booking Management', 'Cleaning and Housekeeping', 'Revenue Optimization'].map((item, i) => (
              <li key={i} className="flex items-center text-navy-800/70">
                <svg className="w-5 h-5 text-gold-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                {item}
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}

export default Management;
