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

      {/* Hero Content — absolute center */}
      <div className="absolute inset-0 z-10 flex items-center justify-center px-4">
        <div className="w-full max-w-4xl opacity-0 animate-fade-up">
          <p className="text-gold-400 text-sm md:text-base font-semibold tracking-[0.25em] uppercase mb-5 text-center">
            Orlando's Premier Vacation Rentals
          </p>
          <SearchBar {...searchBarProps} />
        </div>
      </div>
    </section>
  );
};

export default HeroSection; 