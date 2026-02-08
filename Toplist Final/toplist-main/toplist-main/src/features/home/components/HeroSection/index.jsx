import React from 'react';
import SearchBar from './SearchBar';

const HeroSection = ({ searchBarProps }) => {
  return (
    <section id="home" className="relative h-full hero-section">
      {/* Video Background */}
      <div className="video-background">
        <video autoPlay muted loop playsInline className="fullscreen-video">
          <source src="/images/carousel/background-video.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
        <div className="video-dark-overlay"></div>
      </div>

      {/* Hero Content — absolute center, shifted up to account for navbar */}
      <div className="absolute inset-0 z-10 flex items-center justify-center px-4 -mt-16">
        <div className="w-full max-w-4xl mx-auto opacity-0 animate-fade-up">
          <p className="text-gold-400 text-base md:text-lg font-semibold tracking-[0.25em] uppercase mb-5 text-center" style={{ textShadow: '0 1px 2px rgba(0,0,0,0.6), 0 0 4px rgba(0,0,0,0.3)' }}>
            Orlando's Premier Vacation Rentals
          </p>
          <SearchBar {...searchBarProps} />
        </div>
      </div>
    </section>
  );
};

export default HeroSection; 