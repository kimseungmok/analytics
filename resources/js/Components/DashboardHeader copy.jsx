import React, { useState } from "react";

const DashboardHeader = ({ onSearch, onExport }) => {
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');

    return (
      <div className="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div className="flex items-center gap-2">
          <label className="text-sm text-gray-600">Start:</label>
          <input
            type="date"
            value={startDate}
            onChange={e => setStartDate(e.target.value)}
            className="border rounded px-3 py-2 text-sm"
          />
          <label className="text-sm text-grap-600">End:</label>
          <input
            type="date"
            value={endDate}
            onChange={e => setEndDate(e.target.value)}
            className="border rounded px-3 py-2 text-sm"
          />
          <div className="flex gap-2">
            <button 
              className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm"
              onClick={() => onSearch(startDate, endDate)}
            >
              Search
            </button>
            <button
              className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm"
              onClick={() => onExport(startDate, endDate)}
              >
                CSV Export
              </button>
          </div>
        </div>

      </div>
    );
};

export default DashboardHeader;