import React from 'react';
import SearchBar from './SearchBar';

const HeroSection = ({ searchBarProps }) => {
  return (
    <section id="home" className="relative min-h-screen hero-section flex flex-col">
      {/* Video Background */}
      <div className="video-background">
        <video autoPlay muted loop playsInline className="fullscreen-video">
          <source src="/images/carousel/background-video.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
        <div className="video-dark-overlay"></div>
      </div>

      {/* Hero Content */}
      <div className="container mx-auto px-4 relative z-10 flex-1 flex flex-col justify-center items-center">
        {/* Hero Text Overlay */}
        <div className="text-center mb-16 max-w-4xl">
          <p className="text-gold-400 text-sm md:text-base font-semibold tracking-[0.25em] uppercase mb-6 opacity-0 animate-fade-up">
            Orlando's Premier Vacation Rentals
          </p>
          <h1 className="font-heading text-5xl md:text-6xl lg:text-7xl text-white leading-tight mb-6 opacity-0 animate-fade-up-delay-1">
            Your Dream Vacation{' '}
            <span className="gold-line">Starts Here</span>
          </h1>
          <p className="text-white/70 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed opacity-0 animate-fade-up-delay-2">
            Handpicked luxury homes and resorts near Orlando's world-famous theme parks
          </p>
        </div>

        {/* Search Bar */}
        <div className="w-full max-w-5xl opacity-0 animate-fade-up-delay-3 mb-16">
          <SearchBar {...searchBarProps} />
        </div>
      </div>
    </section>
  );
};

export default HeroSection; 