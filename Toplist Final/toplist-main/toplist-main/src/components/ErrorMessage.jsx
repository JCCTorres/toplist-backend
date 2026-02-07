import React from 'react';

/**
 * Error message component with optional retry button
 * @param {Object} props
 * @param {string} [props.message] - Error message to display
 * @param {Function} [props.onRetry] - Callback for retry button (if provided, button is shown)
 */
const ErrorMessage = ({ message, onRetry }) => {
  const displayMessage = message || 'Unable to load properties. Please try again later.';

  return (
    <div className="flex flex-col items-center justify-center min-h-[400px]">
      <p className="text-gray-600 text-lg mb-4 text-center px-4">
        {displayMessage}
      </p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="bg-blue-900 text-white py-2 px-6 rounded-lg hover:bg-blue-800 transition-colors"
        >
          Try Again
        </button>
      )}
    </div>
  );
};

export default ErrorMessage;
