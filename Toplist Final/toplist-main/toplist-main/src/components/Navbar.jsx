import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    setIsOpen(false);
  }, [location.pathname]);

  const isActive = (path) => location.pathname === path;

  const handleLogoClick = (e) => {
    e.preventDefault();
    navigate('/');
  };

  const navLinks = [
    { to: '/', label: 'Home' },
    { to: '/homes', label: 'Properties' },
    { to: '/services', label: 'Add-ons' },
    { to: '/management', label: 'Management' },
    { to: '/contact', label: 'Contact' },
  ];

  return (
    <nav
      className={`fixed w-full z-50 transition-all duration-500 ${
        scrolled
          ? 'bg-navy-950/90 backdrop-blur-md shadow-lg shadow-black/20'
          : 'bg-transparent'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-20">
          {/* Logo */}
          <div className="flex items-center">
            <div
              onClick={handleLogoClick}
              className="flex-shrink-0 flex items-center gap-3 cursor-pointer group"
              role="button"
              aria-label="Go to homepage"
            >
              <img
                className="h-11 w-auto transition-opacity duration-300 group-hover:opacity-80"
                src="/TopListLogo3.png"
                alt="Toplist Orlando"
              />
              <span className="hidden sm:block font-heading text-xl text-white tracking-wide transition-colors duration-300 group-hover:text-gold-400">
                Toplist Orlando
              </span>
            </div>
          </div>

          {/* Desktop menu */}
          <div className="hidden md:flex items-center gap-1">
            {navLinks.map(({ to, label }) => (
              <Link
                key={to}
                to={to}
                className={`relative inline-flex items-center px-4 py-2 text-xs font-medium uppercase tracking-[0.15em] transition-all duration-300 ${
                  isActive(to)
                    ? 'text-white'
                    : 'text-sand-200/70 hover:text-white'
                }`}
              >
                {label}
                {/* Active indicator */}
                <span
                  className={`absolute bottom-0 left-4 right-4 h-[2px] bg-gold-400 transition-all duration-300 ${
                    isActive(to)
                      ? 'opacity-100 scale-x-100'
                      : 'opacity-0 scale-x-0'
                  }`}
                />
              </Link>
            ))}

            {/* Divider */}
            <div className="w-px h-6 bg-white/10 mx-3" />

            {/* Action buttons */}
            <div className="flex items-center gap-2">
              <Link
                to="/guest"
                className="inline-flex items-center px-4 py-2 border border-white/20 text-xs font-medium uppercase tracking-[0.15em] rounded text-sand-200/80 hover:text-white hover:border-white/40 hover:bg-white/5 transition-all duration-300"
              >
                Guest
              </Link>
              <Link
                to="/admin-login"
                className="inline-flex items-center px-4 py-2 text-xs font-medium uppercase tracking-[0.15em] rounded bg-gold-500 text-navy-950 hover:bg-gold-400 transition-all duration-300"
              >
                Admin
              </Link>
            </div>
          </div>

          {/* Mobile menu button */}
          <div className="flex items-center md:hidden">
            <button
              onClick={() => setIsOpen(!isOpen)}
              className="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-white/10 focus:outline-none transition-colors duration-200"
              aria-label="Open main menu"
            >
              {isOpen ? (
                <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M6 18L18 6M6 6l12 12" />
                </svg>
              ) : (
                <svg className="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile menu overlay */}
      <div
        className={`fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden transition-opacity duration-300 ${
          isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'
        }`}
        onClick={() => setIsOpen(false)}
      />

      {/* Mobile slide-down panel */}
      <div
        className={`absolute top-full left-0 right-0 bg-navy-950 border-t border-white/5 z-50 md:hidden transition-all duration-300 ease-in-out overflow-hidden ${
          isOpen ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0'
        }`}
      >
        <div className="px-6 py-6">
          <nav className="space-y-1">
            {navLinks.map(({ to, label }) => (
              <Link
                key={to}
                to={to}
                className={`block px-4 py-3 rounded-lg text-sm font-medium uppercase tracking-[0.12em] transition-all duration-300 ${
                  isActive(to)
                    ? 'bg-white/5 text-gold-400 border-l-2 border-gold-400'
                    : 'text-sand-200/70 hover:bg-white/5 hover:text-white'
                }`}
                onClick={() => setIsOpen(false)}
              >
                {label}
              </Link>
            ))}

            <div className="pt-4 mt-4 border-t border-white/10 space-y-2">
              <Link
                to="/guest"
                className="block px-4 py-3 border border-white/20 text-sand-200/80 rounded-lg text-sm font-medium uppercase tracking-[0.12em] hover:bg-white/5 hover:text-white transition-all duration-300 text-center"
                onClick={() => setIsOpen(false)}
              >
                Guest Portal
              </Link>
              <Link
                to="/admin-login"
                className="block px-4 py-3 bg-gold-500 text-navy-950 rounded-lg text-sm font-medium uppercase tracking-[0.12em] hover:bg-gold-400 transition-all duration-300 text-center"
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
