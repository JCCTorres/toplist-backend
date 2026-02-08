import React from 'react';
import { Link } from 'react-router-dom';
import ManagementForm from './ManagementForm';

const ManagementSection = () => {
  return (
    <section id="management" className="bg-dark-900 py-16">
      <div className="container mx-auto px-4">
        <h2 className="font-heading text-3xl font-bold mb-10 text-center text-white">Property Management</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h3 className="font-heading text-2xl font-semibold mb-4 text-white">Maximize Your Vacation Home's Potential</h3>
            <p className="text-gray-400 mb-6">
              Our property management services are designed to maximize your vacation home's revenue potential while minimizing your involvement. With our local expertise and dedication to guest satisfaction, we'll handle everything from marketing to maintenance.
            </p>
            <ul className="space-y-3 mb-8">
              <li className="flex items-center text-gray-300">
                <svg className="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <span>Professional photography and listing optimization</span>
              </li>
              <li className="flex items-center text-gray-300">
                <svg className="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <span>Dynamic pricing strategy to maximize occupancy</span>
              </li>
              <li className="flex items-center text-gray-300">
                <svg className="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <span>24/7 guest support and communication</span>
              </li>
              <li className="flex items-center text-gray-300">
                <svg className="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <span>Premium cleaning and maintenance services</span>
              </li>
              <li className="flex items-center text-gray-300">
                <svg className="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
                <span>Regular property inspections and reporting</span>
              </li>
            </ul>
            <Link to="/management" className="inline-block bg-blue-600 text-white py-3 px-8 rounded-lg font-medium hover:bg-blue-700 transition-colors">
              Learn More
            </Link>
          </div>
          <ManagementForm />
        </div>
      </div>
    </section>
  );
};

export default ManagementSection; 