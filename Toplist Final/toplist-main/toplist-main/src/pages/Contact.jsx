import React from 'react';

function Contact() {
  return (
    <div className="pt-24 pb-16 bg-dark-800 min-h-screen">
      <div className="container mx-auto px-4">
        <h1 className="font-heading text-4xl font-bold mb-4 text-white">Contact Us</h1>
        <p className="text-lg mb-8 text-gray-400">Get in touch with our team for inquiries and bookings.</p>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="bg-dark-700 p-8 rounded-lg shadow-lg">
            <form className="space-y-4">
              <div>
                <label htmlFor="name" className="block mb-2 text-gray-300 font-medium">Name</label>
                <input type="text" id="name" className="w-full p-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500" />
              </div>
              <div>
                <label htmlFor="email" className="block mb-2 text-gray-300 font-medium">Email</label>
                <input type="email" id="email" className="w-full p-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500" />
              </div>
              <div>
                <label htmlFor="message" className="block mb-2 text-gray-300 font-medium">Message</label>
                <textarea id="message" rows="4" className="w-full p-3 bg-dark-800 border border-dark-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-500"></textarea>
              </div>
              <button type="submit" className="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">Submit</button>
            </form>
          </div>
          <div className="flex flex-col justify-center">
            <div className="bg-dark-700 p-8 rounded-lg shadow-lg">
              <h2 className="font-heading text-xl font-semibold mb-6 text-white">Our Information</h2>
              <div className="space-y-4">
                <div className="flex items-start">
                  <svg className="w-5 h-5 text-blue-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <div>
                    <p className="text-gray-300 font-medium">Address</p>
                    <p className="text-gray-400">123 Tourism Ave, Orlando, FL</p>
                  </div>
                </div>
                <div className="flex items-start">
                  <svg className="w-5 h-5 text-blue-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <div>
                    <p className="text-gray-300 font-medium">Phone</p>
                    <p className="text-gray-400">(123) 456-7890</p>
                  </div>
                </div>
                <div className="flex items-start">
                  <svg className="w-5 h-5 text-blue-500 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <div>
                    <p className="text-gray-300 font-medium">Email</p>
                    <p className="text-gray-400">info@toplistorlando.com</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Contact; 