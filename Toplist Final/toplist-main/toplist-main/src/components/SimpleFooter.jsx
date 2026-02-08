import React from 'react';

function SimpleFooter() {
  return (
    <footer className="bg-dark-900 text-white py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <img
            src="/TopListLogo3.png"
            alt="Toplist Orlando"
            className="h-10 mx-auto mb-4 opacity-80"
          />
          <p className="text-gray-400 text-sm">
            &copy; {new Date().getFullYear()} Toplist Orlando. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}

export default SimpleFooter;
