import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

export const useSearchFilters = () => {
  const navigate = useNavigate();
  const [selectedResort, setSelectedResort] = useState('All Resorts');
  const [showResortDropdown, setShowResortDropdown] = useState(false);
  const [dates, setDates] = useState([]);
  const [guests, setGuests] = useState('');
  const [houseName, setHouseName] = useState('');
  const [showOptionsDropdown, setShowOptionsDropdown] = useState(false);
  
  const [options, setOptions] = useState({
    bedrooms: '',
    bathrooms: '',
    pool: false,
    gameRoom: false,
    nearDisney: false
  });

  const handleDateChange = (selectedDates) => {
    setDates(selectedDates);
  };

  const handleOptionChange = (option, value) => {
    setOptions(prev => ({
      ...prev,
      [option]: value
    }));
  };

  const formatDate = (date) => date.toISOString().split('T')[0]; // 'YYYY-MM-DD'

  const handleSearch = () => {
    if (dates.length !== 2) {
      alert('Please select check-in and check-out dates');
      return;
    }

    // Navigate to properties page with search params
    const params = new URLSearchParams({
      checkin: formatDate(dates[0]),
      checkout: formatDate(dates[1]),
      adults: guests || '1',
      children: houseName || '0'  // houseName is actually children count
    });

    navigate(`/homes?${params.toString()}`);
  };

  const toggleResortDropdown = () => {
    setShowResortDropdown(!showResortDropdown);
  };

  const toggleOptionsDropdown = () => {
    setShowOptionsDropdown(!showOptionsDropdown);
  };

  const selectResort = (resortLabel) => {
    setSelectedResort(resortLabel);
    setShowResortDropdown(false);
  };

  return {
    // State
    selectedResort,
    showResortDropdown,
    dates,
    guests,
    houseName,
    showOptionsDropdown,
    options,
    
    // Setters
    setGuests,
    setHouseName,
    
    // Handlers
    handleDateChange,
    handleOptionChange,
    handleSearch,
    toggleResortDropdown,
    toggleOptionsDropdown,
    selectResort
  };
}; 