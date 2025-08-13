// Dashboard.jsx
import React, { useState } from 'react';
import ChartGrid from '@/Components/ChartGrid';
import DashboardHeader from '@/Components/DashboardHeader';
import UserTable from '@/Components/UserTable';
import KPIPanel from '@/Components/KPIPanel';
import SegmentMigrationPanel from '@/Components/SegmentMigrationPanel';
import AttributeCrossAnalysisPanel from '@/Components/AttributeCrossAnalysisPanel';
import SegmentSummaryPanel from '@/Components/SegmentSummaryPanel';
import SankeyDiagramPanel from '@/Components/SankeyDiagramPanel';

const Dashboard = () => {
  const [currentKpiDate, setCurrentKpiDate] = useState('2025-06-01');
  const [previousKpiDate, setPreviousKpiDate] = useState('2025-05-01');
  const [selectedBranches, setSelectedBranches] = useState([]);
  const [panelKey, setPanelKey] = useState(0);

  const handleSearch = (currentDate, previousDate) => {
    console.log('検索期間：', previousDate, 'から', currentDate);
    setCurrentKpiDate(currentDate);
    setPreviousKpiDate(previousDate);
    setPanelKey(prevKey => prevKey + 1);
  };

  const handleExport = (start, end) => {
    console.log('CSV出力期間：', start, end);
  };

  return (
    <div className="dashboard-page space-y-4 p-1">
      <DashboardHeader 
        onSearch={handleSearch} 
        onExport={handleExport} 
        selectedBranches={selectedBranches}
        setSelectedBranches={setSelectedBranches}
      />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <KPIPanel
          key={`kpi-${panelKey}`}
          currentDate={currentKpiDate}
          previousDate={previousKpiDate}
          selectedBranches={selectedBranches}
        />
        <SegmentMigrationPanel
          key={`migration-${panelKey}`}
          currentDate={currentKpiDate}
          previousDate={previousKpiDate}
          selectedBranches={selectedBranches}
        />
      </div>

      <div className="mt-6">
        <SegmentSummaryPanel
          key={`summary-${panelKey}`}
          currentDate={currentKpiDate}
          selectedBranches={selectedBranches}
        />
      </div>

      <div className="mt-6">
        <SankeyDiagramPanel
          key={`sankey-${panelKey}`}
          startDate={previousKpiDate}
          endDate={currentKpiDate}
          selectedBranches={selectedBranches}
        />
      </div>

      <div className="mt-6">
        <AttributeCrossAnalysisPanel
          key={`cross-analysis-${panelKey}`}
          snapshotDate={currentKpiDate}
          selectedBranches={selectedBranches}
        />
      </div>

    </div>
  );
};

export default Dashboard;
