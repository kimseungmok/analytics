import React, { useState } from "react";
import DashboardHeader from "@/Components/DashboardHeader";

const Reports = () => {

  const [currentDate, setCurrentDate] = useState('');
  const [previousDate, setPreviousDate] = useState('');
  const [panelKey, setPanelKey] = useState(0);
  const [selectedBranches, setSelectedBranches] = useState([]);

  const handleSearch = (currDate, prevDate) => {
    setCurrentDate(currDate);
    setPreviousDate(prevDate);
    setPanelKey(panelKey => panelKey + 1);
  }

  const handleExport = () => {

  }

  return (
    <div>
      <DashboardHeader
        onSearch={handleSearch}
        onExport={handleExport}
        selectedBranches={selectedBranches}
        setSelectedBranches={setSelectedBranches}
      />
    </div>
  )
}

export default Reports;