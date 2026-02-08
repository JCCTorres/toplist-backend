import React from 'react';
import HeroSection from '../features/home/components/HeroSection';
import ServicesSection from '../features/home/components/ServicesSection';
import PropertiesSection from '../features/home/components/PropertiesSection';
import ContactSection from '../features/home/components/ContactSection';
import ManagementSection from '../features/home/components/ManagementSection';
import { useSearchFilters } from '../features/home/hooks/useSearchFilters';

function Home() {
  const searchBarProps = useSearchFilters();

  return (
    <div className="bg-dark-800 font-sans">
      <HeroSection searchBarProps={searchBarProps} />
      <PropertiesSection />
      <ServicesSection />
      <ContactSection />
      <ManagementSection />
    </div>
  );
}

export default Home; 