import React from 'react';
import HeroSection from '../features/home/components/HeroSection';
import { useSearchFilters } from '../features/home/hooks/useSearchFilters';

function Home() {
  const searchBarProps = useSearchFilters();

  return (
    <div className="font-sans">
      <HeroSection searchBarProps={searchBarProps} />
    </div>
  );
}

export default Home;
