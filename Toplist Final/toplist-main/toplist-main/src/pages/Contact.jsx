import React from 'react';

function Contact() {
  return (
    <div className="min-h-screen">
      {/* Header */}
      <div className="bg-navy-950 pt-24 pb-12">
        <div className="container mx-auto px-4">
          <h1 className="font-heading text-4xl md:text-5xl font-bold text-white text-center">Contact Us</h1>
          <div className="gold-line mx-auto mt-4"></div>
          <p className="text-sand-200/70 text-center mt-4 max-w-2xl mx-auto">Get in touch with our team for inquiries and bookings.</p>
        </div>
      </div>

      <div className="py-16">
        <div className="container mx-auto px-4 max-w-5xl">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-2xl">
              <h2 className="font-heading text-xl font-bold text-white mb-6">Send us a message</h2>
              <form className="space-y-5">
                <div>
                  <label htmlFor="name" className="block mb-2 text-sand-200/60 text-xs font-medium uppercase tracking-wider">Name</label>
                  <input type="text" id="name" className="w-full p-3 bg-white/10 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-white placeholder-white/30" />
                </div>
                <div>
                  <label htmlFor="email" className="block mb-2 text-sand-200/60 text-xs font-medium uppercase tracking-wider">Email</label>
                  <input type="email" id="email" className="w-full p-3 bg-white/10 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-white placeholder-white/30" />
                </div>
                <div>
                  <label htmlFor="message" className="block mb-2 text-sand-200/60 text-xs font-medium uppercase tracking-wider">Message</label>
                  <textarea id="message" rows="4" className="w-full p-3 bg-white/10 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-gold-500 text-white placeholder-white/30"></textarea>
                </div>
                <button type="submit" className="w-full bg-gold-500 text-navy-950 px-6 py-3 rounded-xl font-semibold hover:bg-gold-400 transition-colors shadow-md">Submit</button>
              </form>
            </div>
            <div className="flex flex-col justify-center">
              <div className="bg-white/5 backdrop-blur-sm border border-white/10 p-8 rounded-2xl">
                <h2 className="font-heading text-xl font-bold mb-6 text-white">Our Information</h2>
                <div className="space-y-5">
                  <div className="flex items-start">
                    <div className="bg-gold-500/10 p-2.5 rounded-lg mr-4 flex-shrink-0">
                      <svg className="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sand-200/40 text-xs font-medium uppercase tracking-wider">Address</p>
                      <p className="text-white mt-1">123 Tourism Ave, Orlando, FL</p>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <div className="bg-gold-500/10 p-2.5 rounded-lg mr-4 flex-shrink-0">
                      <svg className="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sand-200/40 text-xs font-medium uppercase tracking-wider">Phone</p>
                      <p className="text-white mt-1">(123) 456-7890</p>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <div className="bg-gold-500/10 p-2.5 rounded-lg mr-4 flex-shrink-0">
                      <svg className="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sand-200/40 text-xs font-medium uppercase tracking-wider">Email</p>
                      <p className="text-white mt-1">info@toplistorlando.com</p>
                    </div>
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
