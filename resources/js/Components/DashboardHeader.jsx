import React, { useState, useEffect } from "react";
import { FaSearch, FaDownload } from "react-icons/fa";
import moment from "moment";

const DashboardHeader = ({ onSearch, onExport }) => {
  const [currentDateInput, setCurrentDateInput] = useState('2025-06-01');
  const [previousDateInput, setPreviousDateInput] = useState('2025-05-01');

  useEffect(() => {
    if (onSearch) {
      onSearch(currentDateInput, previousDateInput);
    }
  }, []);

  const handleSearchClick = () => {
    if (onSearch) {
      if (moment(previousDateInput).isSameOrAfter(moment(currentDateInput))) {
        alert('日付を正しく選択してください');
        return;
      }
      onSearch(currentDateInput, previousDateInput);
    }
  };

  const handleExportClick = () => {
    if (onExport) {
      onExport(currentDateInput, previousDateInput);
    }
  }

  return (
    <div className="dashboard-header bg-white shadow-md rounded-lg p-4 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
      <h1 className="text-2xl font-bold text-gray-800">日付検索</h1>

      <div className="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
        <div className="flex items-center space-x-2">
          <label htmlFor="previousDate" className="text-gray-700 text-sm">前月日:</label>
          <input
            type="date"
            id="previousDate"
            className="border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            value={previousDateInput}
            onChange={(e) => setPreviousDateInput(e.target.value)}
          />
        </div>

        <div className="flex items-center space-x-2">
          <label htmlFor="currentDate" className="text-gray-700 text-sm">現在日:</label>
          <input
            type="date"
            id="currentDate"
            className="border border-gray-300 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500"
            value={currentDateInput}
            onChange={(e) => setCurrentDateInput(e.target.value)}
          />
        </div>

        <button
          onClick={handleSearchClick}
          className="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm w-full sm:w-auto"
        >
          <FaSearch className="mr-2" />検索
        </button>

        <button
          onClick={handleExportClick}
          className="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm w-full sm:w-auto"
        >
          <FaDownload className="mr-2" /> CSV出力
        </button>
      </div>
    </div>
  );
};

export default DashboardHeader;