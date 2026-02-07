import React from 'react';
import CircularProgress from '@mui/material/CircularProgress';

/**
 * Centered loading spinner using MUI CircularProgress
 */
const LoadingSpinner = () => {
  return (
    <div className="flex justify-center items-center min-h-[400px]">
      <CircularProgress size={48} />
    </div>
  );
};

export default LoadingSpinner;
