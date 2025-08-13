import React, { useState, useEffect } from "react";
import moment from "moment";

const DashboardHeader = ({ onSearch, onExport }) => {
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [currentDateInput, setCurrentDateInput] = useState('2025-06-01');
    const [previousDateInput, setPreviousDateInput] = useState('2025-05-01');

    useEffect(() => {
      if (onSearch) {
        onSearch(currentDateInput, previousDateInput);
      }
    }, []);

    const handleSearchClick = () => {
      if(onSearch) {
        if(moment(previousDateInput).isSameOrAfter(moment(currentDateInput))) {
          alert('日付を正しく選択してください');
          return;
        }
        onSearch(currentDateInput, previousDateInput);
      }
    };

    const handleExportClick = () => {
      if(onExport) {
        onExport(currentDateInput, previousDateInput);
      }
    }

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