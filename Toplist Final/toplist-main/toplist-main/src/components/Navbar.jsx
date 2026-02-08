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
    <nav className={`fixed w-full z-50 transition-all duration-300 ${scrolled ? 'bg-white shadow-lg' : 'bg-transparent'}`}>
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
              className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${isActive('/') 
                ? 'text-blue-600 border-b-2 border-blue-600' 
                : `${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}`}
            >
              Home
            </Link>
            <button 
              onClick={handlePropertiesClick}
              className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${(isActive('/homes') || (location.pathname === '/' && false))
                ? 'text-blue-600 border-b-2 border-blue-600' 
                : `${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}`}
            >
              Properties
            </button>
            {location.pathname === '/' ? (
              <>
                <button
                  onClick={() => scrollToSection('services')}
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}
                >
                  Add-ons
                </button>
                <button
                  onClick={() => scrollToSection('management')}
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}
                >
                  Management
                </button>
                <button
                  onClick={() => scrollToSection('contact')}
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}
                >
                  Contact
                </button>
              </>
            ) : (
              <>
                <Link
                  to="/services"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${isActive('/services')
                    ? 'text-blue-600 border-b-2 border-blue-600'
                    : `${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}`}
                >
                  Add-ons
                </Link>
                <Link
                  to="/management"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${isActive('/management')
                    ? 'text-blue-600 border-b-2 border-blue-600'
                    : `${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}`}
                >
                  Management
                </Link>
                <Link
                  to="/contact"
                  className={`inline-flex items-center px-1 pt-3 pb-2 text-sm font-medium ${isActive('/contact')
                    ? 'text-blue-600 border-b-2 border-blue-600'
                    : `${scrolled ? 'text-gray-500' : 'text-white'} hover:text-blue-600`}`}
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
              className={`inline-flex items-center justify-center p-2 rounded-md ${scrolled ? 'text-gray-700' : 'text-white'} hover:text-blue-600 focus:outline-none`}
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

      {/* Mobile menu, show/hide based on menu state */}
      {isOpen && (
        <div className="md:hidden bg-white shadow-lg">
          <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <Link
              to="/"
              className={`block px-3 py-2 rounded-md text-base font-medium ${isActive('/') ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
              onClick={() => scrollToSection('home')}
            >
              Home
            </Link>
            <button
              onClick={handlePropertiesClick}
              className={`block px-3 py-2 rounded-md text-base font-medium w-full text-left ${(isActive('/homes') || (location.pathname === '/' && false)) ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
            >
              Properties
            </button>
            {location.pathname === '/' ? (
              <>
                <button
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 w-full text-left"
                  onClick={() => scrollToSection('services')}
                >
                  Add-ons
                </button>
                <button
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 w-full text-left"
                  onClick={() => scrollToSection('management')}
                >
                  Management
                </button>
                <button
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100 w-full text-left"
                  onClick={() => scrollToSection('contact')}
                >
                  Contact
                </button>
              </>
            ) : (
              <>
                <Link
                  to="/services"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${isActive('/services') ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Add-ons
                </Link>
                <Link
                  to="/management"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${isActive('/management') ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Management
                </Link>
                <Link
                  to="/contact"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${isActive('/contact') ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100'}`}
                  onClick={() => setIsOpen(false)}
                >
                  Contact
                </Link>
              </>
            )}
            <Link
              to="/guest"
              className={`block px-3 py-2 rounded-md text-base font-medium ${isActive('/guest') ? 'bg-blue-100 text-blue-600' : 'bg-blue-600 text-white hover:bg-blue-700'}`}
              onClick={() => setIsOpen(false)}
            >
              Guest
            </Link>
            <Link
              to="/admin-login"
              className="block px-3 py-2 bg-red-600 text-white rounded-md text-base font-medium hover:bg-red-700"
              onClick={() => setIsOpen(false)}
            >
              Admin
            </Link>
          </div>
        </div>
      )}
    </nav>
  );
}

export default Navbar; 