import React from 'react';
import { Link } from 'react-router-dom';
import ServiceCard from './ServiceCard';
import { services } from '../../data/services';

const ServicesSection = () => {
  // Mostrar apenas os primeiros 4 serviços na home
  const featuredServices = services.slice(0, 4);

  return (
    <section id="services" className="relative bg-dark-900 py-16 overflow-hidden">
      {/* Background Image */}
      <div
        className="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
        style={{
          backgroundImage: 'url(/images/carousel/piscina.jpg)'
        }}
      >
        {/* Dark overlay for better text readability */}
        <div className="absolute inset-0 bg-black bg-opacity-60"></div>
      </div>

      <div className="container mx-auto px-4 relative z-10">
        <h2 className="font-heading text-3xl font-bold mb-10 text-center text-white drop-shadow-lg">Our Add-ons</h2>
        <div className="flex flex-row gap-4 justify-center items-stretch flex-wrap">
          {featuredServices.map(service => (
            <ServiceCard key={service.id} service={service} />
          ))}
        </div>
        <div className="text-center mt-8">
          <Link to="/services" className="inline-block border-2 border-white text-white py-3 px-8 rounded-lg font-medium hover:bg-white hover:text-dark-900 transition-all duration-300">
            View All Add-ons
          </Link>
        </div>
      </div>
    </section>
  );
};

export default ServicesSection; 