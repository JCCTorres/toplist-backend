import React from 'react';
import Flatpickr from 'react-flatpickr';
import 'flatpickr/dist/themes/light.css';

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
    <div className="rounded-xl shadow-2xl grid grid-cols-2 md:flex md:flex-row gap-2 md:gap-3 p-2 md:p-3 bg-navy-950/80 backdrop-blur-xl border border-white/10">
      {/* Travel Dates with calendar */}
      <div className="col-span-2 search-bar-section travel-dates bg-white/10 border border-white/20 text-white py-3 md:py-4 px-4 md:px-6 font-semibold md:flex-1 rounded-lg flex items-center gap-3">
        <svg className="w-5 h-5 text-gold-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
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
          className="w-full bg-transparent outline-none cursor-pointer placeholder-white/40 text-white text-sm md:text-base"
          placeholder="Travel Dates"
        />
      </div>

      {/* Adults input */}
      <div className="search-bar-section guests bg-white/10 border border-white/20 flex items-center px-4 md:px-6 py-3 md:py-4 font-semibold md:flex-1 rounded-lg gap-2 md:gap-3">
        <label htmlFor="adults-input" className="text-white/60 text-xs md:text-sm whitespace-nowrap">Adults</label>
        <input
          id="adults-input"
          type="number"
          min="1"
          max="30"
          placeholder="0"
          className="w-full outline-none text-white bg-transparent text-right text-base md:text-lg placeholder-white/40"
          value={guests}
          onChange={(e) => setGuests(e.target.value)}
        />
      </div>

      {/* Children input */}
      <div className="search-bar-section house bg-white/10 border border-white/20 flex items-center px-4 md:px-6 py-3 md:py-4 font-semibold md:flex-1 rounded-lg gap-2 md:gap-3">
        <label htmlFor="children-input" className="text-white/60 text-xs md:text-sm whitespace-nowrap">Children</label>
        <input
          id="children-input"
          type="number"
          min="0"
          max="30"
          placeholder="0"
          className="w-full outline-none text-white bg-transparent text-right text-base md:text-lg placeholder-white/40"
          value={houseName}
          onChange={(e) => setHouseName(e.target.value)}
        />
      </div>

      <button
        className="col-span-2 search-bar-section search-btn bg-navy-900 text-gold-400 py-3 md:py-4 px-8 font-semibold md:flex-1 hover:bg-navy-800 transition-colors rounded-lg border border-gold-500/30"
        onClick={handleSearch}
      >
        Search
      </button>
    </div>
  );
};

export default SearchBar;
