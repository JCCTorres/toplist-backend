import React from 'react';
import SearchBar from './SearchBar';

const HeroSection = ({ searchBarProps }) => {
  return (
    <section id="home" className="relative min-h-screen pt-28 hero-section flex flex-col">
      {/* Video Background */}
      <div className="video-background">
        <video autoPlay muted loop playsInline className="fullscreen-video">
          <source src="/images/carousel/background-video.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
        <div className="video-dark-overlay"></div>
      </div>

      {/* Hero Content */}
      <div className="container mx-auto px-4 py-16 relative z-10 flex-1 flex flex-col justify-end">
        <div className="text-center text-white mb-12">
          {/* Removed h1 and p tags */}
        </div>

        {/* Search Bar */}
        <div className="mb-5">
          <SearchBar {...searchBarProps} />
        </div>
      </div>
    </section>
  );
};

export default HeroSection; 