import React, { useState } from 'react';
import { api } from '../../../../services/api';

const ManagementForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    propertyType: '',
    bedrooms: '',
    message: ''
  });
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.id]: e.target.value });
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Basic validation
    if (!formData.name || !formData.email) {
      setError('Please provide your name and email');
      return;
    }

    setSubmitting(true);
    setError('');

    try {
      const result = await api.sendManagementEmail(formData);
      if (result.success) {
        setSuccess(true);
        setFormData({ name: '', email: '', propertyType: '', bedrooms: '', message: '' });
      } else {
        setError(result.message || 'Failed to send request. Please try again.');
      }
    } catch (err) {
      console.error('Management form error:', err);
      setError('Failed to send request. Please try again later.');
    } finally {
      setSubmitting(false);
    }
  };

  if (success) {
    return (
      <div className="bg-dark-700 p-8 rounded-lg shadow-lg">
        <div className="bg-green-900/30 border border-green-700 rounded-lg p-8 text-center">
          <svg className="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 className="font-heading text-xl font-semibold text-green-400 mb-2">Request Sent!</h3>
          <p className="text-green-300 mb-4">Thank you for your interest! We'll contact you shortly with management information.</p>
          <button
            onClick={() => setSuccess(false)}
            className="text-green-400 hover:text-green-300 underline font-medium"
          >
            Submit another inquiry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-dark-700 p-8 rounded-lg shadow-lg">
      <h3 className="font-heading text-xl font-semibold mb-4 text-center text-white">Request Management Info</h3>
      <form onSubmit={handleSubmit}>
        {error && (
          <div className="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-400 text-sm">
            {error}
          </div>
        )}
        <div className="mb-4">
          <label htmlFor="name" className="block text-gray-300 font-medium mb-2">Your Name</label>
          <input
            type="text"
            id="name"
            value={formData.name}
            onChange={handleChange}
            placeholder="Type your name"
            className="w-full border border-dark-600 bg-dark-800 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-white placeholder-gray-500"
          />
        </div>
        <div className="mb-4">
          <label htmlFor="email" className="block text-gray-300 font-medium mb-2">Email Address</label>
          <input
            type="email"
            id="email"
            value={formData.email}
            onChange={handleChange}
            placeholder="Type your email"
            className="w-full border border-dark-600 bg-dark-800 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-white placeholder-gray-500"
          />
        </div>
        <div className="mb-4">
          <label htmlFor="propertyType" className="block text-gray-300 font-medium mb-2">Property Type</label>
          <select
            id="propertyType"
            value={formData.propertyType}
            onChange={handleChange}
            className="w-full border border-dark-600 bg-dark-800 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-white"
          >
            <option value="">Select Property Type</option>
            <option value="condo">Condo</option>
            <option value="townhouse">Townhouse</option>
            <option value="villa">Villa</option>
            <option value="house">Single Family Home</option>
          </select>
        </div>
        <div className="mb-4">
          <label htmlFor="bedrooms" className="block text-gray-300 font-medium mb-2">Number of Bedrooms</label>
          <select
            id="bedrooms"
            value={formData.bedrooms}
            onChange={handleChange}
            className="w-full border border-dark-600 bg-dark-800 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-white"
          >
            <option value="">Select Bedrooms</option>
            <option value="1-2">1-2 Bedrooms</option>
            <option value="3-4">3-4 Bedrooms</option>
            <option value="5-6">5-6 Bedrooms</option>
            <option value="7+">7+ Bedrooms</option>
          </select>
        </div>
        <div className="mb-4">
          <label htmlFor="message" className="block text-gray-300 font-medium mb-2">Additional Information (Optional)</label>
          <textarea
            id="message"
            rows="3"
            value={formData.message}
            onChange={handleChange}
            placeholder="Tell us about your property..."
            className="w-full border border-dark-600 bg-dark-800 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-white placeholder-gray-500"
          ></textarea>
        </div>
        <button
          type="submit"
          disabled={submitting}
          className="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-lg font-medium text-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {submitting ? 'Sending...' : 'Request Information'}
        </button>
      </form>
    </div>
  );
};

export default ManagementForm;
