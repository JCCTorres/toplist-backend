import React from 'react';

const ServiceCard = ({ service }) => {
  return (
    <div className="bg-dark-700 rounded-xl shadow-lg p-3 flex flex-col w-[200px] h-[260px] transform transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
      <div className="flex flex-col text-center mb-2">
        <h3 className="font-heading font-bold text-sm mb-1 text-white">{service.title}</h3>
        <p className="text-gray-400 text-xs leading-tight line-clamp-2">
          {service.description}
        </p>
        {service.fee && (
          <p className="text-xs text-gray-500 mt-1 italic">
            {service.fee}
          </p>
        )}
      </div>
      <div className="flex-1 w-full overflow-hidden rounded-md">
        <video
          src={service.video}
          className="w-full h-full object-cover"
          autoPlay
          loop
          muted
        />
      </div>
    </div>
  );
};

export default ServiceCard; 