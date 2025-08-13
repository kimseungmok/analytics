import React, { useState, useEffect } from "react";
import { FaSearch, FaDownload } from "react-icons/fa";
import BranchSelector from "./BranchSelector";
import { fetchBranchList } from "@/api/gradeAnalytics";
import moment from "moment";

const DashboardHeader = ({ onSearch, onExport, selectedBranches, setSelectedBranches }) => {
  const [currentDateInput, setCurrentDateInput] = useState('2025-06-01');
  const [previousDateInput, setPreviousDateInput] = useState('2025-05-01');
  const [branchList, setBranchList] = useState([]);
  const [isSelectorOpen, setIsSelectorOpen] = useState(false);

  useEffect(() => {
    const loadBranches = async () => {
      try {
        const data = await fetchBranchList();
        setBranchList(data.branches);
        //setSelectedBranches(data.branches.map((b) => b.id));
      } catch (error) {
        console.error("支店リストの取得に失敗しました:", error);
      }
    };

    loadBranches();

    if (onSearch) {
      onSearch(currentDateInput, previousDateInput);
    };
  }, []);

  const handleSearchClick = () => {
    if (moment(previousDateInput).isSameOrAfter(moment(currentDateInput))) {
      alert('日付を正しく選択してください');
      return;
    }
    onSearch(currentDateInput, previousDateInput);
  };

  const handleExportClick = () => {
    onExport(currentDateInput, previousDateInput);
  };

  const getSelectedBranchNames = () => {
    if (selectedBranches.length === 0) return `0 (+非コンバージョンユーザー)`;
    if (selectedBranches.length === branchList.length) return `全体 (${selectedBranches.length})`;

    const selected = branchList
      .filter((b) => selectedBranches.includes(b.id))
      .map((b) => b.name);

    const visible = selected.slice(0, 3);
    const moreCount = selected.length - visible.length;

    return `${visible.join(', ')}${moreCount > 0 ? `以外 ${moreCount}件` : ''}`;
  };

  return (
    <div className="dashboard-header bg-white shadow-md rounded-lg p-3 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
      
      {/* Left Section */}
      <div className="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-6">
        <h1 className="text-xl font-semibold text-gray-800 whitespace-nowrap">日付検索</h1>

        <div className="flex flex-wrap items-center gap-2.5">
          <div className="flex items-center space-x-2">
            <label htmlFor="previousDate" className="text-gray-700 text-xs">前月日:</label>
            <input
              type="date"
              id="previousDate"
              className="border border-gray-300 rounded-md p-1.5 text-xs focus:ring-blue-500 focus:border-blue-500"
              value={previousDateInput}
              onChange={(e) => setPreviousDateInput(e.target.value)}
            />
          </div>

          <div className="flex items-center space-x-2">
            <label htmlFor="currentDate" className="text-gray-700 text-xs">現在日:</label>
            <input
              type="date"
              id="currentDate"
              className="border border-gray-300 rounded-md p-1.5 text-xs focus:ring-blue-500 focus:border-blue-500"
              value={currentDateInput}
              onChange={(e) => setCurrentDateInput(e.target.value)}
            />
          </div>

          <div className="relative">
            <div className="flex items-center gap-2">
              <button
                onClick={() => setIsSelectorOpen(prev => !prev)}
                className={`px-3 py-1.5 text-white text-xs rounded 
                  ${selectedBranches.length === 0 ? 'bg-gray-400 hover:bg-gray-500'
                    : selectedBranches.length === branchList.length ? 'bg-blue-600 hover:bg-blue-700' 
                    : 'bg-blue-400 hover:bg-blue-500'}`}
              >
                店舗選択 ▼
              </button>

              <span className="text-xs text-gray-700 font-medium whitespace-nowrap">
                選択中：<span className="text-gray-900">{getSelectedBranchNames()}</span>
              </span>
            </div>

            {isSelectorOpen && (
              <BranchSelector
                branchList={branchList}
                selectedBranches={selectedBranches}
                setSelectedBranches={setSelectedBranches}
                close={() => setIsSelectorOpen(false)}
              />
            )}
          </div>
        </div>
      </div>

      {/* Right Section */}
      <div className="flex items-center gap-3 self-start sm:self-auto">
        <button
          onClick={handleSearchClick}
          className="flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-xs"
        >
          <FaSearch className="mr-1" />検索
        </button>

        <button
          onClick={handleExportClick}
          className="flex items-center justify-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-xs"
        >
          <FaDownload className="mr-1" /> CSV出力
        </button>
      </div>
    </div>
  );
};

export default DashboardHeader;
