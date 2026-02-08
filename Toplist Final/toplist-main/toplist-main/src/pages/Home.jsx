import React from 'react';
import HeroSection from '../features/home/components/HeroSection';
import PropertiesSection from '../features/home/components/PropertiesSection';
import { useSearchFilters } from '../features/home/hooks/useSearchFilters';

function Home() {
  const searchBarProps = useSearchFilters();

  return (
    <div className="font-sans">
      <HeroSection searchBarProps={searchBarProps} />
      <PropertiesSection />
    </div>
  );
}

export default Home;
