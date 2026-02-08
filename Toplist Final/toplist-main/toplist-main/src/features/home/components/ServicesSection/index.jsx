import React from 'react';
import { Link } from 'react-router-dom';
import ServiceCard from './ServiceCard';
import { services } from '../../data/services';

const ServicesSection = () => {
  const featuredServices = services.slice(0, 4);

  return (
    <section className="relative py-20 overflow-hidden">
      <div
        className="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
        style={{
          backgroundImage: 'url(/images/carousel/piscina.jpg)'
        }}
      >
        <div className="absolute inset-0 bg-navy-900/80"></div>
      </div>

      <div className="container mx-auto px-4 relative z-10">
        <h2 className="font-heading text-3xl font-bold mb-4 text-center text-white">Our Add-ons</h2>
        <p className="text-gray-300 text-center mb-12 max-w-2xl mx-auto">Enhance your vacation experience with our premium add-on services.</p>
        <div className="flex flex-row gap-4 justify-center items-stretch flex-wrap">
          {featuredServices.map(service => (
            <ServiceCard key={service.id} service={service} />
          ))}
        </div>
        <div className="text-center mt-10">
          <Link to="/services" className="inline-block bg-white text-navy-900 py-3 px-8 rounded-lg font-medium hover:bg-gray-100 transition-all duration-300 shadow-md">
            View All Add-ons
          </Link>
        </div>
      </div>
    </section>
  );
};

export default ServicesSection;
