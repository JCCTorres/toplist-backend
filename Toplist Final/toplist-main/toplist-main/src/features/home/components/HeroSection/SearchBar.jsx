import React from 'react';
import Flatpickr from 'react-flatpickr';
import 'flatpickr/dist/themes/light.css';
import { resorts } from '../../data/resorts';

const SearchBar = ({
  selectedResort,
  showResortDropdown,
  dates,
  guests,
  houseName,
  showOptionsDropdown,
  options,
  setGuests,
  setHouseName,
  handleDateChange,
  handleOptionChange,
  handleSearch,
  toggleResortDropdown,
  toggleOptionsDropdown,
  selectResort
}) => {
  return (
    <div className="bg-transparent rounded-xl overflow-hidden shadow-lg flex flex-col md:flex-row gap-3 p-2">
      {/* Resort Selector with dropdown */}
      {/* <div className="search-bar-section resort-selector bg-blue-700 text-white py-3 px-6 font-semibold flex-1 relative">
        <div 
          className="dropdown-toggle flex items-center justify-between cursor-pointer w-full"
          onClick={toggleResortDropdown}
        >
          <span>{selectedResort}</span>
          <i className="fas fa-chevron-down ml-2"></i>
        </div>
        {showResortDropdown && (
          <div className="resort-dropdown absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-md shadow-lg z-50 w-full border border-gray-200">
            {resorts.map(resort => (
              <div 
                key={resort.value}
                className="p-2 hover:bg-gray-100 cursor-pointer"
                onClick={() => selectResort(resort.label)}
              >
                {resort.label}
              </div>
            ))}
          </div>
        )}
      </div> */}

      {/* Travel Dates with calendar */}
      <div className="search-bar-section travel-dates bg-blue-600 text-white py-4 px-8 font-semibold flex-1 rounded-lg">
        <Flatpickr
          options={{
            mode: 'range',
            dateFormat: 'M d, Y',
            minDate: 'today',
            showMonths: 2,
            placeholder: 'Travel Dates'
          }}
          value={dates}
          onChange={handleDateChange}
          className="w-full bg-transparent outline-none cursor-pointer"
          placeholder="Travel Dates"
        />
      </div>

      {/* Options Dropdown */}
      {/* <div className="search-bar-section options relative bg-blue-600 py-3 px-6 font-semibold flex-1 flex items-center justify-between cursor-pointer"
           onClick={toggleOptionsDropdown}>
        <span>Options</span> 
        <i className="fas fa-chevron-down text-white"></i> */}
        
        {/* {showOptionsDropdown && (
          <div className="options-dropdown absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-md shadow-lg z-50 w-full p-4 border border-gray-200">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Bedrooms</label>
                <select 
                  className="w-full p-2 border border-gray-300 rounded" 
                  value={options.bedrooms}
                  onChange={(e) => handleOptionChange('bedrooms', e.target.value)}
                >
                  <option value="">Any</option>
                  <option value="1-2">1-2</option>
                  <option value="3-4">3-4</option>
                  <option value="5-6">5-6</option>
                  <option value="7+">7+</option>
                </select>
              </div>
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Bathrooms</label>
                <select 
                  className="w-full p-2 border border-gray-300 rounded" 
                  value={options.bathrooms}
                  onChange={(e) => handleOptionChange('bathrooms', e.target.value)}
                >
                  <option value="">Any</option>
                  <option value="1-2">1-2</option>
                  <option value="3-4">3-4</option>
                  <option value="5+">5+</option>
                </select>
              </div>
            </div>
            
            <div className="mt-4 space-y-2">
              <div className="flex items-center">
                <input 
                  type="checkbox" 
                  id="pool-option" 
                  className="h-4 w-4 text-blue-600" 
                  checked={options.pool}
                  onChange={(e) => handleOptionChange('pool', e.target.checked)}
                />
                <label htmlFor="pool-option" className="ml-2 text-sm text-gray-700">Private Pool</label>
              </div>
              <div className="flex items-center">
                <input 
                  type="checkbox" 
                  id="game-room-option" 
                  className="h-4 w-4 text-blue-600" 
                  checked={options.gameRoom}
                  onChange={(e) => handleOptionChange('gameRoom', e.target.checked)}
                />
                <label htmlFor="game-room-option" className="ml-2 text-sm text-gray-700">Game Room</label>
              </div>
              <div className="flex items-center">
                <input 
                  type="checkbox" 
                  id="disney-option" 
                  className="h-4 w-4 text-blue-600" 
                  checked={options.nearDisney}
                  onChange={(e) => handleOptionChange('nearDisney', e.target.checked)}
                />
                <label htmlFor="disney-option" className="ml-2 text-sm text-gray-700">Near Disney (Less than 5 miles)</label>
              </div>
            </div>
          </div>
        )} */}
      {/* </div> */}

      {/* Guests input field */}
      <div className="search-bar-section guests bg-white flex items-center justify-between px-8 py-4 font-semibold flex-1 rounded-lg">
        <span className="text-black">
          {guests ? `${guests} ${parseInt(guests) === 1 ? 'Adult' : 'Adults'}` : 'Adult'}
        </span>
        <input 
          type="number" 
          min="1" 
          placeholder="" 
          className="w-16 outline-none text-black bg-transparent placeholder-gray-500 text-right"
          value={guests}
          onChange={(e) => setGuests(e.target.value)}
        />
      </div>

      {/* House name input field */}
      <div className="search-bar-section house bg-white flex items-center justify-between px-8 py-4 font-semibold flex-1 rounded-lg">
        <span className="text-black">
          {houseName ? `${houseName} ${parseInt(houseName) === 1 ? 'Children' : 'Children'}` : 'Children'}
        </span>
        <input 
          type="number" 
          min="1" 
          placeholder="" 
          className="w-16 outline-none text-black bg-transparent placeholder-gray-500 text-right"
          value={houseName}
          onChange={(e) => setHouseName(e.target.value)}
        />
      </div>

      <button 
        className="search-bar-section search-btn bg-red-400 text-white py-4 px-8 font-semibold flex-1 hover:bg-red-500 transition-colors rounded-lg"
        onClick={handleSearch}
      >
        Search
      </button>
    </div>
  );
};

export default SearchBar; 