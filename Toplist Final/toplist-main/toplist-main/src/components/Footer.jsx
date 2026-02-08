import React from 'react';
import { Link } from 'react-router-dom';

function Footer() {
  return (
    <footer className="bg-navy-950 border-t border-white/5 text-white">
      <div className="max-w-7xl mx-auto py-14 px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-10">
          {/* About Us Column */}
          <div>
            <img
              src="/TopListLogo3.png"
              alt="Toplist Orlando"
              className="h-10 mb-4 opacity-90"
            />
            <h2 className="font-heading text-xl text-white mb-3">Toplist Orlando</h2>
            <p className="text-white/50 text-sm leading-relaxed font-sans">
              Toplist Orlando provides premium vacation rental and property management services in Orlando, Florida. Your dream vacation starts here.
            </p>
          </div>

          {/* Quick Links Column */}
          <div>
            <h3 className="uppercase text-xs tracking-[0.15em] font-sans font-medium text-gold-400 mb-4">Quick Links</h3>
            <ul className="space-y-3">
              <li>
                <Link to="/homes" className="text-white/50 hover:text-white transition-colors text-sm font-sans">
                  Properties
                </Link>
              </li>
              <li>
                <Link to="/services" className="text-white/50 hover:text-white transition-colors text-sm font-sans">
                  Add-ons
                </Link>
              </li>
              <li>
                <Link to="/management" className="text-white/50 hover:text-white transition-colors text-sm font-sans">
                  Management
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-white/50 hover:text-white transition-colors text-sm font-sans">
                  Contact
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact Info Column */}
          <div>
            <h3 className="uppercase text-xs tracking-[0.15em] font-sans font-medium text-gold-400 mb-4">Contact Info</h3>
            <ul className="space-y-3 text-white/50 text-sm font-sans">
              <li className="flex items-start">
                <svg className="w-4 h-4 mr-2.5 mt-0.5 text-gold-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>info@toplistorlando.com</span>
              </li>
              <li className="flex items-start">
                <svg className="w-4 h-4 mr-2.5 mt-0.5 text-gold-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>(123) 456-7890</span>
              </li>
              <li className="flex items-start">
                <svg className="w-4 h-4 mr-2.5 mt-0.5 text-gold-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Orlando, FL</span>
              </li>
            </ul>
          </div>

          {/* Follow Us Column */}
          <div>
            <h3 className="uppercase text-xs tracking-[0.15em] font-sans font-medium text-gold-400 mb-4">Follow Us</h3>
            <p className="text-white/50 text-sm mb-5 font-sans leading-relaxed">Stay connected with us on social media for the latest updates and vacation inspiration.</p>
            <div className="flex space-x-3">
              <a
                href="https://www.instagram.com/toplistvacationrentals/"
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/40 hover:text-gold-400 p-2.5 rounded-full border border-white/10 hover:border-gold-400/30 transition-all duration-300"
              >
                <span className="sr-only">Instagram</span>
                <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                  <path fillRule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clipRule="evenodd" />
                </svg>
              </a>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-12 border-t border-white/10 pt-8">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-white/30 text-xs font-sans">
              &copy; {new Date().getFullYear()} Toplist Orlando. All rights reserved.
            </p>
            <div className="flex space-x-6 mt-4 md:mt-0">
              <a href="#" className="text-white/30 hover:text-white/50 text-xs font-sans transition-colors">
                Privacy Policy
              </a>
              <a href="#" className="text-white/30 hover:text-white/50 text-xs font-sans transition-colors">
                Terms of Service
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}

export default Footer;
