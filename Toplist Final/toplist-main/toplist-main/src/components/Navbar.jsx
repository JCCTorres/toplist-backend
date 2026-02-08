import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();

  // Handle scroll effect
  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 50) {
        setScrolled(true);
      } else {
        setScrolled(false);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);

  // Check if the link is active
  const isActive = (path) => {
    return location.pathname === path;
  };

  // Handle smooth scrolling for anchor links on homepage
  const scrollToSection = (sectionId) => {
    setIsOpen(false); // Close mobile menu if open
    
    // Only perform smooth scroll if on homepage
    if (location.pathname === '/') {
      const element = document.getElementById(sectionId);
      if (element) {
        window.scrollTo({
          top: element.offsetTop - 80, // Adjust for navbar height
          behavior: 'smooth'
        });
      }
    }
  };

  // Handle Properties link click
  const handlePropertiesClick = (e) => {
    e.preventDefault();
    setIsOpen(false); // Close mobile menu if open
    
    if (location.pathname === '/') {
      // If on homepage, scroll to properties section
      scrollToSection('properties');
    } else {
      // If on other page, navigate to /homes
      navigate('/homes');
    }
  };

  // Handle logo click to navigate to homepage
  const handleLogoClick = (e) => {
    e.preventDefault();
    navigate('/');
  };

  return (
    <nav className={`fixed w-full z-50 transition-all duration-300 ${scrolled ? 'bg-dark-900 shadow-lg' : 'bg-transparent'}`}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-20">
          <div className="flex items-center">
            <div 
              onClick={handleLogoClick} 
              className="flex-shrink-0 flex items-center cursor-pointer"
              role="button"
              aria-label="Go to homepage"
            >
              <img className="h-12 w-auto hover:opacity-80 transition-opacity" src="/TopListLogo3.png" alt="Toplist Orlando" />
            </div>
          </div>
          
          {/* Desktop menu */}
          <div className="hidden md:flex items-center space-x-8">
            <Link
              to="/"
              onClick={() => scrollToSection('home')}
              className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium transition-colors duration-200 ${isActive('/')
                ? 'text-blue-400 border-b-2 border-blue-400'
                : 'text-gray-300 hover:text-white'}`}
            >
              Home
            </Link>
            <button
              onClick={handlePropertiesClick}
              className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium transition-colors duration-200 ${(isActive('/homes') || (location.pathname === '/' && false))
                ? 'text-blue-400 border-b-2 border-blue-400'
                : 'text-gray-300 hover:text-white'}`}
            >
              Properties
            </button>
            {location.pathname === '/' ? (
              <>
                <button
                  onClick={() => scrollToSection('services')}
                  className="inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200"
                >
                  Add-ons
                </button>
                <button
                  onClick={() => scrollToSection('management')}
                  className="inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200"
                >
                  Management
                </button>
                <button
                  onClick={() => scrollToSection('contact')}
                  className="inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200"
                >
                  Contact
                </button>
              </>
            ) : (
              <>
                <Link
                  to="/services"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium transition-colors duration-200 ${isActive('/services')
                    ? 'text-blue-400 border-b-2 border-blue-400'
                    : 'text-gray-300 hover:text-white'}`}
                >
                  Add-ons
                </Link>
                <Link
                  to="/management"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium transition-colors duration-200 ${isActive('/management')
                    ? 'text-blue-400 border-b-2 border-blue-400'
                    : 'text-gray-300 hover:text-white'}`}
                >
                  Management
                </Link>
                <Link
                  to="/contact"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium transition-colors duration-200 ${isActive('/contact')
                    ? 'text-blue-400 border-b-2 border-blue-400'
                    : 'text-gray-300 hover:text-white'}`}
                >
                  Contact
                </Link>
              </>
            )}
            <div className="flex space-x-2">
              <Link 
                to="/guest" 
                className={`inline-flex items-center px-3 py-2 mt-1 border border-transparent text-sm font-medium rounded-md 
                  ${isActive('/guest') ? 'bg-blue-700 text-white' : 'text-white bg-blue-600 hover:bg-blue-700'}`}
              >
                Guest
              </Link>
              <Link 
                to="/admin-login" 
                className="inline-flex items-center px-3 py-2 mt-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
              >
                Admin
              </Link>
            </div>
          </div>
          
          {/* Mobile menu button */}
          <div className="flex items-center md:hidden">
            <button
              onClick={() => setIsOpen(!isOpen)}
              className="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-blue-400 focus:outline-none transition-colors duration-200"
            >
              <span className="sr-only">Open main menu</span>
              {isOpen ? (
                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              ) : (
                <svg className="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile menu overlay */}
      <div
        className={`fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity duration-300 ${isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onClick={() => setIsOpen(false)}
      />

      {/* Mobile slide-out drawer */}
      <div
        className={`fixed top-0 left-0 h-full w-72 bg-dark-900 z-50 md:hidden transform transition-transform duration-300 ease-in-out ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}
      >
        <div className="p-6">
          {/* Close button */}
          <div className="flex justify-between items-center mb-8">
            <img src="/TopListLogo3.png" alt="Toplist Orlando" className="h-10" />
            <button
              onClick={() => setIsOpen(false)}
              className="text-gray-400 hover:text-white transition-colors"
            >
              <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          {/* Navigation links */}
          <nav className="space-y-2">
            <Link
              to="/"
              className={`block px-4 py-3 rounded-lg text-base font-medium transition-colors ${isActive('/') ? 'bg-dark-700 text-blue-400' : 'text-gray-300 hover:bg-dark-800 hover:text-white'}`}
              onClick={() => scrollToSection('home')}
            >
              Home
            </Link>
            <button
              onClick={handlePropertiesClick}
              className={`block w-full text-left px-4 py-3 rounded-lg text-base font-medium transition-colors ${(isActive('/homes') || (location.pathname === '/' && false)) ? 'bg-dark-700 text-blue-400' : 'text-gray-300 hover:bg-dark-800 hover:text-white'}`}
            >
              Properties
            </button>
            {location.pathname === '/' ? (
              <>
                <button
                  className="block w-full text-left px-4 py-3 rounded-lg text-base font-medium text-gray-300 hover:bg-dark-800 hover:text-white transition-colors"
                  onClick={() => scrollToSection('services')}
                >
                  Add-ons
                </button>
                <button
                  className="block w-full text-left px-4 py-3 rounded-lg text-base font-medium text-gray-300 hover:bg-dark-800 hover:text-white transition-colors"
                  onClick={() => scrollToSection('management')}
                >
                  Management
                </button>
                <button
                  className="block w-full text-left px-4 py-3 rounded-lg text-base font-medium text-gray-300 hover:bg-dark-800 hover:text-white transition-colors"
                  onClick={() => scrollToSection('contact')}
                >
                  Contact
                </button>
              </>
            ) : (
              <>
                <Link
                  to="/services"
                  className={`block px-4 py-3 rounded-lg text-base font-medium transition-colors ${isActive('/services') ? 'bg-dark-700 text-blue-400' : 'text-gray-300 hover:bg-dark-800 hover:text-white'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Add-ons
                </Link>
                <Link
                  to="/management"
                  className={`block px-4 py-3 rounded-lg text-base font-medium transition-colors ${isActive('/management') ? 'bg-dark-700 text-blue-400' : 'text-gray-300 hover:bg-dark-800 hover:text-white'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Management
                </Link>
                <Link
                  to="/contact"
                  className={`block px-4 py-3 rounded-lg text-base font-medium transition-colors ${isActive('/contact') ? 'bg-dark-700 text-blue-400' : 'text-gray-300 hover:bg-dark-800 hover:text-white'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Contact
                </Link>
              </>
            )}

            <div className="pt-4 mt-4 border-t border-dark-600 space-y-2">
              <Link
                to="/guest"
                className="block px-4 py-3 bg-blue-600 text-white rounded-lg text-base font-medium hover:bg-blue-700 transition-colors text-center"
                onClick={() => setIsOpen(false)}
              >
                Guest Portal
              </Link>
              <Link
                to="/admin-login"
                className="block px-4 py-3 bg-red-600 text-white rounded-lg text-base font-medium hover:bg-red-700 transition-colors text-center"
                onClick={() => setIsOpen(false)}
              >
                Admin
              </Link>
            </div>
          </nav>
        </div>
      </div>
    </nav>
  );
}

export default Navbar; 